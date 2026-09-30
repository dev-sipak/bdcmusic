<?php
/**
 * Shared helper functions and constants.
 * Include this file wherever needed: require_once __DIR__ . '/helpers.php';
 */

if ( ! defined( 'HELPERS_LOADED' ) ) {
	define( 'HELPERS_LOADED', true );
}

// Sanitization

/**
 * Trim and strip HTML tags from a plain-text value.
 *
 * Named clean_text rather than the old sanitize_input because it is not
 * sanitisation in the output-encoding sense and must never be treated as such:
 * it makes a value *safe to store*, not safe to *print*. Anything echoed into
 * HTML still has to go through esc() in the JavaScript templates, or
 * htmlspecialchars() in PHP, or it will still be a stored XSS sink.
 *
 * Use validate_email() to check an address, and filter_input() with
 * FILTER_VALIDATE_INT for numbers.
 *
 * @param mixed $value
 * @return string
 */
function clean_text( $value ) {
	return trim( strip_tags( (string) $value ) );
}

/**
 * Backwards-compatible alias for clean_text().
 *
 * Kept so that a stale include which still calls the old name keeps working
 * during rollout. Remove once no caller uses it.
 */
function sanitize_input( $value ) {
	return clean_text( $value );
}


function sanitize_email( $value ) {
	return filter_var( trim( $value ), FILTER_SANITIZE_EMAIL );
}

function validate_email( $value ) {
	return filter_var( $value, FILTER_VALIDATE_EMAIL );
}

// JSON Storage

function ensure_storage_dir() {
	$dir = __DIR__ . '/../data';
	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0755, true );
	}
}

function load_json_file( $path, $default = [] ) {
	if ( ! file_exists( $path ) ) {
		return $default;
	}
	$content = file_get_contents( $path );
	if ( $content === false ) {
		return $default;
	}
	$data = json_decode( $content, true );
	if ( ! is_array( $data ) ) {
		return $default;
	}
	return $data;
}

function save_json_file( $path, $data ) {
	// Bookings are stored in MySQL. JSON files were the old storage and are no
	// longer written for bookings; this helper is kept only for non-booking
	// config caches and should not be reintroduced as a booking sink.
	ensure_storage_dir();
	$json = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	if ( $json === false ) {
		return false;
	}
	return file_put_contents( $path, $json ) !== false;
}

// Service Constants

define( 'BOOKING_STATUSES', [
	'Pending',
	'Processing',
	'Hold',
	'Delivered',
	'Cancelled',
]);

// Password Policy

// Minimum accepted password length. Signup enforces this; existing accounts are
// not forced to change, but the login path flags anything below it for a reset.
const PASSWORD_MIN_LENGTH = 10;

/**
 * Check a candidate password against the signup policy.
 *
 * Length is the dominant factor, so it is checked first and the complexity rules
 * are deliberately light: a long passphrase should pass, not be punished for
 * lacking a symbol.
 *
 * @param string $password
 * @return string Human-readable reason, or '' when the password is acceptable.
 */
function password_policy_error( $password ) {
	$length = strlen( $password );

	if ( $length < PASSWORD_MIN_LENGTH ) {
		return 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
	}

	if ( $length > 200 ) {
		return 'Password must be 200 characters or fewer.';
	}

	if ( ! preg_match( '/[a-z]/', $password ) || ! preg_match( '/[A-Z]/', $password ) ) {
		return 'Password must include at least one lowercase and one uppercase letter.';
	}

	if ( ! preg_match( '/[0-9]/', $password ) ) {
		return 'Password must include at least one number.';
	}

	return '';
}

// Password-change session invalidation

/**
 * Record which password this session signed in with.
 *
 * A password change has to end the attacker's session as well as the owner's,
 * but the server has no list of a user's sessions to revoke. Instead each
 * session stores a one-way fingerprint of the hash it authenticated against,
 * and the guard re-checks it. A session left over from before the change no
 * longer matches and is rejected.
 *
 * @param string $passwordHash The stored users.password_hash value.
 */
function session_bind_password( $passwordHash ) {
	$_SESSION['auth_fingerprint'] = substr( hash( 'sha256', (string) $passwordHash ), 0, 32 );
	$_SESSION['regenerated_at']  = time();
}

