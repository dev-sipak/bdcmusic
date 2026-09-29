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

$raw   = file_get_contents( 'php://input' );
$input = json_decode( $raw === false ? '' : $raw, true );

if ( ! is_array( $input ) ) {
    $input = array();
}

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf( $input );

$id     = intval( $input['id'] ?? 0 );
$status = clean_text( $input['status'] ?? '' );

$allowed = [ 'new', 'read', 'replied' ];
if ( $id <= 0 || ! in_array( $status, $allowed, true ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Invalid data.' ] );
    exit;
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare( 'UPDATE artist_enquiries SET status = :status WHERE id = :id' );
    $stmt->execute( [ ':status' => $status, ':id' => $id ] );

    // The status is already correct for that row, which is a legitimate no-op,
    // so the existing row is confirmed with a SELECT rather than inferred from
    // rowCount(): MySQL reports 0 affected rows when nothing changed.
    $check = $pdo->prepare( 'SELECT id FROM artist_enquiries WHERE id = :id' );
    $check->execute( [ ':id' => $id ] );

    if ( ! $check->fetch() ) {
        app_log( 'enquiry-update-status', 'no enquiry with id ' . $id );
        echo json_encode( [ 'success' => false, 'message' => 'Enquiry not found.' ] );
        exit;
    }

    echo json_encode( [ 'success' => true, 'message' => 'Status updated.' ] );
} catch ( Throwable $e ) {
    // Throwable, not Exception: an Error would otherwise escape as a 500.
    app_log( 'enquiry-update-status', 'failed to update enquiry ' . $id, $e );
    echo json_encode( [ 'success' => false, 'message' => 'Something went wrong. Please try again.' ] );
}
