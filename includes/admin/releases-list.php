<?php
/**
 * Admin Releases List API
 *
 * GET page, per_page, status, search
 *
 * Returns every release across all customers with its artists, tracks and
 * platform links, so admin-releases.js can render both the list and the
 * detail editor. Unlike includes/customer/releases-list.php this is not scoped
 * to one customer.
 *
 * Admin-only.
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/pagination.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

$page     = max( 1, (int) ( $_GET['page'] ?? 1 ) );
$perPage  = max( 1, min( 100, (int) ( $_GET['per_page'] ?? 10 ) ) );
$status   = isset( $_GET['status'] ) ? clean_text( $_GET['status'] ) : 'all';
$search   = isset( $_GET['search'] ) ? clean_text( $_GET['search'] ) : '';

$validStatuses = array( 'draft', 'pending', 'verification', 'onhold', 'rejected', 'approved', 'live', 'takedown' );

try {
    $where  = [];
    $params = [];

    if ( $status !== 'all' ) {
        if ( ! in_array( $status, $validStatuses, true ) ) {
            $status = 'all';
        } else {
            $where[]  = 'r.status = :status';
            $params[':status'] = $status;
        }
    }

    // One placeholder per column. db_connect() turns EMULATE_PREPARES off, so
    // MySQL rejects a named placeholder that appears more than once in a
    // statement; a single shared :search would make every search a 500.
    if ( $search !== '' ) {
        $where[] = '(r.title LIKE :searchTitle OR r.isrc LIKE :searchIsrc OR r.upc LIKE :searchUpc OR u.name LIKE :searchCustomer OR r.booking_id LIKE :searchBooking)';
        $params[':searchTitle']    = '%' . $search . '%';
        $params[':searchIsrc']     = '%' . $search . '%';
        $params[':searchUpc']      = '%' . $search . '%';
        $params[':searchCustomer'] = '%' . $search . '%';
        $params[':searchBooking']  = '%' . $search . '%';
    }

    $whereSql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

    $baseSql = 'SELECT r.id, r.booking_id, r.title, r.type, r.isrc, r.upc, r.status,
                       r.go_live_date, r.dolby, r.apple_itunes,
                       u.name AS customer, u.email AS email,
                       (SELECT COUNT(*) FROM release_tracks t WHERE t.release_id = r.id) AS track_count
                FROM releases r
                LEFT JOIN users u ON r.customer_id = u.id
                ' . $whereSql . '
                ORDER BY r.created_at DESC';

    $pagination = paginate( $pdo, $baseSql, $params, $page, $perPage );

    $releaseIds = array_column( $pagination['items'], 'id' );
    $artistsMap = [];
    $tracksMap  = [];
    $linksMap   = [];

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

        // Inactive links are included so the admin can see and re-enable them.
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

        $lstmt = $pdo->prepare(
            'SELECT release_id, id, platform, url, is_active, sort_order
             FROM release_platform_links
             WHERE release_id IN (' . $placeholders . ')
             ORDER BY release_id, sort_order, id'
        );
        $lstmt->execute( $releaseIds );
        foreach ( $lstmt->fetchAll() as $l ) {
            $linksMap[ $l['release_id'] ][] = [
                'id'         => $l['id'],
                'platform'   => $l['platform'],
                'url'        => $l['url'],
                'is_active'  => (int) $l['is_active'],
                'sort_order' => (int) $l['sort_order'],
            ];
        }
    }

    foreach ( $pagination['items'] as &$r ) {
        $r['artists'] = $artistsMap[ $r['id'] ] ?? [];
        $r['tracks']  = $tracksMap[ $r['id'] ] ?? [];
        $r['links']   = $linksMap[ $r['id'] ] ?? [];
    }
    unset( $r );

    echo json_encode( [
        'success'   => true,
        'releases'  => $pagination['items'],
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
      app_log( 'releases-list', 'load failed', $e );
      http_response_code( 500 );
      echo json_encode( [ 'success' => false, 'message' => 'The releases could not be loaded. Please try again.' ] );
  }