/**
 * Is the signed-in session still valid for this user's current password?
 *
 * @param PDO   $pdo
 * @param string $userId
 * @return bool
 */
function session_password_is_current( PDO $pdo, $userId ) {
	if ( ! isset( $_SESSION['auth_fingerprint'] ) ) {
		// A session created before this was introduced. Nothing to compare
		// against, so adopt the current hash rather than force a re-login.
		try {
			$stmt = $pdo->prepare( 'SELECT password_hash FROM users WHERE id = :id LIMIT 1' );
			$stmt->execute( [ ':id' => $userId ] );
			$hash = $stmt->fetchColumn();

			if ( $hash !== false && $hash !== null ) {
				session_bind_password( $hash );
				return true;
			}
		} catch ( PDOException $e ) {
			app_log( 'session-guard', 'could not read password hash', $e );
		}

		return true;
	}

	$stmt = $pdo->prepare( 'SELECT password_hash FROM users WHERE id = :id LIMIT 1' );
	$stmt->execute( [ ':id' => $userId ] );
	$hash = $stmt->fetchColumn();

	if ( $hash === false || $hash === null ) {
		return false;
	}

	return hash_equals( substr( hash( 'sha256', (string) $hash ), 0, 32 ), (string) $_SESSION['auth_fingerprint'] );
}

/**
 * Require an authenticated API session holding the given role.
 *
 * Every JSON endpoint used to repeat the same role check inline. They are all
 * replaced by this one call so the password-fingerprint check cannot be left off
 * a single endpoint: changing a password invalidates that user's other sessions,
 * and the page guards alone do not stop one of those sessions from calling the
 * JSON endpoints directly, which is exactly the hole this closes.
 *
 * @param string   $role Expected $_SESSION['user_role'], e.g. 'admin'.
 * @param PDO|null $pdo  An existing connection, when the caller already has one.
 * @return PDO The connection the endpoint should keep using.
 */
function require_api_role( $role, $pdo = null ) {
	if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== $role ) {
		http_response_code( 403 );
		echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
		exit;
	}

	// In practice every caller has already required database.php, but resolving
	// the connection here keeps the helper safe to use on its own.
	if ( $pdo === null ) {
		if ( ! function_exists( 'db_connect' ) ) {
			require_once __DIR__ . '/database.php';
		}

		$pdo = db_connect();
	}

	if ( ! session_password_is_current( $pdo, $_SESSION['user_id'] ) ) {
		// The password behind this session is no longer the one that signed in,
		// so the session is stale rather than merely unauthenticated.
		app_log( 'session-guard', 'stale session rejected for user ' . $_SESSION['user_id'] );

		if ( function_exists( 'app_session_destroy' ) ) {
			app_session_destroy();
		} else {
			$_SESSION = array();
		}

		http_response_code( 401 );
		echo json_encode( [ 'success' => false, 'message' => 'Your password has changed. Please sign in again.' ] );
		exit;
	}

	return $pdo;
}

// Login Throttling

// Failed attempts tolerated per identifier inside the window.
const LOGIN_MAX_ATTEMPTS = 5;

// Length of the sliding window, in seconds.
const LOGIN_ATTEMPT_WINDOW = 900; // 15 minutes

/**
 * The client IP to throttle on.
 *
 * Only REMOTE_ADDR is trusted. X-Forwarded-For is deliberately ignored, because
 * a client can send any value it likes, so trusting it would let an attacker
 * rotate the identity they are throttled under and bypass the limit entirely.
 *
 * @return string
 */
function login_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
	return substr( $ip, 0, 45 );
}

/**
 * Count recent failed attempts for one identifier.
 *
 * @param PDO    $pdo
 * @param string $identifier
 * @param string $scope       'email' or 'ip'.
 * @return int
 */
function login_failed_count( PDO $pdo, $identifier, $scope ) {
	$stmt = $pdo->prepare(
		'SELECT COUNT(*) FROM login_attempts
          WHERE identifier = :identifier
            AND scope = :scope
            AND succeeded = 0
            AND attempted_at > ( NOW() - INTERVAL :window SECOND )'
	);
	$stmt->bindValue( ':identifier', $identifier );
	$stmt->bindValue( ':scope', $scope );
	$stmt->bindValue( ':window', LOGIN_ATTEMPT_WINDOW, PDO::PARAM_INT );
	$stmt->execute();

	return (int) $stmt->fetchColumn();
}

