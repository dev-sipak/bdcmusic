<?php
/**
 * Single order detail API for the customer dashboard.
 *
 * GET ?id=<bookings.booking_id>
 *
 * Returns the complete record of one order belonging to the signed-in
 * customer: the package and add-ons they bought at their snapshotted prices,
 * every field they filled in at order time (decoded from bookings.meta and
 * mapped to human labels), the files they uploaded, and the arrangements the
 * admin has recorded in service_records.
 *
 * Replaces the old standalone "Order Details" page, which is now the View More
 * dialog on My Orders. The customer_id filter is what keeps orders private,
 * and payment internals (provider ids, signature, failure reasons) are never
 * included in this response.
 */
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/service-fields.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'customer' );

$bookingId = isset( $_GET['id'] ) ? clean_text( $_GET['id'] ) : '';

if ( $bookingId === '' ) {
	echo json_encode( [ 'success' => false, 'message' => 'Order not found.' ] );
	exit;
}

try {
	$stmt = $pdo->prepare(
		'SELECT b.booking_id, b.invoice_no, b.status, b.payment_status, b.price, b.message, b.meta,
                b.service_id, b.plan_id, b.plan_name, b.plan_group, b.plan_group_label,
                b.subtotal, b.addons_total, b.currency,
                s.slug AS service_slug, s.name AS service_name,
                DATE_FORMAT(b.created_at, "%Y-%m-%d %H:%i") AS created_at,
                DATE_FORMAT(b.paid_at, "%Y-%m-%d %H:%i") AS paid_at
         FROM bookings b
         JOIN services s ON b.service_id = s.id
         WHERE b.booking_id = :bid AND b.customer_id = :cid
         LIMIT 1'
	);
	$stmt->execute( [ ':bid' => $bookingId, ':cid' => $_SESSION['user_id'] ] );
	$order = $stmt->fetch();

	if ( ! $order ) {
		echo json_encode( [ 'success' => false, 'message' => 'Order not found.' ] );
		exit;
	}

	$slug = $order['service_slug'];

	// Labelled order-time fields.
	$meta      = json_decode( (string) $order['meta'], true );
	$meta      = is_array( $meta ) ? $meta : array();
	$metaPairs = service_meta_pairs( $slug, $meta );

	// Package lines at the prices this order was charged. Every line on the
	// order: one booking can carry several, and the plan_id snapshot on the
	// booking only names the first of them.
	$itemsByBooking = booking_order_items( $pdo, array( $order['booking_id'] ) );
	$amounts        = booking_order_amounts( $order );

	// Files the customer uploaded for this order.
	$fileStmt = $pdo->prepare(
		'SELECT original_name, field_name, file_path, mime_type, file_size
         FROM uploaded_files
         WHERE booking_id = :bid
         ORDER BY uploaded_at'
	);
	$fileStmt->execute( [ ':bid' => $bookingId ] );

	// Admin-managed arrangement for this order.
	$recStmt = $pdo->prepare(
		'SELECT headline, sub_headline, progress, location,
                DATE_FORMAT(starts_on, "%Y-%m-%d") AS starts_on,
                DATE_FORMAT(ends_on, "%Y-%m-%d") AS ends_on,
                notes
         FROM service_records
         WHERE booking_id = :bid
         LIMIT 1'
	);
	$recStmt->execute( [ ':bid' => $bookingId ] );
	$record = $recStmt->fetch() ?: null;

	// Progress steps, and which one this order is currently on.
	$progressOptions = service_progress_options( $slug );
	$currentProgress = $record['progress'] ?? '';
	$progressIndex   = $currentProgress !== '' ? array_search( $currentProgress, $progressOptions, true ) : false;
	$progressIndex   = $progressIndex === false ? -1 : (int) $progressIndex;

	echo json_encode( [
		'success' => true,
		'order'   => array(
			'id'            => $order['booking_id'],
			'invoice'       => $order['invoice_no'],
			'service'       => $order['service_name'],
			'serviceSlug'   => $slug,
			'status'        => $order['status'],
			'statusLabel'   => service_status_label( $order['status'] ),
			'unlocked'      => service_status_unlocks( $order['status'] ),
			'paymentStatus' => $order['payment_status'],
			'paymentLabel'  => booking_payment_status_label( $order['payment_status'] ),
			'amount'        => (float) $order['price'],
			'date'          => $order['created_at'],
			'paidAt'        => $order['paid_at'],
			'message'       => (string) $order['message'],
			'metaFields'    => $metaPairs,
			// Snapshot of what was bought and charged. The customer is shown
			// their own package lines but none of the admin-side payment
			// internals (no provider ids, signature or failure text).
			'plan'          => booking_order_plan( $order ),
			'items'         => $itemsByBooking[ $order['booking_id'] ] ?? array(),
			'amounts'       => $amounts,
			'currency'      => $amounts['currency'],
			'payment'       => booking_order_payment_customer( $order ),
			'files'         => $fileStmt->fetchAll(),
			'arrangement'   => $record,
			'arrangementFields' => service_arrangement_fields( $slug ),
			'progress'      => array_values( array_map( function ( $step, $i ) use ( $progressIndex ) {
				return array(
					'label' => $step,
					'state' => $i < $progressIndex ? 'done' : ( $i === $progressIndex ? 'current' : '' ),
				);
			}, $progressOptions, array_keys( $progressOptions ) ) ),
		),
	] );
} catch ( Throwable $e ) {
	app_log( 'customer-order-detail', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'The order could not be loaded. Please try again.' ] );
}
