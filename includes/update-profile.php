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

$name  = isset( $_POST['name'] ) ? trim( $_POST['name'] ) : '';
$phone = isset( $_POST['phone'] ) ? trim( $_POST['phone'] ) : '';

if ( empty( $name ) ) {
	echo json_encode( [ 'success' => false, 'message' => 'Name is required.' ] );
	exit;
}

$cleanPhone = preg_replace( '/[\s\-\(\)]/', '', $phone );
if ( $cleanPhone !== '' && ! preg_match( '/^\d{10,15}$/', $cleanPhone ) ) {
	echo json_encode( [ 'success' => false, 'message' => 'Please enter a valid contact number (10-15 digits).' ] );
	exit;
}

try {
	$pdo  = db_connect();
	$stmt = $pdo->prepare( 'UPDATE users SET name = :name, mobile = :mobile WHERE id = :id LIMIT 1' );
	$stmt->execute( [
		':name'    => $name,
		':mobile'  => $cleanPhone,
		':id'      => $_SESSION['user_id'],
	] );

	$_SESSION['user_name'] = $name;

	echo json_encode( [ 'success' => true, 'message' => 'Profile updated successfully.' ] );
} catch ( Throwable $e ) {
	app_log( 'update-profile', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'Something went wrong. Please try again.' ] );
}
