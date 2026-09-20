<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'admin' ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
    exit;
}

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed' ] );
    exit;
}

if ( ! isset( $_FILES['artist_image'] ) || $_FILES['artist_image']['error'] !== UPLOAD_ERR_OK ) {
    echo json_encode( [ 'success' => false, 'message' => 'No file uploaded or upload error.' ] );
    exit;
}

$file = $_FILES['artist_image'];

if ( $file['size'] > 2 * 1024 * 1024 ) {
    echo json_encode( [ 'success' => false, 'message' => 'File must be under 2 MB.' ] );
    exit;
}

$finfo = new finfo( FILEINFO_MIME_TYPE );
$mime  = $finfo->file( $file['tmp_name'] );
$allowed = [ 'image/jpeg', 'image/png', 'image/webp' ];
if ( ! in_array( $mime, $allowed ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Only JPG, PNG, and WebP images are allowed.' ] );
    exit;
}

$uploadDir = __DIR__ . '/../../assets/images/artist';
if ( ! is_dir( $uploadDir ) ) {
    mkdir( $uploadDir, 0755, true );
}

$filename = 'artist-' . uniqid() . '.webp';
$filepath = $uploadDir . '/' . $filename;

$result = imageResizeAndConvert( $file['tmp_name'], 250, 360, $filepath );
if ( ! $result ) {
    echo json_encode( [ 'success' => false, 'message' => 'Failed to process image.' ] );
    exit;
}

$relativePath = 'assets/images/artist/' . $filename;

echo json_encode( [ 'success' => true, 'url' => $relativePath, 'filename' => $filename ] );
