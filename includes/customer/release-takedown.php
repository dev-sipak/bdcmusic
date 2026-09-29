<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'customer' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed' ] );
    exit;
}

$input = json_decode( file_get_contents( 'php://input' ), true );
if ( ! $input ) { $input = $_POST; }

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf( $input );

$id    = intval( $input['id'] ?? 0 );
$reason = clean_text( $input['reason'] ?? '' );

if ( $id <= 0 ) {
    echo json_encode( [ 'success' => false, 'message' => 'Invalid release ID.' ] );
    exit;
}

try {
    // Releases belong to Digital Music Distribution, so the same entitlement the
    // page gate in dashboard/releases.php applies has to be enforced here too. The
    // true asks for an *unlocked* booking (SERVICE_UNLOCK_STATUSES: processing or
    // delivered). Without it a customer whose only distribution booking is still
    // pending or cancelled would be refused the page but could still reach this
    // API by calling it directly, which is the inconsistency this flag closes.
    if ( ! customer_has_service_booking( $pdo, $_SESSION['user_id'], 'digital-distribution', true ) ) {
        http_response_code( 403 );
        echo json_encode( [ 'success' => false, 'message' => 'You have not purchased this service.' ] );
        exit;
    }
    // Existence and ownership are confirmed with a SELECT scoped to this
    // customer rather than by trusting rowCount(), which reports 0 both when the
    // row is missing and when the row matched but nothing changed. The lookup is
    // ownership-scoped, so a release belonging to somebody else is reported as
    // not found and its existence is not disclosed.
    $own = $pdo->prepare( 'SELECT status FROM releases WHERE id = :id AND customer_id = :cid LIMIT 1' );
    $own->execute( [ ':id' => $id, ':cid' => $_SESSION['user_id'] ] );
    $currentStatus = $own->fetchColumn();

    if ( $currentStatus === false ) {
        http_response_code( 404 );
        echo json_encode( [ 'success' => false, 'message' => 'Release not found.' ] );
        exit;
    }

    if ( ! in_array( $currentStatus, [ 'live', 'approved' ], true ) ) {
        $message = ( $currentStatus === 'takedown' )
            ? 'A takedown request for this release is already in progress.'
            : 'Only a live or approved release can be taken down.';

        echo json_encode( [ 'success' => false, 'message' => $message ] );
        exit;
    }

    // The status change and its history entry belong together: a takedown the
    // customer can see in their tracker has to carry a matching history row.
    $pdo->beginTransaction();

    $stmt = $pdo->prepare( 'UPDATE releases SET status = :status WHERE id = :id AND customer_id = :cid AND status IN ("live","approved")' );
    $stmt->execute( [ ':status' => 'takedown', ':id' => $id, ':cid' => $_SESSION['user_id'] ] );

    $hstmt = $pdo->prepare( 'INSERT INTO release_history (release_id, action, message) VALUES (:rid, :action, :msg)' );
    $hstmt->execute( [ ':rid' => $id, ':action' => 'Takedown Requested', ':msg' => $reason ?: 'Takedown requested by customer.' ] );

    $pdo->commit();

    echo json_encode( [ 'success' => true, 'message' => 'Takedown request submitted.' ] );
} catch ( Throwable $e ) {
    if ( isset( $pdo ) && $pdo->inTransaction() ) {
        $pdo->rollBack();
    }

    app_log( 'release-takedown', 'request failed', $e );
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'The takedown request could not be recorded. Please try again.' ] );
}
