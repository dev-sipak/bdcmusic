<?php
/**
 * Customer service section API.
 *
 * GET ?service=<services.slug>
 *
 * Returns everything one customer is allowed to see for a single service:
 *   - whether the section is unlocked (i.e. a booking reached processing or
 *     delivered)
 *   - the per-service labels used to render the admin-managed arrangement
 *   - that service's orders, each with:
 *       * the fields the customer filled in at order time, decoded from
 *         bookings.meta and mapped to human labels
 *       * the files the customer uploaded against that order
 *       * the admin-managed arrangement row from service_records
 *
 * A service the customer never booked is rejected, so this endpoint cannot be
 * used to discover other customers' services.
 */
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/service-fields.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'customer' ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
    exit;
}

$slug   = isset( $_GET['service'] ) ? sanitize_input( $_GET['service'] ) : '';
$def    = service_definition( $slug );

if ( $slug === '' || $def === null ) {
    echo json_encode( [ 'success' => false, 'message' => 'Unknown service.' ] );
    exit;
}

try {
    $pdo = db_connect();

    // Resolve the service, then confirm this customer actually bought it.
    $svcStmt = $pdo->prepare( 'SELECT id, slug, name FROM services WHERE slug = :slug AND is_active = 1 LIMIT 1' );
    $svcStmt->execute( [ ':slug' => $slug ] );
    $service = $svcStmt->fetch();

    if ( ! $service ) {
        echo json_encode( [ 'success' => false, 'message' => 'Unknown service.' ] );
        exit;
    }

    $orderStmt = $pdo->prepare(
        'SELECT b.booking_id, b.status, b.payment_status, b.price, b.message, b.meta,
                DATE_FORMAT(b.created_at, "%Y-%m-%d") AS created_at,
                DATE_FORMAT(b.paid_at, "%Y-%m-%d") AS paid_at
         FROM bookings b
         WHERE b.customer_id = :cid AND b.service_id = :sid
         ORDER BY b.created_at DESC'
    );
    $orderStmt->execute( [ ':cid' => $_SESSION['user_id'], ':sid' => $service['id'] ] );
    $orders = $orderStmt->fetchAll();

    if ( empty( $orders ) ) {
        echo json_encode( [ 'success' => false, 'message' => 'You have not purchased this service.' ] );
        exit;
    }

    $bookingIds = array_column( $orders, 'booking_id' );
    $unlocked   = false;

    // Admin-managed arrangement rows, keyed by booking.
    $recordMap = array();
    $placeholders = implode( ',', array_fill( 0, count( $bookingIds ), '?' ) );
    $recStmt = $pdo->prepare(
        'SELECT booking_id, headline, sub_headline, progress, location,
                DATE_FORMAT(starts_on, "%Y-%m-%d") AS starts_on,
                DATE_FORMAT(ends_on, "%Y-%m-%d") AS ends_on,
                notes
         FROM service_records
         WHERE booking_id IN (' . $placeholders . ')'
    );
    $recStmt->execute( $bookingIds );
    foreach ( $recStmt->fetchAll() as $rec ) {
        $recordMap[ $rec['booking_id'] ] = $rec;
    }

    // Files the customer uploaded, grouped by booking.
    $fileMap = array();
    $fileStmt = $pdo->prepare(
        'SELECT booking_id, original_name, file_path, mime_type, file_size
         FROM uploaded_files
         WHERE booking_id IN (' . $placeholders . ')
         ORDER BY uploaded_at'
    );
    $fileStmt->execute( $bookingIds );
    foreach ( $fileStmt->fetchAll() as $file ) {
        $fileMap[ $file['booking_id'] ][] = $file;
    }

    foreach ( $orders as &$order ) {
        if ( service_status_unlocks( $order['status'] ) ) {
            $unlocked = true;
        }

        $meta = json_decode( (string) $order['meta'], true );

        $order['metaFields']   = service_meta_pairs( $slug, $meta );
        $order['message']      = (string) $order['message'];
        $order['statusLabel']  = service_status_label( $order['status'] );
        $order['amount']       = (float) $order['price'];
        $order['files']        = $fileMap[ $order['booking_id'] ] ?? array();
        $order['arrangement']  = $recordMap[ $order['booking_id'] ] ?? null;
    }
    unset( $order );

    echo json_encode( [
        'success' => true,
        'service' => array(
            'slug'        => $service['slug'],
            'name'        => $service['name'],
            'nav'         => $def['nav'] ?? $service['name'],
            'icon'        => $def['icon'] ?? 'fa-circle',
            'blurb'       => service_blurb( $slug ),
            'unlocked'    => $unlocked,
            'arrangement' => service_arrangement_fields( $slug ),
            'progress'    => service_progress_options( $slug ),
        ),
        'orders'  => $orders,
    ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
