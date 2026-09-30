<?php
/**
 * Admin Order Detail API
 *
 * GET ?id=<booking_id>
 *
 * Returns everything needed to render the admin order modal: the customer,
 * the package and add-ons they bought at their snapshotted prices, the fields
 * the customer submitted at order time, their uploads, the full payment
 * record (provider ids, signature-on-file, method, failure reason and every
 * attempt in booking_payments), and the arrangement (service_records row) the
 * admin has recorded for this booking.
 *
 * Admin-only. See includes/customer/order-detail.php for the customer-facing
 * counterpart, which returns the same order-time shape for a single
 * customer's own order but deliberately omits every payment internal.
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/service-fields.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

$bookingId = isset( $_GET['id'] ) ? clean_text( $_GET['id'] ) : '';
if ( $bookingId === '' ) {
	http_response_code( 400 );
	echo json_encode( [ 'success' => false, 'message' => 'Missing order id.' ] );
	exit;
}

try {
	$stmt = $pdo->prepare(
		'SELECT b.booking_id AS id, b.invoice_no, b.customer_id, u.name AS customer,
                u.email AS email, u.mobile AS phone,
                b.customer_name, b.customer_email, b.customer_phone, b.customer_whatsapp,
                b.customer_type,
                s.id AS service_id, s.slug AS service_slug, s.name AS service,
                b.status, b.payment_status, b.price AS amount, b.subtotal, b.addons_total,
                b.currency, b.plan_id, b.plan_name, b.plan_group, b.plan_group_label,
                b.payment_provider, b.payment_id, b.razorpay_order_id, b.razorpay_signature,
                b.payment_method, b.payment_failure_reason,
                b.message,
                DATE_FORMAT(b.created_at, "%Y-%m-%d %H:%i") AS date,
                DATE_FORMAT(b.paid_at, "%Y-%m-%d %H:%i") AS paid_at,
                b.meta
         FROM bookings b
         LEFT JOIN users u ON b.customer_id = u.id
         JOIN services s ON b.service_id = s.id
         WHERE b.booking_id = :id
         LIMIT 1'
	);
	$stmt->execute( [ ':id' => $bookingId ] );
	$order = $stmt->fetch();
	if ( ! $order ) {
		http_response_code( 404 );
		echo json_encode( [ 'success' => false, 'message' => 'Order not found.' ] );
		exit;
	}

	$slug = $order['service_slug'];

	// Fields the customer submitted when placing the order, labelled by the
	// same helper the customer-facing endpoint uses so both agree.
	$metaPairs = service_meta_pairs( $slug, $order['meta'] ? json_decode( $order['meta'], true ) : array() );

	// Package lines, read from the snapshot taken at order time. Every package
	// line on this order: bookings.plan_id only holds the first, so a
	// multi-package order would otherwise look like a single-package one.
	$itemsByBooking = booking_order_items( $pdo, [ $bookingId ] );
	$items = $itemsByBooking[ $bookingId ] ?? [];
	$amounts = booking_order_amounts( $order );

	// Every payment attempt, so an admin can see a failed charge that was
	// retried rather than only the final state on the booking.
	$payments = booking_order_payments( $pdo, $bookingId );

	// Admin-managed arrangement, if one has been saved for this booking.
	$rStmt = $pdo->prepare(
		'SELECT headline, sub_headline, progress, starts_on, ends_on, location, notes
         FROM service_records
         WHERE booking_id = :id
         LIMIT 1'
	);
	$rStmt->execute( [ ':id' => $bookingId ] );
	$record = $rStmt->fetch();
	if ( $record ) {
		// Blank columns read as "not filled in yet" rather than a real value.
		$record = array_filter( $record, static function ( $v ) {
			return $v !== null && $v !== '';
		} );
	} else {
		$record = null;
	}

	$fStmt = $pdo->prepare(
		'SELECT original_name, file_path, mime_type, file_size
         FROM uploaded_files
         WHERE booking_id = :id
         ORDER BY uploaded_at'
	);
	$fStmt->execute( [ ':id' => $bookingId ] );

	echo json_encode( [
		'success' => true,
		'order'   => [
			'id'                 => $order['id'],
			'invoice_no'         => $order['invoice_no'],
			'customer'           => $order['customer_name'] ?: $order['customer'],
			'email'              => $order['customer_email'] ?: $order['email'],
			'phone'              => ( $order['customer_phone'] ?: $order['phone'] ) ?: 'N/A',
			'whatsapp'           => (string) ( $order['customer_whatsapp'] ?? '' ),
			'customerType'       => $order['customer_type'],
			'service'            => $order['service'],
			'service_slug'       => $slug,
			'status'             => $order['status'],
			'payment_status'     => $order['payment_status'],
			'amount'             => $amounts['total'],
			'message'            => $order['message'] ?: '',
			'date'               => $order['date'],
			// Keyed by the DB column names so the admin form can be rendered
			// straight from these, whichever labels the service uses.
			'metaFields'         => $metaPairs,
			// Package lines and money as snapshotted at order time.
			'plan'               => booking_order_plan( $order ),
			'items'              => $items,
			'amounts'            => $amounts,
			'subtotal'           => $amounts['subtotal'],
			'currency'           => $amounts['currency'],
			// Admin-only payment internals. razorpay_signature is reduced to a
			// boolean by the helper and its value is never sent out.
			'payment'            => [
				'status'          => $order['payment_status'],
				'statusLabel'     => booking_payment_status_label( $order['payment_status'] ),
				'provider'        => (string) ( $order['payment_provider'] ?? '' ),
				'method'          => (string) ( $order['payment_method'] ?? '' ),
				'paidAt'          => $order['paid_at'] ?: null,
				'razorpayOrderId' => (string) ( $order['razorpay_order_id'] ?? '' ),
				'paymentId'       => (string) ( $order['payment_id'] ?? '' ),
				'hasSignature'    => ! empty( $order['razorpay_signature'] ),
				'failureReason'   => (string) ( $order['payment_failure_reason'] ?? '' ),
				'attempts'        => $payments,
			],
			'arrangement'        => $record,
			'arrangementFields'  => service_arrangement_fields( $slug ),
			'progressOptions'    => service_progress_options( $slug ),
			'files'              => $fStmt->fetchAll(),
		],
	] );
  } catch ( Throwable $e ) {
	  app_log( 'order-detail', 'load failed', $e );
	  http_response_code( 500 );
	  echo json_encode( [ 'success' => false, 'message' => 'The order could not be loaded. Please try again.' ] );
  }
