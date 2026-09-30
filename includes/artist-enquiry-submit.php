<?php
/**
 * Public artist enquiry submission.
 *
 * This endpoint is reachable without signing in, so it is the easiest thing on
 * the site to abuse. The protections here are layered:
 *   - POST only, and JSON or form encoded.
 *   - A same-origin check, because an anonymous visitor has no session and so
 *     cannot hold a CSRF token.
 *   - A honeypot field that a human never sees and a bot fills in.
 *   - Per-IP and per-email throttling, so it cannot be used to flood the
 *     enquiries table or to harvest addresses for spam.
 *   - A duplicate guard, so an impatient double-click does not create two rows.
 *   - Length and existence checks, so a row can never exceed its column or point
 *     at an artist that does not exist.
 */
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/notifications.php';

header( 'Content-Type: application/json' );

// Enquiries allowed per identifier inside the throttle window.
const ENQUIRY_MAX_PER_WINDOW = 3;

// Throttle window for enquiries, in seconds.
const ENQUIRY_WINDOW = 3600; // 1 hour

// Longest message accepted, in characters. The column is TEXT, so without this
// a single request could store tens of kilobytes.
const ENQUIRY_MAX_MESSAGE = 2000;

/**
 * Fail with a JSON body and stop.
 *
 * @param int    $status
 * @param string $message
 */
function enquiry_fail( $status, $message ) {
	http_response_code( $status );
	echo json_encode( [ 'success' => false, 'message' => $message ] );
	exit;
}

/**
 * Is this request coming from a page served by this site?
 *
 * Origin is checked first because it is set by the browser on every POST and
 * cannot be forged by page JavaScript. Referer is a weaker fallback: it is not
 * sent for some privacy configurations, so a missing Referer is treated as
 * "cannot tell" rather than as a failure.
 *
 * @return bool
 */
function enquiry_is_same_origin() {
	$host = $_SERVER['HTTP_HOST'] ?? '';

	if ( ! empty( $_SERVER['HTTP_ORIGIN'] ) && $host !== '' ) {
		$originHost = parse_url( (string) $_SERVER['HTTP_ORIGIN'], PHP_URL_HOST );
		$originPort = parse_url( (string) $_SERVER['HTTP_ORIGIN'], PHP_URL_PORT );
		$expected   = parse_url( 'http://' . $host, PHP_URL_HOST );
		$expectedPort = parse_url( 'http://' . $host, PHP_URL_PORT );

		if ( $originHost && strtolower( $originHost ) === strtolower( (string) $expected ) ) {
			$a = $originPort ? (int) $originPort : 80;
			$b = $expectedPort ? (int) $expectedPort : 80;
			return $a === $b;
		}

		return false;
	}

	if ( ! empty( $_SERVER['HTTP_REFERER'] ) && $host !== '' ) {
		$refHost = parse_url( (string) $_SERVER['HTTP_REFERER'], PHP_URL_HOST );
		$expected = parse_url( 'http://' . $host, PHP_URL_HOST );

		return $refHost && strtolower( $refHost ) === strtolower( (string) $expected );
	}

	return true;
}

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	enquiry_fail( 405, 'Method not allowed' );
}

$raw   = file_get_contents( 'php://input' );
$input = json_decode( $raw === false ? '' : $raw, true );

if ( ! is_array( $input ) ) {
	$input = $_POST;
}

// Honeypot: a real person never fills this in, because it is hidden and not
// wired to any label. Answering with a plain success keeps a bot from learning
// that it was detected.
if ( ! empty( $input['website'] ) || ! empty( $input['company_website'] ) ) {
	echo json_encode( [ 'success' => true, 'message' => 'Enquiry submitted successfully. We will get back to you soon.' ] );
	exit;
}

if ( ! enquiry_is_same_origin() ) {
	enquiry_fail( 403, 'This request did not come from this site.' );
}

$artistIdRaw = $input['artist_id'] ?? 0;
$artistId    = filter_var( $artistIdRaw, FILTER_VALIDATE_INT );
$name        = clean_text( $input['name'] ?? '' );
$emailRaw    = trim( (string) ( $input['email'] ?? '' ) );
$phone       = clean_text( $input['phone'] ?? '' );
$message     = clean_text( $input['message'] ?? '' );

if ( ! $artistId || $artistId <= 0 ) {
	enquiry_fail( 400, 'That artist could not be identified.' );
}

if ( $name === '' || $emailRaw === '' || $message === '' ) {
	enquiry_fail( 400, 'Please provide your name, email and a message.' );
}

