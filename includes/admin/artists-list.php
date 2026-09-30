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
$search  = isset( $_GET['search'] ) ? clean_text( $_GET['search'] ) : '';

try {
	$where  = [];
	$params = [];

	// One placeholder per column. db_connect() turns EMULATE_PREPARES off, so
	// MySQL rejects a named placeholder that appears more than once in a
	// statement; a single shared :search would make every search a 500.
	if ( $search !== '' ) {
		$where[] = '(a.name LIKE :searchName OR a.location LIKE :searchLocation OR ac.name LIKE :searchCategory)';
		$params[':searchName']     = '%' . $search . '%';
		$params[':searchLocation'] = '%' . $search . '%';
		$params[':searchCategory'] = '%' . $search . '%';
	}

	$whereSql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

	$baseSql = 'SELECT a.id, a.name, a.slug, a.image, a.location, a.experience_years, a.bio, a.is_active, a.category_id,
                       ac.name AS category, ac.slug AS category_slug,
                       DATE_FORMAT(a.created_at, "%Y-%m-%d") AS created_at
                FROM artists a
                JOIN artist_categories ac ON a.category_id = ac.id
                ' . $whereSql . '
                ORDER BY ac.sort_order, a.name';

	$pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

	// Fetch pricing for the current page's artists
	$artistIds = array_column( $pagination['items'], 'id' );
	$pricingMap = [];
	if ( ! empty( $artistIds ) ) {
		$placeholders = implode( ',', array_fill( 0, count( $artistIds ), '?' ) );
		$pstmt = $pdo->prepare(
			'SELECT artist_id, service_type, price, sort_order
             FROM artist_pricing
             WHERE artist_id IN (' . $placeholders . ')
             ORDER BY sort_order'
		);
		$pstmt->execute( $artistIds );
		$pricing = $pstmt->fetchAll();
		foreach ( $pricing as $p ) {
			$pricingMap[ $p['artist_id'] ][] = $p;
		}
	}

	foreach ( $pagination['items'] as &$a ) {
		$a['pricing'] = $pricingMap[ $a['id'] ] ?? [];
	}

	echo json_encode( [
		'success'    => true,
		'artists'    => $pagination['items'],
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
	app_log( 'artists-list', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'The artists could not be loaded. Please try again.' ] );
}
