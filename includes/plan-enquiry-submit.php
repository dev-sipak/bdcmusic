<?php
/**
 * Plan enquiry — the "Enquire Now" path for a quoted tier.
 *
 * `service_plans.is_enquiry = 1` marks a tier with no price the checkout could
 * charge, so `booking_resolve_selection()` refuses it and it can never be bought.
 * It is not dropped from the catalogue either: it is a real product with a real
 * scope, it is just priced per project. Those tiers post here.
 *
 * The enquiry is stored as the same zero-value "quote" booking Promotion already
 * produces — `payment_provider = 'manual'`, `payment_status = 'awaiting'`, price
 * 0 — plus a `booking_items` line carrying the plan. Reusing that shape means the
 * admin order screen, both dashboards and booking-thank-you.php all describe it
 * correctly with no new screen, and it shows up where the team already works.
 *
 * POST only. Requires: booking-catalog, booking-session, booking-registry.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/booking-catalog.php';
require_once __DIR__ . '/booking-session.php';
require_once __DIR__ . '/booking-registry.php';
require_once __DIR__ . '/notifications.php';

/**
 * Send the browser back to the service page with a readable reason.
 *
 * The modal is a plain form rather than an XHR, so a failure has to leave a
 * trace the page can render. Everything that can go wrong here is a validation
 * failure the visitor can fix, not a system error worth a stack trace.
 *
 * @param string $slug  services.slug, to rebuild the return URL.
 * @param string $message
 * @return void
 */
function plan_enquiry_fail( $slug, $message ) {
	$return = booking_service_page_url( $slug );

	header( 'Location: ' . $return . ( strpos( $return, '?' ) === false ? '?' : '&' )
		. 'enquiry_error=' . rawurlencode( $message ) );
	exit;
}

if ( ! booking_ensure_session() ) {
	http_response_code( 500 );
	exit( 'Enquiries are temporarily unavailable. Please try again.' );
}

if ( ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) !== 'POST' ) {
	http_response_code( 405 );
	exit( 'Method not allowed.' );
}

$token = isset( $_POST['csrf_token'] ) ? (string) $_POST['csrf_token'] : '';
if ( ! verify_csrf_token( $token ) ) {
	http_response_code( 419 );
	exit( 'Your session has expired. Please reload the page and try again.' );
}

$pdb    = db_connect();
$planId = isset( $_POST['plan_id'] ) ? (int) $_POST['plan_id'] : 0;
$plan   = $planId > 0 ? booking_plan( $pdb, $planId ) : null;

// Only a tier the catalogue itself marks as an enquiry can be enquired about.
// A fixed-price plan is refused here for the same reason it is refused at
// checkout: the customer must not be able to turn a purchasable package into a
// free "quote" request, or a quoted one into a purchase.
if ( ! $plan || empty( $plan['is_enquiry'] ) ) {
	http_response_code( 400 );
	exit( 'That package is not available for an enquiry.' );
}

$service = booking_service_by_id( $pdb, (int) $plan['service_id'] );
if ( ! $service ) {
	http_response_code( 400 );
	exit( 'That package is not available for an enquiry.' );
}

$slug = (string) $service['slug'];

list( $contact, $contactErrors ) = booking_validate_contact( $_POST );
if ( $contactErrors ) {
	plan_enquiry_fail( $slug, reset( $contactErrors ) );
}

$meta = array(
	'service_category' => booking_av_group_category( $plan['group_key'] ),
	'service_type'     => (string) ( $plan['group_label'] !== null && $plan['group_label'] !== ''
		? $plan['group_label']
		: $plan['group_key'] ),
);

// Optional context. Kept even though the registry does not ask for it on the
// paid path, because a quote with no budget and no deadline costs a round trip
// to obtain.
foreach ( array( 'deadline', 'budget' ) as $key ) {
	$value = isset( $_POST[ $key ] ) && ! is_array( $_POST[ $key ] ) ? clean_text( $_POST[ $key ] ) : '';
	if ( $value !== '' ) {
		$meta[ $key ] = $value;
	}
}

$message = isset( $_POST['message'] ) && ! is_array( $_POST['message'] )
	? clean_text( $_POST['message'] )
	: '';
if ( strlen( $message ) > 2000 ) {
	$message = substr( $message, 0, 2000 );
}

$customerId = ! empty( $_SESSION['user_id'] ) ? (string) $_SESSION['user_id'] : null;
$bookingId  = generate_booking_id();

