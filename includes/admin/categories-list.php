<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

try {
    $pdo  = db_connect();
    $stmt = $pdo->query( 'SELECT id, name, slug, sort_order, is_active FROM artist_categories ORDER BY sort_order' );
    $categories = $stmt->fetchAll();
    echo json_encode( [ 'success' => true, 'categories' => $categories ] );
} catch ( Throwable $e ) {
    app_log( 'categories-list', 'request failed', $e );
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'The categories could not be loaded. Please try again.' ] );
}
