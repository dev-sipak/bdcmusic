<?php
/**
 * Confirm a Razorpay payment and settle the booking.
 *
 * This is the only place a booking becomes 'paid', so it is also the only place
 * a client could try to convince the server that money arrived. It therefore
 * trusts nothing it is sent: the order id is used to look up a row this server
 * created, the amount is compared against the amount that row recorded, and the
 * signature is verified with the key secret.
 *
 * Checks, in order:
 *   1. POST only, and the body is parsed as JSON or form data.
 *   2. A CSRF token and the three Razorpay fields are present.
 *   3. This session created the booking being paid for.
 *   4. booking_payments has a row with that razorpay_order_id, still 'created'.
 *      The unique index on that column is what stops a client inventing one.
 *   5. The booking is still awaiting payment, so a replay cannot re-settle it.
 *   6. HMAC( razorpay_order_id . '|' . razorpay_payment_id ) matches.
 *   7. The amount Razorpay actually captured equals the amount we recorded.
 *   8. One transaction marks the payment paid and the booking paid.
 *
 * Demo mode (outside production only) skips 6 and 7 because there is no gateway
 * to ask, but still enforces 1-5, so it cannot confirm a booking this session
 * did not create.
 *
 * Responds JSON to the checkout script.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/booking-session.php';
require_once __DIR__ . '/notifications.php';

$razorpayInclude = dirname( __DIR__ ) . '/vendor/razorpay/razorpay/src/Api.php';
if ( file_exists( $razorpayInclude ) ) {
	require_once $razorpayInclude;
}

if ( ! booking_ensure_session() ) {
	http_response_code( 500 );
	header( 'Content-Type: application/json' );
	echo json_encode( array(
		'success' => false,
		'message' => 'Booking is temporarily unavailable. Please try again.',
	) );
	exit;
}

header( 'Content-Type: application/json' );

/**
 * Report a failed verification.
 *
 * The HTTP status is chosen for the caller, not for the browser: a 4xx is a
 * request the checkout script should surface, while a 5xx is our problem and is
 * logged rather than detailed to the customer.
 *
 * @param int    $status  HTTP status.
 * @param string $message Customer-facing message.
 * @param string $detail  Server-side reason, for the log.
 */
function booking_verify_fail( $status, $message, $detail = '' ) {
	if ( $detail !== '' && ! IS_PRODUCTION ) {
		error_log( '[razorpay-verify] ' . $detail );
	}

	http_response_code( $status );
	echo json_encode( array( 'success' => false, 'message' => $message ) );
	exit;
}

// 1 ── Method and body ────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	booking_verify_fail( 405, 'This endpoint only accepts POST.' );
}

$input = $_POST;

if ( ! $input ) {
	// The checkout script sends FormData, but Razorpay's own handler posts JSON,
	// so both are accepted rather than one silently reading as empty.
	$raw = file_get_contents( 'php://input' );

	if ( $raw ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			$input = $decoded;
		}
	}
}

if ( ! $input ) {
	booking_verify_fail( 400, 'We did not receive any payment details.' );
}

// 2 ── Required fields ────────────────────────────────────────────────
$token     = isset( $input['csrf_token'] ) ? (string) $input['csrf_token'] : '';
$bookingId = isset( $input['booking_id'] ) ? clean_text( $input['booking_id'] ) : '';
$orderId   = isset( $input['razorpay_order_id'] ) ? clean_text( $input['razorpay_order_id'] ) : '';
$paymentId = isset( $input['razorpay_payment_id'] ) ? clean_text( $input['razorpay_payment_id'] ) : '';
$signature = isset( $input['razorpay_signature'] ) ? (string) $input['razorpay_signature'] : '';

if ( ! verify_csrf_token( $token ) ) {
		booking_verify_fail( 403, 'Your session has expired. Please try paying again.' );
}

if ( $bookingId === '' || $orderId === '' || $paymentId === '' || $signature === '' ) {
	booking_verify_fail( 400, 'The payment response was incomplete. Please try again.' );
}

// 3 ── This session must have created the booking ─────────────────────
$pending = isset( $_SESSION['booking_pending_payment'] ) ? (string) $_SESSION['booking_pending_payment'] : '';

if ( ! hash_equals( $pending, $bookingId ) ) {
	booking_verify_fail(
		403,
		'We could not match this payment to your booking. If you were charged, our team will confirm it by email.',
		'session marker ' . ( $pending === '' ? 'missing' : 'did not match' ) . ' for ' . $bookingId
	);
}

