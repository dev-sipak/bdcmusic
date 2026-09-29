<?php
/**
 * Create a booking and handle payment initiation.
 *
 * Two actions, both POST-only:
 *   - action=quote  -> Promotion (or any quote-mode service): persist a booking
 *                      with price 0 and status awaiting, promoting uploads to
 *                      `data/uploads/booking/<booking_id>/`.
 *   - action=pay    -> Priced service: create a Razorpay order and persist a
 *                      pending booking in a transaction.
 *
 * No GET access. Requires: booking-catalog.php, booking-session.php,
 * booking-registry.php, database.php, helpers.php, file-upload.php, and Razorpay
 * SDK if RAZORPAY_KEY_ID/SECRET are set. Production must not fall back to demo
 * keys silently; local demo is only allowed when explicitly enabled.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/file-upload.php';
require_once __DIR__ . '/booking-catalog.php';
require_once __DIR__ . '/booking-session.php';
require_once __DIR__ . '/booking-registry.php';

// Razorpay SDK.
$razorpayInclude = dirname( __DIR__ ) . '/vendor/razorpay/razorpay/src/Api.php';
if ( file_exists( $razorpayInclude ) ) {
    require_once $razorpayInclude;
}

// Do not leak session output before a JSON response.
if ( ! booking_ensure_session() ) {
    http_response_code( 500 );
    echo json_encode( array(
        'success' => false,
        'message' => 'Booking is temporarily unavailable. Please try again.',
    ) );
    exit;
}

function booking_json( array $data ) {
    header( 'Content-Type: application/json' );
    echo json_encode( $data );
    exit;
}

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    booking_json( array( 'success' => false, 'message' => 'Method not allowed.' ) );
}

$token = isset( $_POST['csrf_token'] ) ? (string) $_POST['csrf_token'] : '';
if ( ! verify_csrf_token( $token ) ) {
    http_response_code( 419 );
    booking_json( array( 'success' => false, 'message' => 'Your session has expired. Please try again.' ) );
}

$pdb    = db_connect();
$draft  = booking_draft();
$action = isset( $_POST['action'] ) ? clean_text( $_POST['action'] ) : '';

// bookings.customer_id points at users.id and is nullable, so a guest booking is
// simply a NULL there rather than a row of its own.
$customerId = ! empty( $_SESSION['user_id'] ) ? (string) $_SESSION['user_id'] : null;

/**
 * Move the draft's staged uploads into the booking's own directory and record
 * them in `uploaded_files`, which is where both dashboards read them from.
 *
 * The draft is the only thing naming these files, and a path is only accepted
 * when it sits inside the staging directory this draft created, so a tampered
 * session record cannot promote or overwrite a file elsewhere on disk. Anything
 * that fails to move is left in staging for booking_clear_staged_uploads() to
 * remove, and is simply not listed on the booking.
 *
 * @param string       $bookingId
 * @param array        $staged    Staged upload records from the draft.
 * @param string       $staging   This draft's staging directory.
 * @param PDO          $pdo
 * @return int Number of files recorded.
 */
