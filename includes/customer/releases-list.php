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

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        'SELECT r.id, r.title, r.type, r.artwork_path, r.isrc, r.upc,
                DATE_FORMAT(r.go_live_date, "%Y-%m-%d") AS go_live_date,
                r.status, r.dolby, r.apple_itunes,
                DATE_FORMAT(r.created_at, "%Y-%m-%d") AS created_at
         FROM releases r
         WHERE r.customer_id = :cid
         ORDER BY r.created_at DESC'
    );
    $stmt->execute( [ ':cid' => $_SESSION['user_id'] ] );
    $releases = $stmt->fetchAll();

    foreach ( $releases as &$r ) {
        $astmt = $pdo->prepare( 'SELECT role, name FROM release_artists WHERE release_id = :rid' );
        $astmt->execute( [ ':rid' => $r['id'] ] );
        $r['artists'] = $astmt->fetchAll();

        $hstmt = $pdo->prepare( 'SELECT action, message, DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") AS created_at FROM release_history WHERE release_id = :rid ORDER BY created_at DESC' );
        $hstmt->execute( [ ':rid' => $r['id'] ] );
        $r['history'] = $hstmt->fetchAll();
    }

    echo json_encode( [ 'success' => true, 'releases' => $releases ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