$pdb = db_connect();

// 4 ── The order must be one we created, and still unpaid ────────────
$stmt = $pdb->prepare( 'SELECT b.id AS booking_row_id, b.booking_id, b.payment_status, b.status,
                               b.customer_email, b.customer_name, b.customer_phone, b.service_name, b.plan_name, b.price, b.currency,
                               p.id AS payment_row_id, p.status AS payment_status_attempt,
                               p.amount AS payment_amount, p.currency AS payment_currency
                        FROM booking_payments p
                        JOIN bookings b ON b.booking_id = p.booking_id
                        WHERE p.razorpay_order_id = :order_id
                        LIMIT 1' );
$stmt->execute( array( ':order_id' => $orderId ) );
$row = $stmt->fetch( PDO::FETCH_ASSOC );

if ( ! $row ) {
	booking_verify_fail(
		404,
		'We do not recognise this payment. If you were charged, please contact us with your order reference.',
		'no booking_payments row for order ' . $orderId
	);
}

if ( ! hash_equals( (string) $row['booking_id'], $bookingId ) ) {
	booking_verify_fail(
		403,
		'This payment does not match your booking.',
		'order ' . $orderId . ' belongs to ' . $row['booking_id'] . ', not ' . $bookingId
	);
}

// 5 ── Not already settled, and the booking still awaiting payment ───
if ( (string) $row['payment_status_attempt'] !== 'created' ) {
	booking_verify_fail(
		409,
		'This payment has already been processed. Refresh the page to see your booking.',
		'attempt already ' . $row['payment_status_attempt']
	);
}

if ( (string) $row['payment_status'] !== 'awaiting' ) {
	booking_verify_fail(
		409,
		'This booking is not awaiting payment. Please contact us if you believe this is wrong.',
		'booking payment_status ' . $row['payment_status']
	);
}

$expectedAmount = (float) $row['payment_amount'];
$method         = 'razorpay';

// 6 and 7 ── Signature and captured amount ───────────────────────────
$demo = BOOKING_DEMO && strpos( $orderId, 'order_demo_' ) === 0;

if ( $demo ) {
	// No gateway was involved, so there is nothing to verify against. Checks 1-5
	// have already run, so this can only settle a booking this session created
	// for a price this server recorded.
	$method = 'demo';
} else {
	$keySecret = (string) RAZORPAY_KEY_SECRET;

	if ( $keySecret === '' ) {
		booking_verify_fail(
			503,
			'We could not confirm the payment just made. Our team will email you shortly to confirm your booking.',
			'RAZORPAY_KEY_SECRET is empty while APP_ENV=' . APP_ENV
		);
	}

	$expected = hash_hmac( 'sha256', $orderId . '|' . $paymentId, $keySecret );

	if ( ! hash_equals( $expected, $signature ) ) {
		$pdb->prepare( 'UPDATE booking_payments
                         SET status = :status, failure_reason = :reason
                       WHERE id = :id' )->execute( array(
			':status' => 'failed',
			':reason' => 'Signature mismatch',
			':id'     => (int) $row['payment_row_id'],
		) );

		booking_verify_fail( 400, 'The payment could not be verified. Please contact us.', 'signature mismatch' );
	}

	// The signature only proves Razorpay signed these two ids. It says nothing
	// about how much was taken, so the captured amount is fetched and compared.
	try {
		$payment = booking_razorpay_payment_fetch( (string) RAZORPAY_KEY_ID, $keySecret, $paymentId );
	} catch ( Throwable $e ) {
		booking_verify_fail( 502, 'We could not reach the payment gateway. Please try again.', $e->getMessage() );
	}

	// Razorpay reports money in the smallest unit, so `amount` is paise, while
	// booking_payments.amount is rupees. Comparing them as-is would reject every
	// real payment, so the expected figure is converted before it is compared.
	$capturedPaise = isset( $payment['amount'] ) ? (int) $payment['amount'] : 0;
	$expectedPaise = (int) round( $expectedAmount * 100 );

	if ( $capturedPaise !== $expectedPaise ) {
		$pdb->prepare( 'UPDATE booking_payments
                         SET status = :status, failure_reason = :reason
                       WHERE id = :id' )->execute( array(
			':status' => 'failed',
			':reason' => 'Amount mismatch',
			':id'     => (int) $row['payment_row_id'],
		) );

		booking_verify_fail(
			400,
			'The amount paid does not match your booking. Our team will contact you to resolve it.',
			'captured ' . $capturedPaise . 'p vs expected ' . $expectedPaise . 'p'
		);
	}

	// A valid signature can exist for a payment that was attempted but never
	// captured, so the state is checked rather than assumed.
	$paymentState = (string) ( $payment['status'] ?? '' );

	if ( $paymentState !== 'captured' && $paymentState !== 'paid' ) {
		$pdb->prepare( 'UPDATE booking_payments
                         SET status = :status, failure_reason = :reason
                       WHERE id = :id' )->execute( array(
			':status' => 'failed',
			':reason' => 'Payment not captured',
			':id'     => (int) $row['payment_row_id'],
		) );

		booking_verify_fail(
			400,
			'The payment did not go through. Please try again or contact us.',
			'gateway state ' . ( $paymentState === '' ? '(none)' : $paymentState )
		);
	}

	// The signature ties the order to the payment, but confirming the payment
	// really belongs to the order we created costs nothing and closes the gap
	// if the gateway ever reports a payment against a different one.
	if ( ! empty( $payment['order_id'] ) && (string) $payment['order_id'] !== $orderId ) {
		$pdb->prepare( 'UPDATE booking_payments
                         SET status = :status, failure_reason = :reason
                       WHERE id = :id' )->execute( array(
			':status' => 'failed',
			':reason' => 'Payment belongs to another order',
			':id'     => (int) $row['payment_row_id'],
		) );

		booking_verify_fail( 400, 'The payment could not be verified. Please contact us.', 'order_id mismatch' );
	}

	if ( ! empty( $payment['method'] ) ) {
		$method = (string) $payment['method'];
	}
}

