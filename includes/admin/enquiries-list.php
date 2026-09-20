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
    $stmt = $pdo->query(
        'SELECT ae.id, ae.artist_id, ae.name, ae.email, ae.phone, ae.message, ae.status,
                DATE_FORMAT(ae.created_at, "%Y-%m-%d %H:%i") AS created_at,
                a.name AS artist_name
         FROM artist_enquiries ae
         LEFT JOIN artists a ON ae.artist_id = a.id
         ORDER BY ae.created_at DESC'
    );
    $enquiries = $stmt->fetchAll();

    echo json_encode( [ 'success' => true, 'enquiries' => $enquiries ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
