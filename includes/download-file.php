<?php
/**
 * Secure File Download Endpoint
 *
 * Validates the requested file path and serves the file for download.
 */
if ( session_status() === PHP_SESSION_NONE ) {
require_once __DIR__ . '/session.php';
}

$relativePath = $_GET['file'] ?? '';

if ( empty( $relativePath ) ) {
    http_response_code( 400 );
    exit( 'Missing file parameter.' );
}

$rootDir       = realpath( dirname( __DIR__ ) );
$uploadsDir    = realpath( dirname( __DIR__ ) . '/data/uploads' );
$requestedFile = realpath( dirname( __DIR__ ) . '/' . $relativePath );

$sep = DIRECTORY_SEPARATOR;

if ( $rootDir === false || $uploadsDir === false || $requestedFile === false ) {
    http_response_code( 403 );
    exit( 'Access denied.' );
}

// The file has to sit inside the uploads directory, not merely inside the
// project, so a path like /data/config.php cannot be read through this endpoint.
$uploadsWithSep = rtrim( $uploadsDir, $sep ) . $sep;
if ( strpos( $requestedFile, $uploadsWithSep ) !== 0 ) {
    http_response_code( 403 );
    exit( 'Access denied.' );
}

require_once __DIR__ . '/database.php';

// uploaded_files.file_path is stored relative to the project root with forward
// slashes, exactly as booking_promote_uploads() writes it, so the same form is
// rebuilt here. Comparing against the uploads-relative form would never match
// and would deny every legitimate download.
$storedPath = str_replace( '\\', '/', ltrim( str_replace( $rootDir, '', $requestedFile ), $sep ) );

$userId = isset( $_SESSION['user_id'] ) ? (string) $_SESSION['user_id'] : '';
$userRole = isset( $_SESSION['user_role'] ) ? (string) $_SESSION['user_role'] : '';
$allowed = false;

if ( $userRole === 'admin' ) {
    $allowed = true;
} else {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare( 'SELECT b.booking_id, b.customer_id, b.customer_type FROM uploaded_files uf JOIN bookings b ON uf.booking_id = b.booking_id WHERE uf.file_path = :path LIMIT 1' );
        $stmt->execute( [ ':path' => $storedPath ] );
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        if ( $row ) {
            if ( $userId !== '' && (string) $row['customer_id'] === $userId ) {
                $allowed = true;
            } elseif ( $row['customer_type'] === 'guest' && ! empty( $_SESSION['booking_confirmed'] ) && hash_equals( (string) $_SESSION['booking_confirmed'], (string) $row['booking_id'] ) ) {
                $allowed = true;
            }
        }
      } catch ( Throwable $e ) {
          // Fail closed: an unreadable path or missing realpath means this file
          // is not demonstrably inside the uploads directory, so deny it.
          app_log( 'download-file', 'path check failed', $e );
          $allowed = false;
      }
}

if ( ! $allowed ) {
    http_response_code( 403 );
    exit( 'Access denied.' );
}

if ( ! file_exists( $requestedFile ) || ! is_file( $requestedFile ) ) {
    http_response_code( 404 );
    exit( 'File not found.' );
}

$fileName = basename( $requestedFile );
$finfo = finfo_open( FILEINFO_MIME_TYPE );
$mimeType = finfo_file( $finfo, $requestedFile );
finfo_close( $finfo );

header( 'Content-Type: ' . $mimeType );
header( 'Content-Disposition: attachment; filename="' . $fileName . '"' );
header( 'Content-Length: ' . filesize( $requestedFile ) );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );

readfile( $requestedFile );
exit;
