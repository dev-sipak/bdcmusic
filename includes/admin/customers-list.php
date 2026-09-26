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

    // One placeholder per column. db_connect() turns EMULATE_PREPARES off, so
    // MySQL rejects a named placeholder that appears more than once in a
    // statement; a single shared :search would make every search a 500.
    if ( $search !== '' ) {
        $where[] = '(u.name LIKE :searchName OR u.email LIKE :searchEmail OR u.mobile LIKE :searchMobile)';
        $params[':searchName']   = '%' . $search . '%';
        $params[':searchEmail']  = '%' . $search . '%';
        $params[':searchMobile'] = '%' . $search . '%';
    }

    $whereSql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

    $baseSql = 'SELECT u.id, u.name, u.email, u.mobile AS phone,
                       COUNT(b.booking_id) AS total_orders,
                       COALESCE(SUM(b.price), 0) AS total_spent,
                       DATE_FORMAT(MAX(b.created_at), "%Y-%m-%d") AS last_order
                FROM users u
                LEFT JOIN bookings b ON b.customer_id = u.id
                ' . $whereSql . '
                GROUP BY u.id, u.name, u.email, u.mobile
                ORDER BY MAX(b.created_at) DESC';

    $pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

    // Format total_spent
    foreach ( $pagination['items'] as &$c ) {
        $c['total_spent'] = number_format( (float) $c['total_spent'], 0, '.', ',' );
    }

    echo json_encode( [
        'success'    => true,
        'customers'  => $pagination['items'],
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
