<?php
header( 'Content-Type: application/json' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed.' ] );
    exit;
}

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

if ( ! isset( $_SESSION['user_id'] ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Please log in first.' ] );
    exit;
}

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf();

$currentPassword = isset( $_POST['current_password'] ) ? $_POST['current_password'] : '';
$newPassword    = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';
$confirmPassword = isset( $_POST['confirm_password'] ) ? $_POST['confirm_password'] : '';

if ( empty( $currentPassword ) || empty( $newPassword ) || empty( $confirmPassword ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Please fill in all fields.' ] );
    exit;
}

if ( $newPassword !== $confirmPassword ) {
    echo json_encode( [ 'success' => false, 'message' => 'New passwords do not match.' ] );
    exit;
}

$policyError = password_policy_error( $newPassword );
if ( $policyError !== '' ) {
    echo json_encode( [ 'success' => false, 'message' => $policyError ] );
    exit;
}

// Refuse a "change" that keeps the same password, which would otherwise look
// like a successful change while leaving the old credential in force.
if ( $newPassword === $currentPassword ) {
    echo json_encode( [ 'success' => false, 'message' => 'New password must be different from the current password.' ] );
    exit;
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare( 'SELECT password_hash FROM users WHERE id = :id LIMIT 1' );
    $stmt->execute( [ ':id' => $_SESSION['user_id'] ] );
    $user = $stmt->fetch();

    if ( ! $user || ! password_verify( $currentPassword, $user['password_hash'] ) ) {
        // Repeated wrong current-password attempts are throttled too, otherwise
        // this endpoint is a free oracle for guessing the existing password.
        $throttleKey = 'pwchange:' . ( isset( $_SESSION['user_id'] ) ? $_SESSION['user_id'] : 'anon' );
        login_record_attempt( $pdo, $throttleKey, 'email', false );

        if ( login_is_locked( $pdo, $throttleKey, 'email' ) ) {
            http_response_code( 429 );
            echo json_encode( [ 'success' => false, 'message' => 'Too many attempts. Please wait before trying again.' ] );
            exit;
        }

        echo json_encode( [ 'success' => false, 'message' => 'Current password is incorrect.' ] );
        exit;
    }

    $newHash = password_hash( $newPassword, PASSWORD_BCRYPT );
    $update  = $pdo->prepare( 'UPDATE users SET password_hash = :hash WHERE id = :id LIMIT 1' );
    $update->execute( [
        ':hash' => $newHash,
        ':id'   => $_SESSION['user_id'],
    ] );

    // Clear the throttling history now that the real password is proven.
    login_clear_attempts( $pdo, 'pwchange:' . $_SESSION['user_id'], 'email' );

    // Re-point this session at the new password and give it a new id, so the
    // caller's own session survives while every other session that signed in
    // with the old password fails the fingerprint check in the guard.
    session_bind_password( $newHash );
    session_regenerate_id( true );

    echo json_encode( [ 'success' => true, 'message' => 'Password changed successfully. Other devices have been signed out.' ] );
} catch ( Throwable $e ) {
    app_log( 'change-password', 'failed for user ' . ( $_SESSION['user_id'] ?? 'unknown' ), $e );
    echo json_encode( [ 'success' => false, 'message' => 'Something went wrong. Please try again.' ] );
}