function booking_promote_uploads( $bookingId, array $staged, $staging, $pdo ) {
    if ( ! $staged ) {
        return 0;
    }

    $dir = dirname( __DIR__ ) . '/data/uploads/booking/' . $bookingId;

    if ( ! is_dir( $dir ) ) {
        mkdir( $dir, 0755, true );
    }

    $stmt = $pdo->prepare( 'INSERT INTO uploaded_files
        (booking_id, field_name, original_name, stored_name, file_path, mime_type, file_size)
        VALUES (:booking_id, :field_name, :original_name, :stored_name, :file_path, :mime_type, :file_size)'
    );

    $saved = 0;

    foreach ( $staged as $upload ) {
        $src = (string) ( $upload['path'] ?? '' );

        if ( $src === '' || $staging === '' || strpos( $src, $staging ) !== 0
            || ! is_file( $src ) || empty( $upload['stored'] ) ) {
            continue;
        }

        // `stored` is the collision-safe name already on disk; `name` is what the
        // customer actually uploaded and is only kept for display.
        $dest     = $dir . DIRECTORY_SEPARATOR . $upload['stored'];
        $relative = 'data/uploads/booking/' . $bookingId . '/' . $upload['stored'];

        if ( ! @rename( $src, $dest ) ) {
            continue;
        }

        $stmt->execute( array(
            ':booking_id'     => $bookingId,
            ':field_name'     => (string) ( $upload['field'] ?? '' ),
            ':original_name'  => (string) $upload['name'],
            ':stored_name'    => (string) $upload['stored'],
            ':file_path'      => $relative,
            ':mime_type'      => isset( $upload['type'] ) ? (string) $upload['type'] : null,
            ':file_size'      => isset( $upload['size'] ) ? (int) $upload['size'] : 0,
        ) );

        $saved++;
    }

    return $saved;
}

// ─── Quote request ──────────────────────────────────────────────────────

