<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed' ] );
    exit;
}

$input = json_decode( file_get_contents( 'php://input' ), true );
if ( ! $input ) {
    $input = $_POST;
}

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf( $input );

$id = intval( $input['id'] ?? 0 );
if ( $id <= 0 ) {
    echo json_encode( [ 'success' => false, 'message' => 'Invalid artist ID.' ] );
    exit;
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare( 'UPDATE artists SET is_active = 0 WHERE id = :id' );
    $stmt->execute( [ ':id' => $id ] );
    echo json_encode( [ 'success' => true, 'message' => 'Artist deleted.' ] );
} catch ( Throwable $e ) {
    app_log( 'artist-delete', 'request failed', $e );
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'The artist could not be deleted. Please try again.' ] );
}
