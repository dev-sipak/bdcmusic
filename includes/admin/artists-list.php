<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/pagination.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'admin' ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
    exit;
}

$page    = max( 1, (int) ( $_GET['page'] ?? 1 ) );
$perPage = max( 1, min( 100, (int) ( $_GET['per_page'] ?? 10 ) ) );
$search  = isset( $_GET['search'] ) ? sanitize_input( $_GET['search'] ) : '';

try {
    $pdo = db_connect();

    $where  = [];
    $params = [];

    if ( $search !== '' ) {
        $where[] = '(a.name LIKE :search OR a.location LIKE :search OR ac.name LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    $whereSql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

    $baseSql = 'SELECT a.id, a.name, a.slug, a.image, a.location, a.bio, a.is_active, a.category_id,
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
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
