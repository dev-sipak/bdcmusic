<?php
header( 'Content-Type: application/json' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed.' ] );
    exit;
}

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

if ( ! isset( $_SESSION['user_id'] ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Please log in first.' ] );
    exit;
}

if ( ! isset( $_FILES['profile_picture'] ) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK ) {
    $errCode = isset( $_FILES['profile_picture'] ) ? $_FILES['profile_picture']['error'] : -1;
    echo json_encode( [ 'success' => false, 'message' => 'Upload failed. Please try again.' ] );
    exit;
}

$file = $_FILES['profile_picture'];

$allowedTypes = [ 'image/jpeg', 'image/png', 'image/webp' ];
$finfo         = new finfo( FILEINFO_MIME_TYPE );
$mimeType      = $finfo->file( $file['tmp_name'] );

if ( ! in_array( $mimeType, $allowedTypes, true ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Only JPG, PNG, and WebP images are allowed.' ] );
    exit;
}

$maxSize = 2 * 1024 * 1024;
if ( $file['size'] > $maxSize ) {
    echo json_encode( [ 'success' => false, 'message' => 'Image must be under 2 MB.' ] );
    exit;
}

$uploadDir = __DIR__ . '/../data/uploads/profile';
if ( ! is_dir( $uploadDir ) ) {
    mkdir( $uploadDir, 0755, true );
}

$extensions = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];
$ext = $extensions[ $mimeType ];

$userId  = $_SESSION['user_id'];
$fileName = $userId . '.' . $ext;
$filePath = $uploadDir . '/' . $fileName;

if ( ! move_uploaded_file( $file['tmp_name'], $filePath ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Failed to save image. Please try again.' ] );
    exit;
}

$relativePath = 'data/uploads/profile/' . $fileName;

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare( 'UPDATE users SET profile_picture = :pic WHERE id = :id LIMIT 1' );
    $stmt->execute( [
        ':pic' => $relativePath,
        ':id'  => $userId,
    ] );
} catch ( Exception $e ) {
    // Column may not exist yet — silently continue
}

$_SESSION['user_picture'] = $relativePath;

$fullUrl = $basePath . $relativePath;
echo json_encode( [ 'success' => true, 'message' => 'Profile picture updated.', 'url' => $fullUrl ] );
