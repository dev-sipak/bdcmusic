<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/pagination.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'customer' ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
    exit;
}

$page    = max( 1, (int) ( $_GET['page'] ?? 1 ) );
$perPage = max( 1, min( 100, (int) ( $_GET['per_page'] ?? 10 ) ) );
$status  = isset( $_GET['status'] ) ? sanitize_input( $_GET['status'] ) : 'all';
$service = isset( $_GET['service'] ) ? sanitize_input( $_GET['service'] ) : 'all';

try {
    $pdo = db_connect();

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
                       DATE_FORMAT(b.created_at, "%Y-%m-%d") AS date,
                       b.price AS amount
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                ' . $whereSql . '
                ORDER BY b.created_at DESC';

    $pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

    echo json_encode( [
        'success'    => true,
        'orders'     => $pagination['items'],
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
