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

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

$action = isset( $_POST['action'] ) ? trim( $_POST['action'] ) : '';

try {
    $pdo = db_connect();
} catch ( PDOException $e ) {
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'Database connection failed.' ] );
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

    $stmt = $pdo->prepare( 'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1' );
    $stmt->execute( [ ':email' => strtolower( $email ) ] );
    $user = $stmt->fetch();

    if ( ! $user || ! password_verify( $password, $user['password_hash'] ) ) {
        echo json_encode( [ 'success' => false, 'message' => 'Invalid email or password.' ] );
        exit;
    }

    // Regenerate session ID to prevent session fixation
    session_regenerate_id( true );

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = (int) $user['role'];

    $redirectUrl = (int) $user['role'] === 2
        ? ( $basePath ?? '/' ) . 'bdc-admin/'
        : ( $basePath ?? '/' ) . 'dashboard/customer-dashboard.php';

    echo json_encode( [
        'success'    => true,
        'message'    => 'Signed in successfully! Redirecting...',
        'role'       => (int) $user['role'],
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

    if ( strlen( $password ) < 6 ) {
        echo json_encode( [ 'success' => false, 'message' => 'Password must be at least 6 characters.' ] );
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
         VALUES (:id, :name, :email, :mobile, :password_hash, 1, NOW(), :source)'
    );

    $stmt->execute( [
        ':id'            => $customerId,
        ':name'          => $name,
        ':email'         => strtolower( $email ),
        ':mobile'        => $cleanPhone,
        ':password_hash' => $passwordHash,
        ':source'        => 'website',
    ] );

    // Auto-login after signup
    session_regenerate_id( true );

    $_SESSION['user_id']    = $customerId;
    $_SESSION['user_name']  = $name;
    $_SESSION['user_email'] = strtolower( $email );
    $_SESSION['user_role']  = 1;

    $redirectUrl = ( $basePath ?? '/' ) . 'dashboard/customer-dashboard.php';

    echo json_encode( [
        'success'  => true,
        'message'  => 'Account created successfully! Redirecting...',
        'role'     => 1,
        'redirect' => $redirectUrl,
    ] );
    exit;

} else {
    echo json_encode( [ 'success' => false, 'message' => 'Invalid action.' ] );
    exit;
}
