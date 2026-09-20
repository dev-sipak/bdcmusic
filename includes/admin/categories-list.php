<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'admin' ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
    exit;
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->query( 'SELECT id, name, slug, sort_order, is_active FROM artist_categories ORDER BY sort_order' );
    $categories = $stmt->fetchAll();
    echo json_encode( [ 'success' => true, 'categories' => $categories ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
