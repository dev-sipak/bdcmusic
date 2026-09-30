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

$raw           = file_get_contents( 'php://input' );
$input         = json_decode( $raw === false ? '' : $raw, true );

if ( ! is_array( $input ) ) {
	$input = array();
}

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf( $input );

$id             = intval( $input['id'] ?? 0 );
$reply_message  = trim( $input['reply_message'] ?? '' );

if ( $id <= 0 || $reply_message === '' ) {
	echo json_encode( [ 'success' => false, 'message' => 'Invalid data. Please provide an enquiry ID and reply message.' ] );
	exit;
}

if ( strlen( $reply_message ) > 5000 ) {
	echo json_encode( [ 'success' => false, 'message' => 'Reply is too long.' ] );
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

	$adminEmail  = getenv( 'BOOKING_OWNER_EMAIL' ) ?: 'bdcmusic37@gmail.com';
	$siteName    = 'BDC Music Studio';
	$enquiryEmail = sanitize_email( $enquiry['email'] );
	$enquiryName  = clean_text( $enquiry['name'] );

	$subject = 'Reply to your enquiry - ' . $siteName;

	$emailBody = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">'
		. '<h2 style="color:#333;">Reply to Your Enquiry</h2>'
		. '<p>Hi ' . htmlspecialchars( $enquiryName ) . ',</p>'
		. '<p>Thank you for reaching out to us. Here is our reply to your enquiry:</p>'
		. '<div style="background:#f5f5f5;padding:16px;border-radius:8px;margin:16px 0;">'
		. '<p style="margin:0 0 8px;font-weight:bold;color:#555;">Your original message:</p>'
		. '<p style="margin:0;color:#777;font-style:italic;">' . nl2br( htmlspecialchars( $enquiry['message'] ?: 'No message' ) ) . '</p>'
		. '</div>'
		. '<div style="background:#e8f4fd;padding:16px;border-radius:8px;margin:16px 0;">'
		. '<p style="margin:0 0 8px;font-weight:bold;color:#333;">Our reply:</p>'
		. '<p style="margin:0;color:#333;">' . nl2br( htmlspecialchars( $reply_message ) ) . '</p>'
		. '</div>'
		. '<p style="color:#999;font-size:12px;margin-top:24px;">This is a reply from ' . htmlspecialchars( $siteName ) . '. Please do not reply to this email directly.</p>'
		. '</div>';

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers .= 'From: ' . $siteName . ' <' . $adminEmail . '>' . "\r\n";

	// The mail is sent first and its result checked. Marking the enquiry
	// replied before knowing the mail left would silently lose the reply, and
	// telling the admin "sent" when mail() failed would be worse.
	$sent = @mail( $enquiryEmail, $subject, $emailBody, $headers );

	if ( ! $sent ) {
		app_log( 'enquiry-reply', 'mail() failed for enquiry ' . $id );
		echo json_encode( [ 'success' => false, 'message' => 'The reply could not be sent. The enquiry is still open.' ] );
		exit;
	}

	$updateStmt->execute( [ ':status' => 'replied', ':id' => $id ] );

	echo json_encode( [ 'success' => true, 'message' => 'Reply sent and enquiry marked as replied.' ] );
} catch ( Throwable $e ) {
	// Throwable, not Exception: a TypeError or Error would otherwise escape as
	// an unhandled fatal and return an HTML 500 to the admin panel.
	app_log( 'enquiry-reply', 'failed to reply to enquiry ' . $id, $e );
	echo json_encode( [ 'success' => false, 'message' => 'Something went wrong. Please try again.' ] );
}
