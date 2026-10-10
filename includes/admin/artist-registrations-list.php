<?php
/**
 * Artist registrations list for the admin verification queue.
 *
 * GET ?status=all|pending|verified|rejected&search=<text>&page=<n>&per_page=<n>
 *
 * One row per BDC Marketplace package purchase, newest first, paginated in SQL.
 * The queue is the studio's review list, so a pending registration is what an
 * admin sees by default.
 *
 * Admin-only.
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/pagination.php';
require_once __DIR__ . '/../../includes/artist-registrations.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

$status = isset( $_GET['status'] ) ? clean_text( $_GET['status'] ) : 'pending';
$search = isset( $_GET['search'] ) ? clean_text( $_GET['search'] ) : '';
$page   = max( 1, (int) ( $_GET['page'] ?? 1 ) );
$perPage = max( 1, min( 100, (int) ( $_GET['per_page'] ?? 10 ) ) );

// An unknown value in the query string is treated as "no filter" rather than
// passed through to SQL, so the enum can never be the thing that is filtered on.
if ( ! in_array( $status, array( 'all', 'pending', 'verified', 'rejected' ), true ) ) {
	$status = 'all';
}

try {
	$where  = array();
	$params = array();

	if ( $status !== 'all' ) {
		$where[] = 'r.status = :status';
		$params[':status'] = $status;
	}

	// One placeholder per column: db_connect() turns EMULATE_PREPARES off, so a
	// named placeholder may not appear twice in one statement.
	if ( $search !== '' ) {
		$where[] = '(r.artist_name LIKE :searchName OR r.artist_email LIKE :searchEmail OR r.booking_id LIKE :searchBooking OR r.artist_code LIKE :searchCode)';
		$params[':searchName']    = '%' . $search . '%';
		$params[':searchEmail']   = '%' . $search . '%';
		$params[':searchBooking'] = '%' . $search . '%';
		$params[':searchCode']    = '%' . $search . '%';
	}

	$whereSql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

	// Amounts and the plan come from the registration's own snapshot, so the row
	// reports what was paid for even if the catalogue changes later.
	$baseSql = 'SELECT r.id, r.booking_id, r.customer_id, r.artist_name, r.artist_email,
					   r.artist_phone, r.artist_code, r.category_slug, r.city, r.plan_name,
					   r.plan_price, r.currency, r.status, r.reviewed_at,
					   DATE_FORMAT(r.created_at, "%Y-%m-%d %H:%i") AS created_at,
					   b.status AS order_status, b.payment_status
				FROM artist_registrations r
				LEFT JOIN bookings b ON b.booking_id = r.booking_id
				' . $whereSql . '
				ORDER BY r.created_at DESC, r.id DESC';

	$pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

	$registrations = array();

	foreach ( $pagination['items'] as $row ) {
		$row['category_label'] = artist_registration_category_label( $row['category_slug'] );
		$row['status_label']   = artist_registration_status_label( $row['status'] );
		$registrations[]       = $row;
	}

	// The tab counters, so the filter bar can show how many are waiting without
	// a second request per tab.
	$counts = array(
		'all'       => (int) $pdo->query( 'SELECT COUNT(*) FROM artist_registrations' )->fetchColumn(),
		'pending'   => (int) $pdo->query( "SELECT COUNT(*) FROM artist_registrations WHERE status = 'pending'" )->fetchColumn(),
		'verified'  => (int) $pdo->query( "SELECT COUNT(*) FROM artist_registrations WHERE status = 'verified'" )->fetchColumn(),
		'rejected'  => (int) $pdo->query( "SELECT COUNT(*) FROM artist_registrations WHERE status = 'rejected'" )->fetchColumn(),
	);

	echo json_encode( array(
		'success'      => true,
		'registrations' => $registrations,
		'counts'       => $counts,
		'pagination'   => array(
			'currentPage'  => $pagination['currentPage'],
			'totalPages'   => $pagination['totalPages'],
			'totalRecords' => $pagination['totalRecords'],
			'perPage'      => $pagination['perPage'],
			'hasPrev'      => $pagination['hasPrev'],
			'hasNext'      => $pagination['hasNext'],
		),
	) );
} catch ( Throwable $e ) {
	app_log( 'artist-registrations-list', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( array( 'success' => false, 'message' => 'The registrations could not be loaded. Please try again.' ) );
}
