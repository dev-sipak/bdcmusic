<?php
/**
 * Authentication AJAX Handler
 *
 * Handles login and signup requests via POST.
 * Expected POST parameters:
 *   action  - 'login' or 'signup'
 *   email   - user email
 *   password - user password
 *   name    - (signup only) full name
 *   phone   - (signup only) contact number
 */

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
require_once __DIR__ . '/notifications.php';

$action = isset( $_POST['action'] ) ? trim( $_POST['action'] ) : '';

try {
	$pdo = db_connect();
} catch ( PDOException $e ) {
	app_log( 'auth', 'database connection failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'Something went wrong. Please try again.' ] );
	exit;
}

if ( $action === 'login' ) {
	$email    = isset( $_POST['email'] ) ? trim( $_POST['email'] ) : '';
	$password = isset( $_POST['password'] ) ? $_POST['password'] : '';

	if ( empty( $email ) || empty( $password ) ) {
		echo json_encode( [ 'success' => false, 'message' => 'Please fill in all fields.' ] );
		exit;
	}

	if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		echo json_encode( [ 'success' => false, 'message' => 'Please enter a valid email address.' ] );
		exit;
	}

	$emailKey = strtolower( $email );
	$ipKey    = login_client_ip();

	// Two independent limits: per account, so one account cannot be ground down
	// from many addresses, and per client, so one address cannot spray many
	// accounts. Either one being tripped stops the attempt.
	if ( login_is_locked( $pdo, $emailKey, 'email' ) || login_is_locked( $pdo, $ipKey, 'ip' ) ) {
		$wait = max(
			login_retry_after( $pdo, $emailKey, 'email' ),
			login_retry_after( $pdo, $ipKey, 'ip' )
		);
		$minutes = max( 1, (int) ceil( $wait / 60 ) );

		http_response_code( 429 );
		echo json_encode( [
			'success' => false,
			'message' => 'Too many sign-in attempts. Please try again in ' . $minutes . ' minute' . ( $minutes === 1 ? '' : 's' ) . '.',
		] );
		exit;
	}

	login_prune_attempts( $pdo );

	$stmt = $pdo->prepare( 'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1' );
	$stmt->execute( [ ':email' => $emailKey ] );
	$user = $stmt->fetch();

	// A hash of a value nobody knows, verified when the account does not exist,
	// so a missing account costs the same time as a wrong password. Without
	// this, response time alone reveals which addresses are registered.
	$hashToVerify = $user ? $user['password_hash'] : '$2y$10$usesomesillystringforsalt0000000000000000000000000000000000';

	$passwordOk = password_verify( $password, $hashToVerify );

	if ( ! $user || ! $passwordOk ) {
		login_record_attempt( $pdo, $emailKey, 'email', false );
		login_record_attempt( $pdo, $ipKey, 'ip', false );

		echo json_encode( [ 'success' => false, 'message' => 'Invalid email or password.' ] );
		exit;
	}

	// Transparent upgrade when the stored hash uses older parameters or
	// algorithm than the current PHP build prefers.
	$effectiveHash = $user['password_hash'];

	if ( password_needs_rehash( $user['password_hash'], PASSWORD_BCRYPT ) ) {
		$effectiveHash = password_hash( $password, PASSWORD_BCRYPT );

		$up = $pdo->prepare( 'UPDATE users SET password_hash = :hash WHERE id = :id' );
		$up->execute( [ ':hash' => $effectiveHash, ':id' => $user['id'] ] );
	}

	login_record_attempt( $pdo, $emailKey, 'email', true );
	login_record_attempt( $pdo, $ipKey, 'ip', true );
	login_clear_attempts( $pdo, $emailKey, 'email' );
	login_clear_attempts( $pdo, $ipKey, 'ip' );

	// Regenerate session ID to prevent session fixation
	session_regenerate_id( true );
	$_SESSION['regenerated_at'] = time();

	// Must be the hash now in the database, which is the rehashed one when an
	// upgrade happened above. Binding the pre-upgrade value would make the
	// fingerprint check reject this brand-new session on the next page load.
	session_bind_password( $effectiveHash );

	$_SESSION['user_id']    = $user['id'];
	$_SESSION['user_name']  = $user['name'];
	$_SESSION['user_email'] = $user['email'];
	$_SESSION['user_role']  = $user['role'];

	$redirectUrl = $user['role'] === 'admin'
		? ( $basePath ?? '/' ) . 'bdc-admin/'
		: ( $basePath ?? '/' ) . 'dashboard/customer-dashboard';

	echo json_encode( [
		'success'    => true,
		'message'    => 'Signed in successfully! Redirecting...',
		'role'       => $user['role'],
		'redirect'   => $redirectUrl,
	] );
	exit;

} elseif ( $action === 'signup' ) {
	$name     = isset( $_POST['name'] ) ? trim( $_POST['name'] ) : '';
	$email    = isset( $_POST['email'] ) ? trim( $_POST['email'] ) : '';
	$phone    = isset( $_POST['phone'] ) ? trim( $_POST['phone'] ) : '';
	$password = isset( $_POST['password'] ) ? $_POST['password'] : '';

	if ( empty( $name ) || empty( $email ) || empty( $phone ) || empty( $password ) ) {
		echo json_encode( [ 'success' => false, 'message' => 'Please fill in all fields.' ] );
		exit;
	}

	if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		echo json_encode( [ 'success' => false, 'message' => 'Please enter a valid email address.' ] );
		exit;
	}

	$cleanPhone = preg_replace( '/[\s\-\(\)]/', '', $phone );
	if ( ! preg_match( '/^\d{10,15}$/', $cleanPhone ) ) {
		echo json_encode( [ 'success' => false, 'message' => 'Please enter a valid contact number (10-15 digits).' ] );
		exit;
	}

	$policyError = password_policy_error( $password );
	if ( $policyError !== '' ) {
		echo json_encode( [ 'success' => false, 'message' => $policyError ] );
		exit;
	}

	// Check if email already exists
	$stmt = $pdo->prepare( 'SELECT id FROM users WHERE email = :email LIMIT 1' );
	$stmt->execute( [ ':email' => strtolower( $email ) ] );
	if ( $stmt->fetch() ) {
		echo json_encode( [ 'success' => false, 'message' => 'An account with this email already exists.' ] );
		exit;
	}

	// Generate unique customer ID
	$customerId = 'CUST-' . strtoupper( substr( md5( strtolower( $email ) . microtime( true ) ), 0, 8 ) );
	$passwordHash = password_hash( $password, PASSWORD_BCRYPT );

	$stmt = $pdo->prepare(
		'INSERT INTO users (id, name, email, mobile, password_hash, role, created_at, source)
         VALUES (:id, :name, :email, :mobile, :password_hash, :role, NOW(), :source)'
	);

	$stmt->execute( [
		':id'            => $customerId,
		':name'          => $name,
		':email'         => strtolower( $email ),
		':mobile'        => $cleanPhone,
		':password_hash' => $passwordHash,
		':role'          => 'customer',
		':source'        => 'website',
	] );

	// Notify the new customer and the admin. Neither can change the signup
	// result, so a mail failure is logged and ignored.
	notify_account_created( $name, strtolower( $email ) );
	notify_admin_new_account( $name, strtolower( $email ), $cleanPhone, 'website' );

	// Auto-login after signup
	session_regenerate_id( true );
	$_SESSION['regenerated_at'] = time();

	session_bind_password( $passwordHash );

	$_SESSION['user_id']    = $customerId;
	$_SESSION['user_name']  = $name;
	$_SESSION['user_email'] = strtolower( $email );
	$_SESSION['user_role']  = 'customer';

	$redirectUrl = ( $basePath ?? '/' ) . 'dashboard/customer-dashboard';

	echo json_encode( [
		'success'  => true,
		'message'  => 'Account created successfully! Redirecting...',
		'role'     => 'customer',
		'redirect' => $redirectUrl,
	] );
	exit;

} else {
	echo json_encode( [ 'success' => false, 'message' => 'Invalid action.' ] );
	exit;
}