/**
 * How long the caller must wait, in seconds, before trying again.
 *
 * @param PDO    $pdo
 * @param string $identifier
 * @param string $scope
 * @return int Seconds remaining, or 0 when not locked out.
 */
function login_retry_after( PDO $pdo, $identifier, $scope ) {
	$stmt = $pdo->prepare(
		'SELECT TIMESTAMPDIFF( SECOND, MAX(attempted_at), NOW() ) AS elapsed
           FROM login_attempts
          WHERE identifier = :identifier
            AND scope = :scope
            AND succeeded = 0
            AND attempted_at > ( NOW() - INTERVAL :window SECOND )'
	);
	$stmt->bindValue( ':identifier', $identifier );
	$stmt->bindValue( ':scope', $scope );
	$stmt->bindValue( ':window', LOGIN_ATTEMPT_WINDOW, PDO::PARAM_INT );
	$stmt->execute();

	$elapsed = $stmt->fetchColumn();

	if ( $elapsed === false || $elapsed === null ) {
		return 0;
	}

	$remaining = LOGIN_ATTEMPT_WINDOW - (int) $elapsed;

	return $remaining > 0 ? $remaining : 0;
}

/**
 * Is this identifier currently locked out?
 *
 * @param PDO    $pdo
 * @param string $identifier
 * @param string $scope
 * @return bool
 */
function login_is_locked( PDO $pdo, $identifier, $scope ) {
	return login_failed_count( $pdo, $identifier, $scope ) >= LOGIN_MAX_ATTEMPTS;
}

/**
 * Record the outcome of a sign-in attempt.
 *
 * @param PDO    $pdo
 * @param string $identifier
 * @param string $scope
 * @param bool   $succeeded
 */
function login_record_attempt( PDO $pdo, $identifier, $scope, $succeeded ) {
	$stmt = $pdo->prepare(
		'INSERT INTO login_attempts (identifier, scope, attempted_at, succeeded)
         VALUES (:identifier, :scope, NOW(), :succeeded)'
	);
	$stmt->bindValue( ':identifier', $identifier );
	$stmt->bindValue( ':scope', $scope );
	$stmt->bindValue( ':succeeded', $succeeded ? 1 : 0, PDO::PARAM_INT );

	try {
		$stmt->execute();
	} catch ( PDOException $e ) {
		// Throttling must never be the reason a legitimate person cannot sign
		// in, so a failure to record is logged and otherwise ignored.
		app_log( 'login-throttle', 'could not record attempt', $e );
	}
}

/**
 * Clear the failure history for an identifier after a successful sign-in.
 *
 * @param PDO    $pdo
 * @param string $identifier
 * @param string $scope
 */
function login_clear_attempts( PDO $pdo, $identifier, $scope ) {
	try {
		$stmt = $pdo->prepare( 'DELETE FROM login_attempts WHERE identifier = :identifier AND scope = :scope' );
		$stmt->execute( [ ':identifier' => $identifier, ':scope' => $scope ] );
	} catch ( PDOException $e ) {
		app_log( 'login-throttle', 'could not clear attempts', $e );
	}
}

/**
 * Prune attempt rows older than the window. Called opportunistically on login.
 *
 * @param PDO $pdo
 */
function login_prune_attempts( PDO $pdo ) {
	try {
		$stmt = $pdo->prepare( 'DELETE FROM login_attempts WHERE attempted_at < ( NOW() - INTERVAL :window SECOND )' );
		$stmt->bindValue( ':window', LOGIN_ATTEMPT_WINDOW, PDO::PARAM_INT );
		$stmt->execute();
	} catch ( PDOException $e ) {
		app_log( 'login-throttle', 'could not prune attempts', $e );
	}
}

/**
 * Count every recent attempt for an identifier, successful or not.
 *
 * login_failed_count() deliberately ignores successes, because for a sign-in a
 * success clears the slate. Throttles that limit total activity rather than
 * consecutive failures, such as the public enquiry form, need this instead.
 *
 * @param PDO    $pdo
 * @param string $identifier
 * @param string $scope
 * @param int    $window Seconds to look back.
 * @return int
 */
