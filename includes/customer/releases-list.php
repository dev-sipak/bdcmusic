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

try {
    // Releases belong to Digital Music Distribution, so the same entitlement the
    // page gate in dashboard/releases.php applies has to be enforced here too.
    // Without it, any signed-in customer could read releases by calling this
    // endpoint directly. The true asks for an *unlocked* booking
    // (SERVICE_UNLOCK_STATUSES: processing or delivered); without it a customer
    // whose only distribution booking is still pending or cancelled would be
    // refused the page but could still read their releases through this API.
    if ( ! customer_has_service_booking( $pdo, $_SESSION['user_id'], 'digital-distribution', true ) ) {
        http_response_code( 403 );
        echo json_encode( [ 'success' => false, 'message' => 'You have not purchased this service.' ] );
        exit;
    }

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

    // Fetch artists, tracks, platform links and history for current page
    // releases (batched, no N+1).
    $releaseIds = array_column( $pagination['items'], 'id' );
    $artistsMap = [];
    $tracksMap  = [];
    $linksMap   = [];
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

        // Tracks. An album or EP has many; a single has one. A release with no
        // track rows yet is still listed, just with an empty track list.
        $tstmt = $pdo->prepare(
            'SELECT release_id, id, track_no, title, isrc, duration
             FROM release_tracks
             WHERE release_id IN (' . $placeholders . ')
             ORDER BY release_id, track_no, id'
        );
        $tstmt->execute( $releaseIds );
        foreach ( $tstmt->fetchAll() as $t ) {
            $tracksMap[ $t['release_id'] ][] = [
                'id'       => $t['id'],
                'track_no' => (int) $t['track_no'],
                'title'    => $t['title'],
                'isrc'     => $t['isrc'],
                'duration' => $t['duration'],
            ];
        }

        // Admin-managed platform live links, active ones only.
        $lstmt = $pdo->prepare(
            'SELECT release_id, id, platform, url, sort_order
             FROM release_platform_links
             WHERE release_id IN (' . $placeholders . ')
               AND is_active = 1
             ORDER BY release_id, sort_order, id'
        );
        $lstmt->execute( $releaseIds );
        foreach ( $lstmt->fetchAll() as $l ) {
            $linksMap[ $l['release_id'] ][] = [
                'id'       => $l['id'],
                'platform' => $l['platform'],
                'url'      => $l['url'],
            ];
        }

        $hstmt = $pdo->prepare(
            'SELECT release_id, action, message, reviewer_note,
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
        $r['tracks']  = $tracksMap[ $r['id'] ] ?? [];
        $r['links']   = $linksMap[ $r['id'] ] ?? [];
        $r['history'] = $historyMap[ $r['id'] ] ?? [];
    }
    unset( $r );

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
} catch ( Throwable $e ) {
    app_log( 'customer-releases-list', 'request failed', $e );
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'Your releases could not be loaded. Please try again.' ] );
}
