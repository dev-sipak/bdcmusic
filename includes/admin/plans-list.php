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
$perPage = max( 1, min( 100, (int) ( $_GET['per_page'] ?? 15 ) ) );
$search  = isset( $_GET['search'] ) ? clean_text( $_GET['search'] ) : '';
$service = (int) ( $_GET['service_id'] ?? 0 );
$group   = isset( $_GET['group_key'] ) ? clean_text( $_GET['group_key'] ) : '';

try {
	$where  = [];
	$params = [];

	if ( $service > 0 ) {
		$where[]            = 'p.service_id = :serviceId';
		$params[':serviceId'] = $service;
	}

	if ( $group !== '' ) {
		$where[]            = 'p.group_key = :groupKey';
		$params[':groupKey'] = $group;
	}

	// One placeholder per column. db_connect() turns EMULATE_PREPARES off, so
	// MySQL rejects a named placeholder that appears more than once in a
	// statement; a single shared :search would make every search a 500.
	if ( $search !== '' ) {
		$where[] = '(p.name LIKE :searchName
                     OR p.group_label LIKE :searchLabel
                     OR p.group_key LIKE :searchKey
                     OR s.name LIKE :searchService)';
		$params[':searchName']    = '%' . $search . '%';
		$params[':searchLabel']   = '%' . $search . '%';
		$params[':searchKey']     = '%' . $search . '%';
		$params[':searchService'] = '%' . $search . '%';
	}

	$whereSql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

	$baseSql = 'SELECT p.id, p.service_id, p.group_key, p.group_label, p.name, p.price,
                       p.price_note, p.description, p.best_for, p.features,
                       p.is_default, p.is_active, p.is_enquiry, p.is_orderable, p.sort_order,
                       s.name AS service, s.slug AS service_slug
                FROM service_plans p
                JOIN services s ON s.id = p.service_id
                ' . $whereSql . '
                ORDER BY s.name, p.sort_order, p.id';

	$pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

	foreach ( $pagination['items'] as &$plan ) {
		$features = json_decode( (string) ( $plan['features'] ?? '' ), true );
		$plan['features'] = is_array( $features )
			? array_values( array_filter( array_map( 'strval', $features ), 'strlen' ) )
			: [];
		$plan['group_label'] = (string) ( $plan['group_label'] ?? '' );
		$plan['price_note']  = (string) ( $plan['price_note'] ?? '' );
		$plan['description'] = (string) ( $plan['description'] ?? '' );
		$plan['best_for']    = (string) ( $plan['best_for'] ?? '' );
	}
	unset( $plan );

	echo json_encode( [
		'success'    => true,
		'plans'      => $pagination['items'],
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
	app_log( 'plans-list', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'The packages could not be loaded. Please try again.' ] );
}