if ( ! validate_email( $emailRaw ) ) {
	enquiry_fail( 400, 'Please provide a valid email address.' );
}

$email = strtolower( sanitize_email( $emailRaw ) );

// Lengths are checked against the column widths so a long value fails with a
// clear message instead of a driver error.
if ( strlen( $name ) > 150 ) {
	enquiry_fail( 400, 'Please keep your name under 150 characters.' );
}

if ( strlen( $email ) > 200 ) {
	enquiry_fail( 400, 'Please keep your email address under 200 characters.' );
}

if ( strlen( $phone ) > 20 ) {
	enquiry_fail( 400, 'Please enter a contact number of 20 digits or fewer.' );
}

if ( strlen( $message ) > ENQUIRY_MAX_MESSAGE ) {
	enquiry_fail( 400, 'Please keep your message under ' . ENQUIRY_MAX_MESSAGE . ' characters.' );
}

if ( $phone !== '' && ! preg_match( '/^\+?[0-9\s\-()]{6,20}$/', $phone ) ) {
	enquiry_fail( 400, 'Please enter a valid contact number.' );
}

$ipKey    = login_client_ip();
$emailKey = $email;

try {
	$pdo = db_connect();

	// Total-activity throttle on both the client and the address. Sign-in
	// throttling only counts consecutive failures, which is the wrong measure
	// here: a successful submission is exactly the thing being limited.
	throttle_prune( $pdo, ENQUIRY_WINDOW );

	$ipAttempts    = throttle_count_all( $pdo, 'enquiry:ip:' . $ipKey, 'ip', ENQUIRY_WINDOW );
	$emailAttempts = throttle_count_all( $pdo, 'enquiry:email:' . $emailKey, 'email', ENQUIRY_WINDOW );

	if ( $ipAttempts >= ENQUIRY_MAX_PER_WINDOW || $emailAttempts >= ENQUIRY_MAX_PER_WINDOW ) {
		app_log( 'artist-enquiry', 'throttled ip=' . $ipKey . ' email=' . $emailKey );
		enquiry_fail( 429, 'You have sent several enquiries recently. Please try again later.' );
	}

	// The column has no foreign key, so an unknown id would otherwise be stored
	// and later shown against the wrong artist.
	$artistCheck = $pdo->prepare( 'SELECT id, name FROM artists WHERE id = :id LIMIT 1' );
	$artistCheck->execute( [ ':id' => $artistId ] );
	$artist = $artistCheck->fetch();

	if ( ! $artist ) {
		enquiry_fail( 404, 'That artist could not be found.' );
	}

	// An impatient double-click should not create two identical rows.
	$dupe = $pdo->prepare(
		'SELECT id FROM artist_enquiries
          WHERE artist_id = :aid
            AND email = :email
            AND message = :msg
            AND created_at > ( NOW() - INTERVAL 10 MINUTE )
          LIMIT 1'
	);
	$dupe->execute( [ ':aid' => $artistId, ':email' => $email, ':msg' => $message ] );

	if ( $dupe->fetch() ) {
		// Already recorded, so confirm rather than store a second copy.
		echo json_encode( [ 'success' => true, 'message' => 'Enquiry submitted successfully. We will get back to you soon.' ] );
		exit;
	}

	$stmt = $pdo->prepare(
		'INSERT INTO artist_enquiries (artist_id, name, email, phone, message)
         VALUES (:aid, :name, :email, :phone, :msg)'
	);
	$stmt->execute( [
		':aid'   => $artistId,
		':name'  => $name,
		':email' => $email,
		':phone' => $phone !== '' ? $phone : null,
		':msg'   => $message,
	] );

	// Recorded whether or not the address is later confirmed deliverable, so
	// the allowance reflects enquiries actually sent.
	login_record_attempt( $pdo, 'enquiry:ip:' . $ipKey, 'ip', true );
	login_record_attempt( $pdo, 'enquiry:email:' . $emailKey, 'email', true );

	notify_admin_new_enquiry( array(
		'name'        => $name,
		'email'       => $email,
		'phone'       => $phone,
		'message'     => $message,
		'artist_name' => (string) $artist['name'],
	) );

	echo json_encode( [ 'success' => true, 'message' => 'Enquiry submitted successfully. We will get back to you soon.' ] );
} catch ( Throwable $e ) {
	app_log( 'artist-enquiry', 'submission failed', $e );
	enquiry_fail( 500, 'Something went wrong. Please try again later.' );
}
