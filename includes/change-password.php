<?php
header( 'Content-Type: application/json' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed.' ] );
    exit;
}

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

if ( ! isset( $_SESSION['user_id'] ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Please log in first.' ] );
    exit;
}

$currentPassword = isset( $_POST['current_password'] ) ? $_POST['current_password'] : '';
$newPassword    = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';
$confirmPassword = isset( $_POST['confirm_password'] ) ? $_POST['confirm_password'] : '';

if ( empty( $currentPassword ) || empty( $newPassword ) || empty( $confirmPassword ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Please fill in all fields.' ] );
    exit;
}

if ( strlen( $newPassword ) < 6 ) {
    echo json_encode( [ 'success' => false, 'message' => 'New password must be at least 6 characters.' ] );
    exit;
}

if ( $newPassword !== $confirmPassword ) {
    echo json_encode( [ 'success' => false, 'message' => 'New passwords do not match.' ] );
    exit;
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare( 'SELECT password_hash FROM users WHERE id = :id LIMIT 1' );
    $stmt->execute( [ ':id' => $_SESSION['user_id'] ] );
    $user = $stmt->fetch();

    if ( ! $user || ! password_verify( $currentPassword, $user['password_hash'] ) ) {
        echo json_encode( [ 'success' => false, 'message' => 'Current password is incorrect.' ] );
        exit;
    }

    $newHash = password_hash( $newPassword, PASSWORD_BCRYPT );
    $update  = $pdo->prepare( 'UPDATE users SET password_hash = :hash WHERE id = :id LIMIT 1' );
    $update->execute( [
        ':hash' => $newHash,
        ':id'   => $_SESSION['user_id'],
    ] );

    echo json_encode( [ 'success' => true, 'message' => 'Password changed successfully.' ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Something went wrong. Please try again.' ] );
}
