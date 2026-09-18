<?php
/**
 * Secure File Download Endpoint
 *
 * Validates the requested file path and serves the file for download.
 */
session_start();

$relativePath = $_GET['file'] ?? '';

if ( empty( $relativePath ) ) {
    http_response_code( 400 );
    exit( 'Missing file parameter.' );
}

$realBase = realpath( dirname( __DIR__ ) . '/data/uploads' );
$requestedFile = realpath( dirname( __DIR__ ) . '/' . $relativePath );

if ( $requestedFile === false || strpos( $requestedFile, $realBase ) !== 0 ) {
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
