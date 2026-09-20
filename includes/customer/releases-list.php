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

try {
    $pdo = db_connect();

    $where  = [ 'r.customer_id = :cid' ];
    $params = [ ':cid' => $_SESSION['user_id'] ];

    if ( $status !== 'all' ) {
        $where[]  = 'r.status = :status';
        $params[':status'] = $status;
    }

    $whereSql = 'WHERE ' . implode( ' AND ', $where );

    $baseSql = 'SELECT r.id, r.title, r.type, r.artwork_path, r.isrc, r.upc,
                       DATE_FORMAT(r.go_live_date, "%Y-%m-%d") AS go_live_date,
                       r.status, r.dolby, r.apple_itunes,
                       DATE_FORMAT(r.created_at, "%Y-%m-%d") AS created_at
                FROM releases r
                ' . $whereSql . '
                ORDER BY r.created_at DESC';

    $pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

    // Fetch artists and history for current page releases (batch, no N+1)
    $releaseIds = array_column( $pagination['items'], 'id' );
    $artistsMap = [];
    $historyMap = [];

    if ( ! empty( $releaseIds ) ) {
        $placeholders = implode( ',', array_fill( 0, count( $releaseIds ), '?' ) );

        $astmt = $pdo->prepare(
            'SELECT release_id, role, name
             FROM release_artists
             WHERE release_id IN (' . $placeholders . ')'
        );
        $astmt->execute( $releaseIds );
        foreach ( $astmt->fetchAll() as $a ) {
            $artistsMap[ $a['release_id'] ][] = [ 'role' => $a['role'], 'name' => $a['name'] ];
        }

        $hstmt = $pdo->prepare(
            'SELECT release_id, action, message,
                    DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") AS created_at
             FROM release_history
             WHERE release_id IN (' . $placeholders . ')
             ORDER BY created_at DESC'
        );
        $hstmt->execute( $releaseIds );
        foreach ( $hstmt->fetchAll() as $h ) {
            $historyMap[ $h['release_id'] ][] = $h;
        }
    }

    foreach ( $pagination['items'] as &$r ) {
        $r['artists'] = $artistsMap[ $r['id'] ] ?? [];
        $r['history'] = $historyMap[ $r['id'] ] ?? [];
    }

    echo json_encode( [
        'success'    => true,
        'releases'   => $pagination['items'],
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
