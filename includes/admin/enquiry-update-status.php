<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

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

$input = json_decode( file_get_contents( 'php://input' ), true );
$id     = intval( $input['id'] ?? 0 );
$status = sanitize_input( $input['status'] ?? '' );

$allowed = [ 'new', 'read', 'replied' ];
if ( $id <= 0 || ! in_array( $status, $allowed ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Invalid data.' ] );
    exit;
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare( 'UPDATE artist_enquiries SET status = :status WHERE id = :id' );
    $stmt->execute( [ ':status' => $status, ':id' => $id ] );

    echo json_encode( [ 'success' => true, 'message' => 'Status updated.' ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
