<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/pagination.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'customer' );

$page    = max( 1, (int) ( $_GET['page'] ?? 1 ) );
$perPage = max( 1, min( 100, (int) ( $_GET['per_page'] ?? 10 ) ) );
$status  = isset( $_GET['status'] ) ? clean_text( $_GET['status'] ) : 'all';
$service = isset( $_GET['service'] ) ? clean_text( $_GET['service'] ) : 'all';

try {
	$where  = [ 'b.customer_id = :cid' ];
	$params = [ ':cid' => $_SESSION['user_id'] ];

	if ( $status !== 'all' ) {
		$where[]  = 'b.status = :status';
		$params[':status'] = $status;
	}

	if ( $service !== 'all' ) {
		$where[]  = 's.name = :service';
		$params[':service'] = $service;
	}

	$whereSql = 'WHERE ' . implode( ' AND ', $where );

	$baseSql = 'SELECT b.booking_id AS id, s.name AS service, s.name AS item, b.status,
                       b.plan_id, b.plan_name, b.plan_group, b.plan_group_label,
                       b.subtotal, b.addons_total, b.price AS amount, b.currency,
                       b.payment_status,
                       DATE_FORMAT(b.created_at, "%Y-%m-%d %H:%i") AS date
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                ' . $whereSql . '
                ORDER BY b.created_at DESC';

	$pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

	// Package lines for this page of orders: bookings.plan_id only names the
	// first package, so a multi-package order needs its own rows to list them.
	$itemsByBooking = booking_order_items( $pdo, array_column( $pagination['items'], 'id' ) );

	// Snapshot columns only: no provider ids, signatures or failure reasons are
	// selected here, so there is nothing for a customer's browser to see.
	$orders = array();
	foreach ( $pagination['items'] as $row ) {
		$amounts = booking_order_amounts( $row );

		$orders[] = [
			'id'            => $row['id'],
			'service'       => $row['service'],
			'item'          => $row['item'],
			'status'        => $row['status'],
			'date'          => $row['date'],
			'plan'          => booking_order_plan( $row ),
			'items'         => $itemsByBooking[ $row['id'] ] ?? array(),
			'amounts'       => $amounts,
			'paymentStatus' => $row['payment_status'],
			'paymentLabel'  => booking_payment_status_label( $row['payment_status'] ),
			'amount'        => $amounts['total'],
		];
	}

	echo json_encode( [
		'success'    => true,
		'orders'     => $orders,
		'pagination' => [
			'currentPage'  => $pagination['currentPage'],
			'totalPages'   => $pagination['totalPages'],
			'totalRecords' => $pagination['totalRecords'],
			'perPage'      => $pagination['perPage'],
			'hasPrev'      => $pagination['hasPrev'],
			'hasNext'      => $pagination['hasNext'],
		],
	] );
} catch ( Throwable $e ) {
	app_log( 'customer-orders-list', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'Your orders could not be loaded. Please try again.' ] );
}