function throttle_count_all( PDO $pdo, $identifier, $scope, $window ) {
	$stmt = $pdo->prepare(
		'SELECT COUNT(*) FROM login_attempts
          WHERE identifier = :identifier
            AND scope = :scope
            AND attempted_at > ( NOW() - INTERVAL :window SECOND )'
	);
	$stmt->bindValue( ':identifier', $identifier );
	$stmt->bindValue( ':scope', $scope );
	$stmt->bindValue( ':window', (int) $window, PDO::PARAM_INT );
	$stmt->execute();

	return (int) $stmt->fetchColumn();
}

/**
 * Discard attempt rows outside the window. Called opportunistically.
 *
 * @param PDO $pdo
 * @param int $window Seconds to keep.
 */
function throttle_prune( PDO $pdo, $window ) {
	try {
		$stmt = $pdo->prepare( 'DELETE FROM login_attempts WHERE attempted_at < ( NOW() - INTERVAL :window SECOND )' );
		$stmt->bindValue( ':window', (int) $window, PDO::PARAM_INT );
		$stmt->execute();
	} catch ( PDOException $e ) {
		app_log( 'throttle', 'could not prune attempts', $e );
	}
}

// Error logging


/**
 * Record a server-side problem that the customer must not see.
 *
 * Every endpoint answers the browser with a generic message and logs the real
 * reason here, so a support request can be diagnosed from the log without the
 * detail ever reaching the client. In production the message goes to the PHP
 * error log; locally it also echoes to the response so it is visible during
 * development.
 *
 * Never pass customer data or secrets to this: it writes to a shared log.
 *
 * @param string         $channel Endpoint or subsystem name, e.g. 'artist-save'.
 * @param string         $message What went wrong.
 * @param \Throwable|null $error   The caught error, when there is one.
 */
function app_log( $channel, $message, $error = null ) {
	$line = '[' . $channel . '] ' . $message;

	if ( $error instanceof \Throwable ) {
		$line .= ' | ' . get_class( $error ) . ': ' . $error->getMessage()
			. ' @ ' . basename( $error->getFile() ) . ':' . $error->getLine();
	}

	$production = defined( 'IS_PRODUCTION' ) ? IS_PRODUCTION : false;

	if ( $production ) {
		error_log( $line );
		return;
	}

	error_log( $line );

	if ( ! headers_sent() ) {
		header( 'X-Debug-Error: ' . str_replace( array( "\r", "\n" ), ' ', $line ) );
	}
}

// CSRF Protection

function generate_csrf_token() {
	if ( session_status() === PHP_SESSION_NONE ) {
require_once __DIR__ . '/session.php';
	}
	if ( empty( $_SESSION['csrf_token'] ) ) {
		$_SESSION['csrf_token'] = bin2hex( random_bytes( 32 ) );
	}
	return $_SESSION['csrf_token'];
}

function csrf_field() {
	return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars( generate_csrf_token() ) . '">';
}

function verify_csrf_token( $token ) {
	if ( session_status() === PHP_SESSION_NONE ) {
require_once __DIR__ . '/session.php';
	}
	return isset( $_SESSION['csrf_token'] ) && hash_equals( $_SESSION['csrf_token'], $token );
}

/**
 * Read the CSRF token out of a request, whether it arrived as a form field, a
 * JSON body field or a request header.
 * @return string
 */
function csrf_token_from_request() {
	if ( isset( $_POST['csrf_token'] ) ) {
		return (string) $_POST['csrf_token'];
	}

	if ( ! empty( $_SERVER['HTTP_X_CSRF_TOKEN'] ) ) {
		return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
	}

	// Only decode a JSON body here; callers that already decoded it pass the
	// value to verify_csrf_token() directly.
	$raw = file_get_contents( 'php://input' );
	if ( $raw !== false && $raw !== '' ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) && isset( $decoded['csrf_token'] ) ) {
			return (string) $decoded['csrf_token'];
		}
	}

	return '';
}

/**
 * Enforce CSRF on a state-changing endpoint.
 *
 * Session cookies are sent automatically by the browser, so without this any
 * page on the internet could make a logged-in admin or customer submit a
 * state-changing request. The token is only readable by same-origin JS, so it
 * must be sent explicitly.
 *
 * Call this as the first check after the method and auth checks. It always
 * exits on failure so the caller cannot forget to handle the rejection.
 *
 * @param array|null $decodedInput Already-decoded JSON body, to avoid decoding twice.
 */
