<?php
/**
 * One artist registration, in full, for the admin review panel.
 *
 * GET ?id=<registration id>
 *
 * Everything needed to decide on a registration: the order-time submission as
 * labelled label/value pairs (including any field this feature does not
 * denormalise), the order it came from with its payment state, the artist's
 * uploads, and the review history. A verified registration also reports the
 * documents issued for it.
 *
 * Admin-only.
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/artist-registrations.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

if ( $id <= 0 ) {
	http_response_code( 400 );
	echo json_encode( array( 'success' => false, 'message' => 'Missing registration id.' ) );
	exit;
}

try {
	$stmt = $pdo->prepare(
		'SELECT r.*,
				DATE_FORMAT(r.created_at, "%Y-%m-%d %H:%i") AS created_at_display,
				DATE_FORMAT(r.reviewed_at, "%Y-%m-%d %H:%i") AS reviewed_at_display,
				b.status AS order_status, b.payment_status, b.invoice_no,
				b.customer_name, b.customer_email, b.customer_phone,
				b.customer_type, b.service_name
		 FROM artist_registrations r
		 LEFT JOIN bookings b ON b.booking_id = r.booking_id
		 WHERE r.id = :id
		 LIMIT 1'
	);
	$stmt->execute( array( ':id' => $id ) );
	$row = $stmt->fetch();

	if ( ! $row ) {
		http_response_code( 404 );
		echo json_encode( array( 'success' => false, 'message' => 'That registration could not be found.' ) );
		exit;
	}

	// What the artist typed, labelled through the service definition so the
	// review reads the same way the order modal reads the same order.
	$pairs = artist_registration_detail_pairs( $row );

	// Anything the artist uploaded with the registration, such as a portfolio
	// sample. Read through uploaded_files, the same table the order modal uses.
	$filesStmt = $pdo->prepare(
		'SELECT original_name, file_path, mime_type, file_size
		 FROM uploaded_files
		 WHERE booking_id = :booking_id
		 ORDER BY uploaded_at'
	);
	$filesStmt->execute( array( ':booking_id' => $row['booking_id'] ) );

	$reviewerName = '';

	if ( ! empty( $row['reviewed_by'] ) ) {
		$reviewerStmt = $pdo->prepare( 'SELECT name FROM users WHERE id = :id LIMIT 1' );
		$reviewerStmt->execute( array( ':id' => $row['reviewed_by'] ) );
		$reviewerName = (string) $reviewerStmt->fetchColumn();
	}

	echo json_encode( array(
		'success'       => true,
		'registration'  => array(
			'id'                => (int) $row['id'],
			'bookingId'         => $row['booking_id'],
			'customerId'        => $row['customer_id'],
			'customerName'      => $row['customer_name'] ?: $row['artist_name'],
			'customerEmail'     => $row['customer_email'] ?: $row['artist_email'],
			'customerPhone'     => $row['customer_phone'] ?: $row['artist_phone'],
			'customerType'      => $row['customer_type'],
			'artistName'        => $row['artist_name'],
			'artistEmail'       => $row['artist_email'],
			'artistPhone'       => $row['artist_phone'],
			'artistCode'        => $row['artist_code'],
			'category'          => artist_registration_category_label( $row['category_slug'] ),
			'experience'        => $row['experience'],
			'city'              => $row['city'],
			'planName'          => $row['plan_name'],
			'planPrice'         => (float) $row['plan_price'],
			'currency'          => $row['currency'],
			'status'            => $row['status'],
			'statusLabel'       => artist_registration_status_label( $row['status'] ),
			'reviewNote'        => (string) ( $row['review_note'] ?? '' ),
			'reviewedBy'        => $row['reviewed_by'],
			'reviewerName'      => $reviewerName,
			'reviewedAt'        => $row['reviewed_at_display'],
			'createdAt'         => $row['created_at_display'],
			'orderStatus'       => $row['order_status'],
			'paymentStatus'     => $row['payment_status'],
			'invoiceNo'         => $row['invoice_no'],
			'serviceName'       => $row['service_name'],
			'portfolioUrl'      => (string) ( $row['portfolio_url'] ?? '' ),
			'about'             => (string) ( $row['about'] ?? '' ),
			'goal'              => (string) ( $row['goal'] ?? '' ),
			'detailFields'      => $pairs,
			'files'             => $filesStmt->fetchAll(),
		),
	) );
} catch ( Throwable $e ) {
	app_log( 'artist-registration-detail', 'load failed', $e );
	http_response_code( 500 );
	echo json_encode( array( 'success' => false, 'message' => 'The registration could not be loaded. Please try again.' ) );
}
