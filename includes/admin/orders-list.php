<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/pagination.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

$page    = max( 1, (int) ( $_GET['page'] ?? 1 ) );
$perPage = max( 1, min( 100, (int) ( $_GET['per_page'] ?? 10 ) ) );
$status  = isset( $_GET['status'] ) ? clean_text( $_GET['status'] ) : 'all';
$service = isset( $_GET['service'] ) ? clean_text( $_GET['service'] ) : 'all';
$payment = isset( $_GET['payment'] ) ? clean_text( $_GET['payment'] ) : 'all';
$search  = isset( $_GET['search'] ) ? clean_text( $_GET['search'] ) : '';

try {
	$where  = [];
	$params = [];

	if ( $status !== 'all' ) {
		$where[]  = 'b.status = :status';
		$params[':status'] = $status;
	}

	if ( $service !== 'all' ) {
		$where[]  = 's.name = :service';
		$params[':service'] = $service;
	}

	if ( $payment !== 'all' ) {
		$where[]  = 'b.payment_status = :payment';
		$params[':payment'] = $payment;
	}

	// One placeholder per column. db_connect() turns EMULATE_PREPARES off, so
	// MySQL rejects a named placeholder that appears more than once in a
	// statement; a single shared :search would make every search a 500.
	if ( $search !== '' ) {
		$where[] = '(b.booking_id LIKE :searchId OR u.name LIKE :searchName OR u.email LIKE :searchEmail OR u.mobile LIKE :searchMobile)';
		$params[':searchId']     = '%' . $search . '%';
		$params[':searchName']   = '%' . $search . '%';
		$params[':searchEmail']  = '%' . $search . '%';
		$params[':searchMobile'] = '%' . $search . '%';
	}

	$whereSql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

	// Plan, amount and contact columns are read from the snapshot on the
	// booking, not re-joined from the live catalogue, so this list always
	// reports what the customer was actually charged.
	$baseSql = 'SELECT b.booking_id AS id, u.name AS customer, u.email AS email, u.mobile AS phone,
                       COALESCE(NULLIF(b.customer_name, ""), u.name) AS customer_name,
                       s.name AS service, s.name AS item, b.status,
                       b.customer_type, b.plan_id, b.plan_name, b.plan_group, b.plan_group_label,
                       b.subtotal, b.addons_total, b.price AS amount, b.currency,
                       b.payment_status, b.payment_method,
                       DATE_FORMAT(b.created_at, "%Y-%m-%d %H:%i") AS date
                FROM bookings b
                LEFT JOIN users u ON b.customer_id = u.id
                JOIN services s ON b.service_id = s.id
                ' . $whereSql . '
                ORDER BY b.created_at DESC';

	$pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

	// Package lines for just this page of orders, so the list can show them
	// without a correlated subquery per row.
	$itemsByBooking = booking_order_items( $pdo, array_column( $pagination['items'], 'id' ) );

	$orders = [];
	foreach ( $pagination['items'] as $row ) {
		$items   = $itemsByBooking[ $row['id'] ] ?? [];
		$amounts = booking_order_amounts( $row );

		$orders[] = [
			'id'             => $row['id'],
			// The name snapshot is taken at booking time, so it is always set.
			// No "Guest" placeholder: an unlabeled guest row read as a second kind
			// of customer, which it is not.
			'customer'       => $row['customer_name'],
			'email'          => $row['email'],
			'phone'          => $row['phone'] ?: '',
			'customerType'   => $row['customer_type'],
			'service'        => $row['service'],
			'item'           => $row['item'],
			'date'           => $row['date'],
			'status'         => $row['status'],
			'paymentStatus'  => $row['payment_status'],
			'paymentLabel'   => booking_payment_status_label( $row['payment_status'] ),
			'paymentMethod'  => (string) ( $row['payment_method'] ?? '' ),
			'plan'           => booking_order_plan( $row ),
			'items'          => $items,
			'itemsCount'     => count( $items ),
			'amounts'        => $amounts,
			// Kept at the top level so existing consumers of `amount` and
			// `payment_status` keep working unchanged.
			'amount'         => $amounts['total'],
			'subtotal'       => $amounts['subtotal'],
			'currency'       => $amounts['currency'],
			'payment_status' => $row['payment_status'],
		];
	}

	echo json_encode( [
		'success'   => true,
		'orders'    => $orders,
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
	app_log( 'orders-list', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'The orders could not be loaded. Please try again.' ] );
}
