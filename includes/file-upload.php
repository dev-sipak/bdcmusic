<?php
/**
 * File Upload Helper Functions
 *
 * Reusable utilities for handling file uploads across services.
 */

if ( ! defined( 'HELPERS_LOADED' ) ) {
    require_once __DIR__ . '/helpers.php';
}

/**
 * Get allowed MIME types by service.
 */
function get_allowed_mime_types( $service = 'general' ) {
    $types = [
        'audio-video' => [
            'audio/wav', 'audio/x-wav', 'audio/mpeg', 'audio/mp3',
            'audio/flac', 'audio/x-flac',
            'video/mp4', 'video/quicktime', 'video/x-msvideo',
        ],
        'digital-distribution' => [
            'audio/wav', 'audio/x-wav', 'audio/mpeg', 'audio/mp3',
            'image/jpeg', 'image/png',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv',
        ],
        'iprs' => [
            'image/jpeg', 'image/png',
            'application/pdf',
        ],
        'general' => [
            'audio/wav', 'audio/x-wav', 'audio/mpeg', 'audio/mp3', 'audio/flac',
            'video/mp4', 'video/quicktime',
            'image/jpeg', 'image/png',
            'application/pdf',
        ],
    ];

    return $types[ $service ] ?? $types['general'];
}

/**
 * Get max file size by service (in bytes).
 */
function get_max_file_size( $service = 'general' ) {
    $sizes = [
        'audio-video'          => 200 * 1024 * 1024,  // 200MB
        'digital-distribution' => 300 * 1024 * 1024,  // 300MB
        'iprs'                 => 10 * 1024 * 1024,   // 10MB
        'general'              => 50 * 1024 * 1024,   // 50MB
    ];

    return $sizes[ $service ] ?? $sizes['general'];
}

/**
 * Build the upload directory path for a service/booking.
 */
function get_upload_dir( $service, $bookingId ) {
    $dataDir = dirname( __DIR__ ) . '/data/uploads/' . $service . '/' . preg_replace( '/[^a-zA-Z0-9_-]/', '', $bookingId );
    if ( ! is_dir( $dataDir ) ) {
        mkdir( $dataDir, 0755, true );
    }
    return $dataDir;
}

/**
 * Process a single file upload.
 *
 * @param array  $file       The $_FILES entry for the file.
 * @param string $uploadDir  Destination directory.
 * @param array  $allowedMime Allowed MIME types.
 * @param int    $maxSize     Max file size in bytes.
 * @return array|false        Array with file info on success, false on failure.
 */
function process_file_upload( $file, $uploadDir, $allowedMime = [], $maxSize = 52428800 ) {
    if ( $file['error'] !== UPLOAD_ERR_OK ) {
        return false;
    }

    if ( $file['size'] > $maxSize ) {
        return false;
    }

    if ( ! empty( $allowedMime ) ) {
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mimeType = finfo_file( $finfo, $file['tmp_name'] );
        finfo_close( $finfo );

        if ( ! in_array( $mimeType, $allowedMime, true ) ) {
            return false;
        }
    } else {
        $mimeType = $file['type'] ?: 'application/octet-stream';
    }

    $originalName = basename( $file['name'] );
    $safeName = preg_replace( '/[^a-zA-Z0-9._-]/', '_', $originalName );
    $timestamp = time();
    $finalName = $timestamp . '_' . $safeName;
    $destination = $uploadDir . '/' . $finalName;

    if ( ! move_uploaded_file( $file['tmp_name'], $destination ) ) {
        return false;
    }

    return [
        'name'    => $originalName,
        'stored'  => $finalName,
        'path'    => $destination,
        'type'    => $mimeType,
        'size'    => $file['size'],
    ];
}

/**
 * Process multiple file uploads from a single input.
 *
 * @param array  $files       The $_FILES entry (with name[] style).
 * @param string $uploadDir   Destination directory.
 * @param array  $allowedMime Allowed MIME types.
 * @param int    $maxSize     Max file size per file.
 * @return array               Array of file info arrays.
 */
function process_multiple_uploads( $files, $uploadDir, $allowedMime = [], $maxSize = 52428800 ) {
    $stored = [];
    $count = is_array( $files['name'] ) ? count( $files['name'] ) : 0;

    for ( $i = 0; $i < $count; $i++ ) {
        $singleFile = [
            'name'     => $files['name'][ $i ],
            'type'     => $files['type'][ $i ],
            'tmp_name' => $files['tmp_name'][ $i ],
            'error'    => $files['error'][ $i ],
            'size'     => $files['size'][ $i ],
        ];

        $result = process_file_upload( $singleFile, $uploadDir, $allowedMime, $maxSize );
        if ( $result ) {
            $stored[] = $result;
        }
    }

    return $stored;
}

/**
 * Save file references to a booking's JSON data.
 */
function save_booking_files( $bookingsPath, $bookingId, $files ) {
    $bookings = [];
    if ( file_exists( $bookingsPath ) ) {
        $bookings = json_decode( file_get_contents( $bookingsPath ), true ) ?: [];
    }

    foreach ( $bookings as &$booking ) {
        if ( ( $booking['booking_id'] ?? '' ) === $bookingId || ( $booking['id'] ?? '' ) === $bookingId ) {
            $booking['files'] = $files;
            break;
        }
    }
    unset( $booking );

    return file_put_contents( $bookingsPath, json_encode( $bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) !== false;
}