function require_csrf( $decodedInput = null ) {
	$token = '';
	if ( is_array( $decodedInput ) && isset( $decodedInput['csrf_token'] ) ) {
		$token = (string) $decodedInput['csrf_token'];
	}
	if ( $token === '' ) {
		$token = csrf_token_from_request();
	}

	if ( $token === '' || ! verify_csrf_token( $token ) ) {
		http_response_code( 403 );
		if ( ! headers_sent() ) {
			header( 'Content-Type: application/json' );
		}
		echo json_encode( [ 'success' => false, 'message' => 'Invalid or missing CSRF token.' ] );
		exit;
	}
}

// Booking ID Generator

function generate_booking_id() {
	return 'BDCM-' . strtoupper( bin2hex( random_bytes( 3 ) ) );
}

// URL Helpers

function url( $path = '' ) {
	global $siteUrl;
	$base = defined( 'APP_BASE_URL' ) ? APP_BASE_URL : ( $siteUrl ?? '/' );
	return $base . ltrim( $path, '/' );
}

function asset( $path = '' ) {
	global $assetPath;
	$assets = defined( 'APP_BASE_URL' ) ? APP_BASE_URL . 'assets/' : ( $assetPath ?? '/assets/' );
	return $assets . ltrim( $path, '/' );
}

// Output Helpers