// 8 ── Settle, in one transaction ────────────────────────────────────
$pdb->beginTransaction();

try {
	$paidAt = date( 'Y-m-d H:i:s' );

	$pdb->prepare( 'UPDATE booking_payments
                     SET status = :status, razorpay_payment_id = :payment_id,
                         razorpay_signature = :signature, method = :method,
                         amount = :amount, paid_at = :paid_at
                     WHERE id = :id AND status = :created' )->execute( array(
		':status'    => 'paid',
		':payment_id' => $paymentId,
		':signature' => $signature,
		':method'    => $method,
		':amount'    => $expectedAmount,
		':paid_at'   => $paidAt,
		':id'        => (int) $row['payment_row_id'],
		':created'   => 'created',
	) );

	$pdb->prepare( 'UPDATE bookings
                     SET payment_status = :payment_status, payment_id = :payment_id,
                         razorpay_signature = :signature, payment_method = :method,
                         paid_at = :paid_at
                     WHERE id = :id AND payment_status = :awaiting' )->execute( array(
		':payment_status' => 'paid',
		':payment_id'     => $paymentId,
		':signature'      => $signature,
		':method'         => $method,
		':paid_at'        => $paidAt,
		':id'             => (int) $row['booking_row_id'],
		':awaiting'       => 'awaiting',
	) );

	// Both UPDATEs are guarded on the status they expect, so a second concurrent
	// confirmation changes nothing and the commit is still correct.
	$pdb->commit();
} catch ( Throwable $e ) {
	$pdb->rollBack();
	booking_verify_fail( 500, 'We could not record your payment. Our team will email you shortly.', $e->getMessage() );
}

// The marker is single-use, so the same payment cannot be replayed through this
// session even if the gateway is asked twice.
unset( $_SESSION['booking_pending_payment'] );

// The confirmation page still needs proof this booking is the caller's, and a
// guest has no account to check against.
$_SESSION['booking_confirmed'] = (string) $row['booking_id'];

// The money has moved, so the customer gets a payment confirmation and the admin
// gets the same status change they would see from the panel.
$order = array(
	'booking_id'   => (string) $row['booking_id'],
	'name'         => (string) $row['customer_name'],
	'email'        => (string) $row['customer_email'],
	'phone'        => (string) $row['customer_phone'],
	'service_name' => (string) $row['service_name'],
	'plan_name'    => (string) $row['plan_name'],
	'total'        => $expectedAmount,
	'status'       => 'Paid',
);

notify_order_status_changed( $order, 'awaiting payment', 'paid' );
notify_admin_order_status_changed( $order, 'awaiting payment', 'paid' );

echo json_encode( array(
	'success'    => true,
	'booking_id' => (string) $row['booking_id'],
	'amount'     => $expectedAmount,
	'currency'   => (string) $row['currency'],
	'method'     => $method,
) );
