<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'admin' ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
    exit;
}

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed' ] );
    exit;
}

$input          = json_decode( file_get_contents( 'php://input' ), true );
$id             = intval( $input['id'] ?? 0 );
$reply_message  = trim( $input['reply_message'] ?? '' );

if ( $id <= 0 || empty( $reply_message ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Invalid data. Please provide an enquiry ID and reply message.' ] );
    exit;
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare( 'SELECT id, name, email, message FROM artist_enquiries WHERE id = :id' );
    $stmt->execute( [ ':id' => $id ] );
    $enquiry = $stmt->fetch();

    if ( ! $enquiry ) {
        echo json_encode( [ 'success' => false, 'message' => 'Enquiry not found.' ] );
        exit;
    }

    $updateStmt = $pdo->prepare( 'UPDATE artist_enquiries SET status = :status WHERE id = :id' );
    $updateStmt->execute( [ ':status' => 'replied', ':id' => $id ] );

    $adminEmail  = getenv( 'BOOKING_OWNER_EMAIL' ) ?: 'bdcmusic37@gmail.com';
    $siteName    = 'BDC Music Studio';
    $enquiryEmail = sanitize_email( $enquiry['email'] );
    $enquiryName  = sanitize_input( $enquiry['name'] );

    $subject = 'Reply to your enquiry - ' . $siteName;

    $emailBody = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">'
        . '<h2 style="color:#333;">Reply to Your Enquiry</h2>'
        . '<p>Hi ' . htmlspecialchars( $enquiryName ) ',</p>'
        . '<p>Thank you for reaching out to us. Here is our reply to your enquiry:</p>'
        . '<div style="background:#f5f5f5;padding:16px;border-radius:8px;margin:16px 0;">'
        . '<p style="margin:0 0 8px;font-weight:bold;color:#555;">Your original message:</p>'
        . '<p style="margin:0;color:#777;font-style:italic;">' . nl2br( htmlspecialchars( $enquiry['message'] ?: 'No message' ) ) . '</p>'
        . '</div>'
        . '<div style="background:#e8f4fd;padding:16px;border-radius:8px;margin:16px 0;">'
        . '<p style="margin:0 0 8px;font-weight:bold;color:#333;">Our reply:</p>'
        . '<p style="margin:0;color:#333;">' . nl2br( htmlspecialchars( $reply_message ) ) . '</p>'
        . '</div>'
        . '<p style="color:#999;font-size:12px;margin-top:24px;">This is a reply from ' . htmlspecialchars( $siteName ) '. Please do not reply to this email directly.</p>'
        . '</div>';

    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
    $headers .= 'From: ' . $siteName . ' <' . $adminEmail . '>' . "\r\n";

    @mail( $enquiryEmail, $subject, $emailBody, $headers );

    echo json_encode( [ 'success' => true, 'message' => 'Reply sent and enquiry marked as replied.' ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