$pdb->beginTransaction();

try {
	// plan_group is NOT NULL with an empty default, and the plan itself is the
	// whole point of this row, so unlike a plain quote it is snapshotted here.
	$stmt = $pdb->prepare( 'INSERT INTO bookings (
        booking_id, customer_id, customer_type,
        customer_name, customer_email, customer_phone, customer_whatsapp,
        service_id, service_name, service_slug, plan_id, plan_name,
        plan_group, plan_group_label,
        price, subtotal, addons_total, currency,
        status, payment_status, payment_provider,
        meta, message
    ) VALUES (
        :booking_id, :customer_id, :customer_type,
        :customer_name, :customer_email, :customer_phone, :customer_whatsapp,
        :service_id, :service_name, :service_slug, :plan_id, :plan_name,
        :plan_group, :plan_group_label,
        0, 0, 0, :currency,
        :status, :payment_status, :payment_provider,
        :meta, :message
    )' );

	$stmt->execute( array(
		':booking_id'        => $bookingId,
		':customer_id'       => $customerId,
		':customer_type'     => $customerId !== null ? 'registered' : 'guest',
		':customer_name'     => (string) ( $contact['name'] ?? '' ),
		':customer_email'    => (string) ( $contact['email'] ?? '' ),
		':customer_phone'    => (string) ( $contact['phone'] ?? '' ),
		':customer_whatsapp' => (string) ( $contact['whatsapp'] ?? '' ),
		':service_id'        => (int) $service['id'],
		':service_name'      => (string) $service['name'],
		':service_slug'      => $slug,
		':plan_id'           => (int) $plan['id'],
		':plan_name'         => (string) $plan['name'],
		':plan_group'        => (string) $plan['group_key'],
		':plan_group_label'  => (string) ( $plan['group_label'] ?? '' ),
		':currency'          => 'INR',
		// Nothing to pay, so the order waits for a quote rather than a payment
		// the customer was never going to make.
		':status'            => 'pending',
		':payment_status'    => 'awaiting',
		':payment_provider'  => 'manual',
		':meta'              => json_encode( $meta ),
		':message'           => $message,
	) );

	// One line item, at the price charged: nothing. Keeping the line means the
	// dashboards' item list describes this order exactly as it does a paid one.
	$item = $pdb->prepare( 'INSERT INTO booking_items
        (booking_id, plan_id, plan_name, plan_group, plan_group_label, unit_price, qty, line_total)
        VALUES (:booking_id, :plan_id, :plan_name, :plan_group, :plan_group_label, 0, 1, 0)' );

	$item->execute( array(
		':booking_id'       => $bookingId,
		':plan_id'          => (int) $plan['id'],
		':plan_name'        => (string) $plan['name'],
		':plan_group'       => (string) $plan['group_key'],
		':plan_group_label' => (string) ( $plan['group_label'] ?? '' ),
	) );

	$pay = $pdb->prepare( 'INSERT INTO booking_payments
        (booking_id, provider, razorpay_order_id, amount, currency, status)
        VALUES (:booking_id, :provider, :razorpay_order_id, :amount, :currency, :status)' );

	$pay->execute( array(
		':booking_id'        => $bookingId,
		':provider'          => 'manual',
		':razorpay_order_id' => null,
		':amount'            => 0.0,
		':currency'          => 'INR',
		':status'            => 'created',
	) );

	$pdb->commit();
} catch ( Throwable $e ) {
	$pdb->rollBack();
	plan_enquiry_fail( $slug, 'We could not save your enquiry. Please try again.' );
}

// A plan enquiry is a quote request, so no account is created; it may never
// become a purchase. Both sides are still told it arrived.
$order = array(
	'booking_id'   => $bookingId,
	'name'         => (string) ( $contact['name'] ?? '' ),
	'email'        => (string) ( $contact['email'] ?? '' ),
	'phone'        => (string) ( $contact['phone'] ?? '' ),
	'service_name' => (string) $service['name'],
	'plan_name'    => (string) $plan['name'],
	'total'        => null,
	'status'       => 'Pending',
);

notify_order_placed( $order );
notify_admin_new_order( $order );

// A guest has no account, so this session marker is the only thing that will
// let booking-thank-you.php show them their own booking later.
$_SESSION['booking_confirmed'] = $bookingId;

header( 'Location: ' . url( 'booking-thank-you.php?order=' . urlencode( $bookingId ) . '&quote=1' ) );
exit;
