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

$id = (int) ( $input['id'] ?? 0 );
if ( $id <= 0 ) {
    echo json_encode( [ 'success' => false, 'message' => 'Invalid service ID.' ] );
    exit;
}

try {
    // Retired rather than removed: bookings hold a foreign key to this row, so
    // a hard delete would fail or take an order's service reference with it.
    $stmt = $pdo->prepare( 'UPDATE services SET is_active = 0 WHERE id = :id' );
    $stmt->execute( [ ':id' => $id ] );

    if ( $stmt->rowCount() === 0 ) {
        $exists = $pdo->prepare( 'SELECT id FROM services WHERE id = :id LIMIT 1' );
        $exists->execute( [ ':id' => $id ] );

        if ( ! $exists->fetch() ) {
            echo json_encode( [ 'success' => false, 'message' => 'That service could not be found.' ] );
            exit;
        }
    }

    echo json_encode( [ 'success' => true, 'message' => 'Service removed.' ] );
} catch ( Throwable $e ) {
    app_log( 'service-delete', 'request failed', $e );
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'The service could not be removed. Please try again.' ] );
}
