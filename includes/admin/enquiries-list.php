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

try {
	$where  = [];
	$params = [];

	if ( $status !== 'all' ) {
		$where[]  = 'ae.status = :status';
		$params[':status'] = $status;
	}

	$whereSql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

	$baseSql = 'SELECT ae.id, ae.artist_id, ae.name, ae.email, ae.phone, ae.message, ae.status,
                       DATE_FORMAT(ae.created_at, "%Y-%m-%d %H:%i") AS created_at
                FROM artist_enquiries ae
                ' . $whereSql . '
                ORDER BY ae.created_at DESC';

	$pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

	echo json_encode( [
		'success'    => true,
		'enquiries'  => $pagination['items'],
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
	app_log( 'enquiries-list', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'The enquiries could not be loaded. Please try again.' ] );
}
