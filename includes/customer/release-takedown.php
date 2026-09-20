<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'customer' ) {
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
if ( ! $input ) { $input = $_POST; }

$id    = intval( $input['id'] ?? 0 );
$reason = sanitize_input( $input['reason'] ?? '' );

if ( $id <= 0 ) {
    echo json_encode( [ 'success' => false, 'message' => 'Invalid release ID.' ] );
    exit;
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare( 'UPDATE releases SET status = :status WHERE id = :id AND customer_id = :cid AND status IN ("live","approved")' );
    $stmt->execute( [ ':status' => 'takedown', ':id' => $id, ':cid' => $_SESSION['user_id'] ] );

    if ( $stmt->rowCount() > 0 ) {
        $hstmt = $pdo->prepare( 'INSERT INTO release_history (release_id, action, message) VALUES (:rid, :action, :msg)' );
        $hstmt->execute( [ ':rid' => $id, ':action' => 'Takedown Requested', ':msg' => $reason ?: 'Takedown requested by customer.' ] );
        echo json_encode( [ 'success' => true, 'message' => 'Takedown request submitted.' ] );
    } else {
        echo json_encode( [ 'success' => false, 'message' => 'Release not found or cannot be taken down.' ] );
    }
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