if ( $action === 'quote' ) {
    $slug    = (string) ( $draft['service'] ?? '' );
    $service = $slug !== '' ? booking_service( $pdb, $slug ) : null;

    if ( ! $service || ! booking_is_quote_mode( $service ) ) {
        booking_json( array( 'success' => false, 'message' => 'This service is not a quote request.' ) );
    }

    list( $contact, $contactErrors ) = booking_validate_contact( (array) ( $draft['contact'] ?? array() ) );
    if ( $contactErrors ) {
        booking_json( array( 'success' => false, 'message' => 'Please complete your contact details.' ) );
    }

    // The draft's values are already validated; re-running the walk here is what
    // stops a value that has since become conditional-hidden from being stored.
    list( $details, $detailErrors ) = booking_validate_details(
        $service['slug'],
        (array) ( $draft['details'] ?? array() ),
        array()
    );

    if ( $detailErrors ) {
        booking_json( array( 'success' => false, 'message' => 'Please complete the required details.' ) );
    }

    $staged    = (array) ( $draft['uploads'] ?? array() );
    $staging   = (string) ( $draft['upload_dir'] ?? '' );
    $bookingId = generate_booking_id();

    $pdb->beginTransaction();

    try {
        // plan_group is NOT NULL with an empty default: a quote has no package
        // group, so it is the empty string rather than NULL.
        $stmt = $pdb->prepare( 'INSERT INTO bookings (
            booking_id, customer_id, customer_type,
            customer_name, customer_email, customer_phone, customer_whatsapp,
            service_id, service_name, service_slug, plan_id, plan_name,
            plan_group, plan_group_label,
            price, subtotal, addons_total, currency,
            status, payment_status, payment_provider,
            meta, message
        ) VALUES (
            :booking_id, :customer_id, :customer_type,
            :customer_name, :customer_email, :customer_phone, :customer_whatsapp,
            :service_id, :service_name, :service_slug, NULL, NULL,
            :plan_group, NULL,
            0, 0, 0, :currency,
            :status, :payment_status, :payment_provider,
            :meta, :message
        )' );

        $stmt->execute( array(
            ':booking_id'        => $bookingId,
            ':customer_id'       => $customerId,
            ':customer_type'     => $customerId !== null ? 'registered' : 'guest',
            ':customer_name'     => (string) ( $contact['name'] ?? '' ),
            ':customer_email'    => (string) ( $contact['email'] ?? '' ),
            ':customer_phone'    => (string) ( $contact['phone'] ?? '' ),
            ':customer_whatsapp' => (string) ( $contact['whatsapp'] ?? '' ),
            ':service_id'        => (int) $service['id'],
            ':service_name'      => (string) $service['name'],
            ':service_slug'      => (string) $service['slug'],
            ':plan_group'        => '',
            ':currency'          => 'INR',
            // A quote has nothing to pay, so it is left awaiting an admin quote
            // rather than pending a payment the customer was never going to make.
            ':status'            => 'pending',
            ':payment_status'    => 'awaiting',
            ':payment_provider'  => 'manual',
            ':meta'              => json_encode( $details ),
            ':message'           => (string) ( $details['message'] ?? '' ),
        ) );

        booking_promote_uploads( $bookingId, $staged, $staging, $pdb );

        // The attempt trail, so a failed retry cannot overwrite the previous
        // order id and the admin can reconstruct what happened. razorpay-verify
        // looks the order up here, which is what stops a client inventing one.
        $pay = $pdb->prepare( 'INSERT INTO booking_payments
            (booking_id, provider, razorpay_order_id, amount, currency, status)
            VALUES (:booking_id, :provider, :razorpay_order_id, :amount, :currency, :status)' );

        $pay->execute( array(
            ':booking_id'       => $bookingId,
            ':provider'         => 'manual',
            ':razorpay_order_id' => null,
            ':amount'           => 0.0,
            ':currency'         => 'INR',
            ':status'           => 'created',
        ) );

        $pdb->commit();
    } catch ( Throwable $e ) {
        $pdb->rollBack();
        booking_json( array( 'success' => false, 'message' => 'We could not save your quote. Please try again.' ) );
    }

    booking_clear_staged_uploads();
    booking_draft_reset();

    // A guest has no account, so this session marker is the only thing that will
    // let booking-thank-you.php show them their own booking later.
    $_SESSION['booking_confirmed'] = $bookingId;

    header( 'Location: ' . url( 'booking-thank-you.php?order=' . urlencode( $bookingId ) . '&quote=1' ) );
    exit;
}

// ─── Paid booking ───────────────────────────────────────────────────────

if ( $action !== 'pay' ) {
    booking_json( array( 'success' => false, 'message' => 'Unknown action.' ) );
}

$slug    = (string) ( $draft['service'] ?? '' );
$service = $slug !== '' ? booking_service( $pdb, $slug ) : null;

if ( ! $service || booking_is_quote_mode( $service ) ) {
    booking_json( array( 'success' => false, 'message' => 'This service cannot be paid online.' ) );
}

list( $contact, $contactErrors ) = booking_validate_contact( (array) ( $draft['contact'] ?? array() ) );
if ( $contactErrors ) {
    booking_json( array( 'success' => false, 'message' => 'Please complete your contact details.' ) );
}

list( $details, $detailErrors ) = booking_validate_details(
    $service['slug'],
    (array) ( $draft['details'] ?? array() ),
    array()
);

if ( $detailErrors ) {
    booking_json( array( 'success' => false, 'message' => 'Please complete the required details.' ) );
}

// Re-resolve the whole selection from the database. The plan, the add-ons and
// therefore the amount are read fresh here, so a price change or a package
// withdrawn since the review step cannot be charged at the old figure.
$selection = booking_resolve_selection(
    $pdb,
    $service['slug'],
    (string) ( $draft['group'] ?? '' ),
    (int) ( $draft['plan_id'] ?? 0 ),
    array(),
    booking_draft_plan_ids( $draft )
);

if ( ! $selection ) {
    booking_json( array( 'success' => false, 'message' => 'Your package is no longer available. Please choose another.' ) );
}

// Already computed in integer paise by the resolver, so no float rounding
// happens here and the amount charged is exactly the amount reviewed.
$totalPaise = (int) $selection['total_paise'];

// Allocated before the gateway call, because it doubles as the order receipt and
// the booking id the verification step will match against.
$bookingId = generate_booking_id();

$keyId      = (string) RAZORPAY_KEY_ID;
$keySecret  = (string) RAZORPAY_KEY_SECRET;
$demo       = BOOKING_DEMO === true;
$orderId    = '';

if ( $keyId !== '' && $keySecret !== '' && class_exists( 'Razorpay\Api\Api' ) ) {
    try {
        $api  = new Razorpay\Api\Api( $keyId, $keySecret );
        $order = $api->order->create( array(
            'receipt'  => $bookingId,
            'amount'   => $totalPaise,
            'currency' => 'INR',
        ) );

        $orderId = (string) $order['id'];
    } catch ( Throwable $e ) {
        http_response_code( 502 );
        booking_json( array( 'success' => false, 'message' => 'The payment gateway is not responding. Please try again.' ) );
    }
} elseif ( $demo ) {
    // Demo mode exists so the flow can be exercised before real keys are
    // available. The order id is fake, so razorpay-verify.php settles it without
    // calling the gateway. BOOKING_DEMO must be off in production.
    $orderId = 'order_demo_' . bin2hex( random_bytes( 8 ) );
} else {
    http_response_code( 503 );
    booking_json( array( 'success' => false, 'message' => 'Online payment is not configured yet. Please contact us and we will take your booking directly.' ) );
}

$staged    = (array) ( $draft['uploads'] ?? array() );
$staging   = (string) ( $draft['upload_dir'] ?? '' );

$plan        = $selection['plan'];
$planLines   = (array) $selection['plans'];
$totalRupees = (float) $selection['total'];

// The sub-service is chosen and priced on the package step, so it is derived
// here from the packages actually bought rather than asked for a second time
// on the details form. Both dashboards keep reading meta['service_type'], so
// the key is still written, now with one label per group on the order.
$serviceTypes = array();
foreach ( $planLines as $line ) {
    $label = isset( $line['group_label'] ) ? trim( (string) $line['group_label'] ) : '';
    if ( $label !== '' ) {
        $serviceTypes[ $label ] = true;
    }
}
if ( $serviceTypes && ! isset( $details['service_type'] ) ) {
    $details['service_type'] = implode( ', ', array_keys( $serviceTypes ) );
}

$pdb->beginTransaction();

try {
    // price is the authoritative grand total; subtotal and addons_total are the
    // components, kept separately so the admin order screen can show the
    // breakdown without re-deriving it.
    $stmt = $pdb->prepare( 'INSERT INTO bookings (
        booking_id, customer_id, customer_type,
        customer_name, customer_email, customer_phone, customer_whatsapp,
        service_id, service_name, service_slug,
        plan_id, plan_name, plan_group, plan_group_label,
        price, subtotal, addons_total, currency,
        status, payment_status, payment_provider, razorpay_order_id,
        meta, message
    ) VALUES (
        :booking_id, :customer_id, :customer_type,
        :customer_name, :customer_email, :customer_phone, :customer_whatsapp,
        :service_id, :service_name, :service_slug,
        :plan_id, :plan_name, :plan_group, :plan_group_label,
        :price, :subtotal, :addons_total, :currency,
        :status, :payment_status, :payment_provider, :razorpay_order_id,
        :meta, :message
    )' );

    $stmt->execute( array(
        ':booking_id'        => $bookingId,
        ':customer_id'       => $customerId,
        ':customer_type'     => $customerId !== null ? 'registered' : 'guest',
        ':customer_name'     => (string) ( $contact['name'] ?? '' ),
        ':customer_email'    => (string) ( $contact['email'] ?? '' ),
        ':customer_phone'    => (string) ( $contact['phone'] ?? '' ),
        ':customer_whatsapp' => (string) ( $contact['whatsapp'] ?? '' ),
        ':service_id'        => (int) $service['id'],
        ':service_name'      => (string) $service['name'],
        ':service_slug'      => (string) $service['slug'],
        ':plan_id'           => (int) $plan['id'],
        ':plan_name'         => (string) $plan['name'],
        ':plan_group'        => (string) $plan['group_key'],
        ':plan_group_label'  => isset( $plan['group_label'] ) ? (string) $plan['group_label'] : null,
        ':price'             => $totalRupees,
        ':subtotal'          => (float) $selection['subtotal'],
        ':addons_total'      => (float) $selection['addons_total'],
        ':currency'          => 'INR',
        // Awaiting, not paid: the money has not moved until razorpay-verify.php
        // confirms the signature, and this row must not claim otherwise.
        ':status'            => 'pending',
        ':payment_status'    => 'awaiting',
        ':payment_provider'  => 'razorpay',
        ':razorpay_order_id' => $orderId,
        ':meta'              => json_encode( $details ),
        ':message'           => (string) ( $details['message'] ?? '' ),
    ) );

    booking_promote_uploads( $bookingId, $staged, $staging, $pdb );

    // booking_items is the per-order package snapshot: one line per package on
    // the order, at the price the catalogue held when the order was created.
    // bookings.plan_id / plan_name / plan_group keep mirroring the first line,
    // so every reader that expects a single package still finds one, and
    // UNIQUE (booking_id, plan_id) is the server-side guarantee that the same
    // package cannot be charged twice in one order.
    if ( $planLines ) {
        $lineStmt = $pdb->prepare( 'INSERT INTO booking_items
            (booking_id, plan_id, plan_name, plan_group, plan_group_label, unit_price, qty, line_total)
            VALUES (:booking_id, :plan_id, :plan_name, :plan_group, :plan_group_label, :unit_price, 1, :line_total)' );

        foreach ( $planLines as $line ) {
            $label = isset( $line['group_label'] ) ? trim( (string) $line['group_label'] ) : '';

            $lineStmt->execute( array(
                ':booking_id'       => $bookingId,
                ':plan_id'          => (int) $line['id'],
                ':plan_name'        => (string) $line['name'],
                ':plan_group'       => (string) $line['group_key'],
                ':plan_group_label' => $label !== '' ? $label : null,
                ':unit_price'       => (float) $line['price'],
                ':line_total'       => (float) $line['price'],
            ) );
        }
    }

    // No add-on lines are written any more: an order is its package lines, and
    // `booking_addons` only holds the snapshot of orders placed before the
    // add-ons were dropped from the catalogue.

    $pay = $pdb->prepare( 'INSERT INTO booking_payments
        (booking_id, provider, razorpay_order_id, amount, currency, status)
        VALUES (:booking_id, :provider, :razorpay_order_id, :amount, :currency, :status)' );

    $pay->execute( array(
        ':booking_id'       => $bookingId,
        ':provider'         => 'razorpay',
        ':razorpay_order_id' => $orderId,
        ':amount'           => $totalRupees,
        ':currency'         => 'INR',
        ':status'           => 'created',
    ) );

    $pdb->commit();
} catch ( Throwable $e ) {
    $pdb->rollBack();
    booking_json( array( 'success' => false, 'message' => 'We could not save your booking. Please try again.' ) );
}

booking_clear_staged_uploads();
booking_draft_reset();

// razorpay-verify.php requires this marker before it will settle anything, so a
// visitor who merely guesses an order id cannot confirm somebody else's
// booking. Set before the draft is dropped, since it is the same session.
$_SESSION['booking_pending_payment'] = $bookingId;

// The checkout script redirects here once the payment is confirmed, and a guest
// has no account, so this is what proves the booking is theirs.
$_SESSION['booking_confirmed'] = $bookingId;

booking_json( array(
    'success'     => true,
    'booking_id'  => $bookingId,
    'order_id'    => $orderId,
    'amount'      => $totalPaise,
    'currency'    => 'INR',
    'key_id'      => $keyId,
    'demo'        => $demo,
    'name'        => SITE_NAME,
    'description' => (string) $service['name'] . ' booking',
    'prefill'     => array(
        'name'    => (string) ( $contact['name'] ?? '' ),
        'email'   => (string) ( $contact['email'] ?? '' ),
        'contact' => (string) ( $contact['phone'] ?? '' ),
    ),
    'notes'       => array( 'booking_id' => $bookingId ),
    'csrf_token'  => generate_csrf_token(),
) );
