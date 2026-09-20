<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

header( 'Content-Type: application/json' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed' ] );
    exit;
}

$input = json_decode( file_get_contents( 'php://input' ), true );
if ( ! $input ) {
    $input = $_POST;
}

$artist_id = intval( $input['artist_id'] ?? 0 );
$name      = sanitize_input( $input['name'] ?? '' );
$email     = sanitize_email( $input['email'] ?? '' );
$phone     = sanitize_input( $input['phone'] ?? '' );
$message   = trim( $input['message'] ?? '' );

if ( $artist_id <= 0 || empty( $name ) || empty( $email ) || ! validate_email( $email ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Please provide a valid name, email, and artist reference.' ] );
    exit;
}

try {
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        'INSERT INTO artist_enquiries (artist_id, name, email, phone, message) VALUES (:aid, :name, :email, :phone, :msg)'
    );
    $stmt->execute( [
        ':aid'   => $artist_id,
        ':name'  => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':msg'   => $message,
    ] );

    echo json_encode( [ 'success' => true, 'message' => 'Enquiry submitted successfully. We will get back to you soon.' ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Something went wrong. Please try again later.' ] );
}
