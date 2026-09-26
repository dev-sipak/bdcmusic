<?php
/**
 * Admin Order Detail API
 *
 * GET ?id=<booking_id>
 *
 * Returns everything needed to render the admin order modal: the customer,
 * the fields the customer submitted at order time, their uploads, and the
 * arrangement (service_records row) the admin has recorded for this booking.
 *
 * Admin-only. See includes/customer/order-detail.php for the customer-facing
 * counterpart, which returns the same shape for a single customer's own order.
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/service-fields.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'admin' ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
    exit;
}

$bookingId = isset( $_GET['id'] ) ? sanitize_input( $_GET['id'] ) : '';
if ( $bookingId === '' ) {
    http_response_code( 400 );
    echo json_encode( [ 'success' => false, 'message' => 'Missing order id.' ] );
    exit;
}

try {
    $pdo = db_connect();

    $stmt = $pdo->prepare(
        'SELECT b.booking_id AS id, b.invoice_no, b.customer_id, u.name AS customer,
                u.email AS email, u.mobile AS phone,
                s.id AS service_id, s.slug AS service_slug, s.name AS service,
                b.status, b.payment_status, b.price AS amount, b.message,
                DATE_FORMAT(b.created_at, "%Y-%m-%d") AS date,
                b.meta
         FROM bookings b
         LEFT JOIN users u ON b.customer_id = u.id
         JOIN services s ON b.service_id = s.id
         WHERE b.booking_id = :id
         LIMIT 1'
    );
    $stmt->execute( [ ':id' => $bookingId ] );
    $order = $stmt->fetch();
    if ( ! $order ) {
        http_response_code( 404 );
        echo json_encode( [ 'success' => false, 'message' => 'Order not found.' ] );
        exit;
    }

    $slug = $order['service_slug'];

    // Fields the customer submitted when placing the order, labelled by the
    // same helper the customer-facing endpoint uses so both agree.
    $metaPairs = service_meta_pairs( $slug, $order['meta'] ? json_decode( $order['meta'], true ) : array() );

    // Admin-managed arrangement, if one has been saved for this booking.
    $rStmt = $pdo->prepare(
        'SELECT headline, sub_headline, progress, starts_on, ends_on, location, notes
         FROM service_records
         WHERE booking_id = :id
         LIMIT 1'
    );
    $rStmt->execute( [ ':id' => $bookingId ] );
    $record = $rStmt->fetch();
    if ( $record ) {
        // Blank columns read as "not filled in yet" rather than a real value.
        $record = array_filter( $record, static function ( $v ) {
            return $v !== null && $v !== '';
        } );
    } else {
        $record = null;
    }

    $fStmt = $pdo->prepare(
        'SELECT original_name, file_path, mime_type, file_size
         FROM uploaded_files
         WHERE booking_id = :id
         ORDER BY uploaded_at'
    );
    $fStmt->execute( [ ':id' => $bookingId ] );

    echo json_encode( [
        'success' => true,
        'order'   => [
            'id'                 => $order['id'],
            'invoice_no'         => $order['invoice_no'],
            'customer'           => $order['customer'],
            'email'              => $order['email'],
            'phone'              => $order['phone'] ?: 'N/A',
            'service'            => $order['service'],
            'service_slug'       => $slug,
            'status'             => $order['status'],
            'payment_status'     => $order['payment_status'],
            'amount'             => $order['amount'],
            'message'            => $order['message'] ?: '',
            'date'               => $order['date'],
            // Keyed by the DB column names so the admin form can be rendered
            // straight from these, whichever labels the service uses.
            'metaFields'         => $metaPairs,
            'arrangement'        => $record,
            'arrangementFields'  => service_arrangement_fields( $slug ),
            'progressOptions'    => service_progress_options( $slug ),
            'files'              => $fStmt->fetchAll(),
        ],
    ] );
} catch ( Exception $e ) {
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'Database error: ' . $e->getMessage() ] );
}
