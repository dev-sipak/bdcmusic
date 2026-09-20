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

try {
    $pdo  = db_connect();
    $stmt = $pdo->query(
        'SELECT a.id, a.name, a.slug, a.image, a.location, a.bio, a.is_active, a.category_id,
                ac.name AS category, ac.slug AS category_slug,
                DATE_FORMAT(a.created_at, "%Y-%m-%d") AS created_at
         FROM artists a
         JOIN artist_categories ac ON a.category_id = ac.id
         ORDER BY ac.sort_order, a.name'
    );
    $artists = $stmt->fetchAll();

    $pstmt = $pdo->query( 'SELECT artist_id, service_type, price, sort_order FROM artist_pricing ORDER BY sort_order' );
    $pricing = $pstmt->fetchAll();

    $pricingMap = [];
    foreach ( $pricing as $p ) {
        $pricingMap[ $p['artist_id'] ][] = $p;
    }

    foreach ( $artists as &$a ) {
        $a['pricing'] = $pricingMap[ $a['id'] ] ?? [];
    }

    echo json_encode( [ 'success' => true, 'artists' => $artists ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