function e( $value ) {
	echo htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function status_badge_html( $status ) {
	$class = 'panel-status-badge status-' . strtolower( htmlspecialchars( $status ) );
	return '<span class="' . $class . '">' . htmlspecialchars( $status ) . '</span>';
}

// --- Image Processing ---

function imageResizeAndConvert( $sourcePath, $targetWidth = 250, $targetHeight = 360, $outputPath = '' ) {
	$info = @getimagesize( $sourcePath );
	if ( $info === false ) {
		return false;
	}

	$mime = $info[ 'mime' ];
	switch ( $mime ) {
		case 'image/jpeg':
			$src = imagecreatefromjpeg( $sourcePath );
			break;
		case 'image/png':
			$src = imagecreatefrompng( $sourcePath );
			break;
		case 'image/webp':
			$src = imagecreatefromwebp( $sourcePath );
			break;
		default:
			return false;
	}

	if ( ! $src ) {
		return false;
	}

	$srcW = imagesx( $src );
	$srcH = imagesy( $src );

	$srcRatio = $srcW / $srcH;
	$tgtRatio = $targetWidth / $targetHeight;

	if ( $srcRatio > $tgtRatio ) {
		$cropH = $srcH;
		$cropW = (int) ( $srcH * $tgtRatio );
		$cropX = (int) ( ( $srcW - $cropW ) / 2 );
		$cropY = 0;
	} else {
		$cropW = $srcW;
		$cropH = (int) ( $srcW / $tgtRatio );
		$cropX = 0;
		$cropY = (int) ( ( $srcH - $cropH ) / 2 );
	}

	$dst = imagecreatetruecolor( $targetWidth, $targetHeight );
	imagecopyresampled( $dst, $src, 0, 0, $cropX, $cropY, $targetWidth, $targetHeight, $cropW, $cropH );

	imagedestroy( $src );

	$targetPath = ! empty( $outputPath ) ? $outputPath : preg_replace( '/\.[^.]+$/', '.webp', $sourcePath );
	$result = imagewebp( $dst, $targetPath, 90 );
	imagedestroy( $dst );

	return $result ? $targetPath : false;
}

// Order Read Helpers
// Shared by the admin and customer order APIs so both read the same snapshot
// and present the same numbers. Plan and payment data always come from the
// snapshot columns on bookings plus the booking_items / booking_payments
// tables, never from the live service_plans catalogue, so a later price change
// cannot rewrite what an already-placed order says it cost.
//
// Add-ons are gone: the `booking_addons` table has been dropped and nothing
// writes add-on lines any more, so no endpoint returns an `addons` key. The
// `bookings.addons_total` column is NOT NULL and stays for schema stability, but
// nothing new ever writes a non-zero value into it.

/**
 * Human label for a payment status.
 *
 * Covers both bookings.payment_status (awaiting/paid/refunded/failed/cancelled)
 * and booking_payments.status (created/paid/failed/cancelled/refunded).
 *
 * @param string $status
 * @return string
 */
function booking_payment_status_label( $status ) {
	$labels = [
		'awaiting'  => 'Awaiting Payment',
		'created'   => 'Payment Initiated',
		'paid'      => 'Paid',
		'refunded'  => 'Refunded',
		'failed'    => 'Failed',
		'cancelled' => 'Cancelled',
	];

	$status = (string) $status;

	return $labels[ $status ] ?? ( $status !== '' ? ucfirst( $status ) : '' );
}

/**
 * Normalise the money columns snapshotted on a bookings row.
 *
 * Accepts either the raw column name (price) or the legacy `amount` alias the
 * existing list endpoints select it under, so callers can pass their own row
 * without having to re-alias.
 *
 * @param array $row
 * @return array
 */
function booking_order_amounts( array $row ) {
	$total = $row['price'] ?? $row['amount'] ?? 0;

	return [
		'subtotal' => (float) ( $row['subtotal'] ?? 0 ),
		'total'    => (float) $total,
		'currency' => (string) ( $row['currency'] ?? 'INR' ),
	];
}

/**
 * The package snapshot recorded on a bookings row.
 *
 * Orders placed before packages existed have no plan_id and carry a
 * placeholder plan_name, so hasPlan lets the UI tell a real package from a
 * legacy row rather than rendering the placeholder as something purchasable.
 *
 * @param array $row
 * @return array
 */
function booking_order_plan( array $row ) {
	$planId = $row['plan_id'] ?? null;
	$hasPlan = $planId !== null && $planId !== '';

	return [
		'hasPlan'    => $hasPlan,
		'id'         => $hasPlan ? (int) $planId : null,
		'name'       => (string) ( $row['plan_name'] ?? '' ),
		'group'      => (string) ( $row['plan_group'] ?? '' ),
		'groupLabel' => (string) ( $row['plan_group_label'] ?? '' ),
	];
}

/**
 * Package lines for the given bookings, grouped by booking id.
 *
 * One booking can carry several packages, and `bookings.plan_id` only holds
 * the first of them, so this is the authoritative list of what was bought. The
 * rows backfill from `bookings.plan_id` for orders placed before line items
 * existed, which is why an old single-package order returns exactly the line
 * its snapshot columns already describe.
 *
 * @param PDO   $pdo
 * @param array $bookingIds
 * @return array booking_id => list of package lines
 */
function booking_order_items( $pdo, array $bookingIds ) {
	$bookingIds = array_values( array_unique( array_filter( array_map( 'strval', $bookingIds ) ) ) );

	if ( empty( $bookingIds ) ) {
		return [];
	}

	$placeholders = implode( ',', array_fill( 0, count( $bookingIds ), '?' ) );
	$stmt = $pdo->prepare(
		'SELECT booking_id, plan_id, plan_name, plan_group, plan_group_label,
                unit_price, qty, line_total
         FROM booking_items
         WHERE booking_id IN (' . $placeholders . ')
         ORDER BY id'
	);
	$stmt->execute( $bookingIds );

	$map = [];
	foreach ( $stmt->fetchAll() as $line ) {
		$map[ $line['booking_id'] ][] = [
			'id'         => (int) $line['plan_id'],
			'name'       => (string) $line['plan_name'],
			'group'      => (string) $line['plan_group'],
			'groupLabel' => (string) ( $line['plan_group_label'] ?? '' ),
			'qty'        => (int) $line['qty'],
			'unitPrice'  => (float) $line['unit_price'],
			'lineTotal'  => (float) $line['line_total'],
		];
	}

	return $map;
}

/**
 * Every payment attempt recorded against a booking, newest first.
 *
 * razorpay_signature is reported as a boolean only: the admin UI needs to know
 * a signature was captured, never the value itself.
 *
 * @param PDO   $pdo
 * @param string $bookingId
 * @return array
 */
function booking_order_payments( $pdo, $bookingId ) {
	$stmt = $pdo->prepare(
		'SELECT id, provider, razorpay_order_id, razorpay_payment_id, razorpay_signature,
                amount, currency, status, method, failure_reason, created_at, paid_at
         FROM booking_payments
         WHERE booking_id = :bid
         ORDER BY id DESC'
	);
	$stmt->execute( [ ':bid' => (string) $bookingId ] );

	$attempts = [];
	foreach ( $stmt->fetchAll() as $row ) {
		$attempts[] = [
			'id'                => (int) $row['id'],
			'provider'          => (string) $row['provider'],
			'razorpayOrderId'   => (string) ( $row['razorpay_order_id'] ?? '' ),
			'razorpayPaymentId' => (string) ( $row['razorpay_payment_id'] ?? '' ),
			'hasSignature'      => ! empty( $row['razorpay_signature'] ),
			'amount'            => (float) $row['amount'],
			'currency'          => (string) $row['currency'],
			'status'            => (string) $row['status'],
			'statusLabel'       => booking_payment_status_label( $row['status'] ),
			'method'            => (string) ( $row['method'] ?? '' ),
			'failureReason'     => (string) ( $row['failure_reason'] ?? '' ),
			'createdAt'         => (string) $row['created_at'],
			'paidAt'            => $row['paid_at'] ?: null,
		];
	}

	return $attempts;
}

/**
 * Payment summary safe to send to a customer.
 *
 * Deliberately omits the provider reference ids, the signature flag and the
 * failure reason: those are admin-side payment internals and are not part of
 * what the customer bought or owes.
 *
 * @param array $row
 * @return array
 */
function booking_order_payment_customer( array $row ) {
	return [
		'status'      => (string) ( $row['payment_status'] ?? '' ),
		'statusLabel' => booking_payment_status_label( $row['payment_status'] ?? '' ),
		'paidAt'      => $row['paid_at'] ?? null,
	];
}

/**
 * Whether a customer has a booking for a given service.
 *
 * The dashboard pages gate every service section on this, and the customer APIs
 * gate the matching endpoints on it, so a customer who never bought a service
 * cannot reach its data by calling the API directly. This mirrors the check in
 * dashboard/includes/panel-guard.php, which resolves the same list for the
 * navigation.
 *
 * @param PDO    $pdo
 * @param string $customerId users.id, which is a varchar id and never an integer.
 * @param string $slug       services.slug.
 * @param bool   $requireUnlocked Only count bookings that have reached one of
 *                                SERVICE_UNLOCK_STATUSES.
 * @return bool
 */
function customer_has_service_booking( $pdo, $customerId, $slug, $requireUnlocked = false ) {
	if ( $requireUnlocked ) {
		// service-fields.php is a pure definition file, so it is safe to pull in
		// here. It has to be pulled in: the unlock statuses used to be read
		// behind a defined() guard, which meant a caller that had not included
		// service-fields.php silently dropped the unlock requirement and counted
		// bookings in any status. That failed open, so a customer whose only
		// booking was still pending or cancelled passed an API check that the
		// dashboard page refused.
		require_once __DIR__ . '/service-fields.php';

		// Fail closed. If the unlock list ever fails to load, deny the section
		// rather than treating every booking as unlocked.
		if ( ! defined( 'SERVICE_UNLOCK_STATUSES' ) || empty( SERVICE_UNLOCK_STATUSES ) ) {
			app_log( 'entitlements', 'unlock statuses unavailable for ' . $slug );

			return false;
		}
	}

	$sql    = 'SELECT COUNT(*) FROM bookings b
               JOIN services s ON b.service_id = s.id
               WHERE b.customer_id = :cid AND s.slug = :slug';
	$params = array( ':cid' => $customerId, ':slug' => $slug );

	if ( $requireUnlocked ) {
		$placeholders = array();
		foreach ( array_values( SERVICE_UNLOCK_STATUSES ) as $i => $unlockStatus ) {
			$placeholders[]           = ':unlock' . $i;
			$params[ ':unlock' . $i ] = $unlockStatus;
		}
		$sql .= ' AND b.status IN (' . implode( ',', $placeholders ) . ')';
	}

	$stmt = $pdo->prepare( $sql );
	$stmt->execute( $params );

	return (int) $stmt->fetchColumn() > 0;
}

// Razorpay Gateway
// Two thin calls against the Orders and Payments APIs. The official SDK is used
// when it is installed, and cURL otherwise, so adding the key id and the secret
// to .env is genuinely the only step needed to switch Razorpay on: this project
// ships no vendor directory and no composer.json.
//
// The key secret never leaves this server. Only the key id is handed to the
// browser, which is public by design.

/**
 * Call a Razorpay REST endpoint with the server's credentials.
 *
 * @param string $method    'GET' or 'POST'.
 * @param string $path      e.g. '/v1/orders'.
 * @param string $keyId     RAZORPAY_KEY_ID.
 * @param string $keySecret RAZORPAY_KEY_SECRET.
 * @param array  $payload   Request body, JSON encoded for POST.
 * @return array Decoded response.
 * @throws RuntimeException On a transport error, a non-2xx status, or a body
 *                         that is not JSON. The message is the gateway's own.
 */
function booking_razorpay_request( $method, $path, $keyId, $keySecret, array $payload = [] ) {
	if ( ! function_exists( 'curl_init' ) ) {
		throw new RuntimeException( 'The server cannot reach Razorpay: the cURL extension is not loaded.' );
	}

	$curl = curl_init( 'https://api.razorpay.com' . $path );

	$headers = [
		'Authorization: Basic ' . base64_encode( $keyId . ':' . $keySecret ),
		'Content-Type: application/json',
	];

	curl_setopt_array( $curl, [
		CURLOPT_CUSTOMREQUEST  => $method,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 20,
		CURLOPT_HTTPHEADER     => $headers,
	] );

	if ( $method === 'POST' ) {
		curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $payload ) );
	}

	$body   = curl_exec( $curl );
	$error  = curl_error( $curl );
	$status = (int) curl_getinfo( $curl, CURLINFO_HTTP_CODE );

	curl_close( $curl );

	if ( $body === false || $error !== '' ) {
		throw new RuntimeException( 'Razorpay request failed: ' . ( $error !== '' ? $error : 'no response' ) );
	}

	$decoded = json_decode( (string) $body, true );

	if ( $status < 200 || $status >= 300 ) {
		$message = isset( $decoded['error']['description'] )
			? (string) $decoded['error']['description']
			: 'HTTP ' . $status;

		throw new RuntimeException( 'Razorpay returned ' . $message );
	}

	if ( ! is_array( $decoded ) ) {
		throw new RuntimeException( 'Razorpay returned an unreadable response.' );
	}

	return $decoded;
}

