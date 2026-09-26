<?php
/**
 * Single order detail API for the customer dashboard.
 *
 * GET ?id=<bookings.booking_id>
 *
 * Returns the complete record of one order belonging to the signed-in
 * customer: every field they filled in at order time (decoded from
 * bookings.meta and mapped to human labels), the files they uploaded, and the
 * arrangements the admin has recorded in service_records.
 *
 * Replaces the old standalone "Order Details" page, which is now the View More
 * dialog on My Orders. The customer_id filter is what keeps orders private.
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

$bookingId = isset( $_GET['id'] ) ? sanitize_input( $_GET['id'] ) : '';

if ( $bookingId === '' ) {
    echo json_encode( [ 'success' => false, 'message' => 'Order not found.' ] );
    exit;
}

try {
    $pdo = db_connect();

    $stmt = $pdo->prepare(
        'SELECT b.booking_id, b.invoice_no, b.status, b.payment_status, b.price, b.message, b.meta,
                b.service_id, s.slug AS service_slug, s.name AS service_name,
                DATE_FORMAT(b.created_at, "%Y-%m-%d") AS created_at,
                DATE_FORMAT(b.paid_at, "%Y-%m-%d") AS paid_at
         FROM bookings b
         JOIN services s ON b.service_id = s.id
         WHERE b.booking_id = :bid AND b.customer_id = :cid
         LIMIT 1'
    );
    $stmt->execute( [ ':bid' => $bookingId, ':cid' => $_SESSION['user_id'] ] );
    $order = $stmt->fetch();

    if ( ! $order ) {
        echo json_encode( [ 'success' => false, 'message' => 'Order not found.' ] );
        exit;
    }

    $slug = $order['service_slug'];

    // Labelled order-time fields.
    $meta      = json_decode( (string) $order['meta'], true );
    $meta      = is_array( $meta ) ? $meta : array();
    $metaPairs = service_meta_pairs( $slug, $meta );

    // Files the customer uploaded for this order.
    $fileStmt = $pdo->prepare(
        'SELECT original_name, field_name, file_path, mime_type, file_size
         FROM uploaded_files
         WHERE booking_id = :bid
         ORDER BY uploaded_at'
    );
    $fileStmt->execute( [ ':bid' => $bookingId ] );

    // Admin-managed arrangement for this order.
    $recStmt = $pdo->prepare(
        'SELECT headline, sub_headline, progress, location,
                DATE_FORMAT(starts_on, "%Y-%m-%d") AS starts_on,
                DATE_FORMAT(ends_on, "%Y-%m-%d") AS ends_on,
                notes
         FROM service_records
         WHERE booking_id = :bid
         LIMIT 1'
    );
    $recStmt->execute( [ ':bid' => $bookingId ] );
    $record = $recStmt->fetch() ?: null;

    // Progress steps, and which one this order is currently on.
    $progressOptions = service_progress_options( $slug );
    $currentProgress = $record['progress'] ?? '';
    $progressIndex   = $currentProgress !== '' ? array_search( $currentProgress, $progressOptions, true ) : false;
    $progressIndex   = $progressIndex === false ? -1 : (int) $progressIndex;

    echo json_encode( [
        'success' => true,
        'order'   => array(
            'id'            => $order['booking_id'],
            'invoice'       => $order['invoice_no'],
            'service'       => $order['service_name'],
            'serviceSlug'   => $slug,
            'status'        => $order['status'],
            'statusLabel'   => service_status_label( $order['status'] ),
            'unlocked'      => service_status_unlocks( $order['status'] ),
            'paymentStatus' => $order['payment_status'],
            'amount'        => (float) $order['price'],
            'date'          => $order['created_at'],
            'paidAt'        => $order['paid_at'],
            'message'       => (string) $order['message'],
            'metaFields'    => $metaPairs,
            'files'         => $fileStmt->fetchAll(),
            'arrangement'   => $record,
            'arrangementFields' => service_arrangement_fields( $slug ),
            'progress'      => array_values( array_map( function ( $step, $i ) use ( $progressIndex ) {
                return array(
                    'label' => $step,
                    'state' => $i < $progressIndex ? 'done' : ( $i === $progressIndex ? 'current' : '' ),
                );
            }, $progressOptions, array_keys( $progressOptions ) ) ),
        ),
    ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