/**
 * Create a Razorpay order for a booking.
 *
 * @param string $keyId      RAZORPAY_KEY_ID.
 * @param string $keySecret  RAZORPAY_KEY_SECRET.
 * @param string $receipt    The booking id, so the gateway order is traceable
 *                           back to the booking it pays for.
 * @param int    $amountPaise Total in paise, the smallest unit Razorpay uses.
 * @return string The gateway order id.
 * @throws RuntimeException
 */
function booking_razorpay_order_create( $keyId, $keySecret, $receipt, $amountPaise ) {
	$payload = [
		'receipt'  => (string) $receipt,
		'amount'   => (int) $amountPaise,
		'currency' => 'INR',
	];

	if ( class_exists( 'Razorpay\Api\Api' ) ) {
		$api   = new Razorpay\Api\Api( $keyId, $keySecret );
		$order = $api->order->create( $payload );

		return (string) $order['id'];
	}

	$order = booking_razorpay_request( 'POST', '/v1/orders', $keyId, $keySecret, $payload );

	return (string) ( $order['id'] ?? '' );
}

/**
 * Fetch a payment from Razorpay, so the captured amount and state can be
 * checked rather than taken on trust.
 *
 * @param string $keyId     RAZORPAY_KEY_ID.
 * @param string $keySecret RAZORPAY_KEY_SECRET.
 * @param string $paymentId The payment id returned by the checkout.
 * @return array
 * @throws RuntimeException
 */
function booking_razorpay_payment_fetch( $keyId, $keySecret, $paymentId ) {
	if ( class_exists( 'Razorpay\Api\Api' ) ) {
		$api     = new Razorpay\Api\Api( $keyId, $keySecret );
		$payment = $api->payment->fetch( $paymentId );

		return (array) $payment;
	}

	return booking_razorpay_request(
		'GET',
		'/v1/payments/' . rawurlencode( (string) $paymentId ),
		$keyId,
		$keySecret
	);
}
