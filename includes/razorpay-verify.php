<?php
/**
 * Razorpay Payment Verification Endpoint
 *
 * Verifies payment signature server-side and confirms booking.
 */
session_start();

header( 'Content-Type: application/json' );

if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
    http_response_code( 405 );
    echo json_encode( [ 'error' => 'Method not allowed' ] );
    exit;
}

$input = json_decode( file_get_contents( 'php://input' ), true );

if ( empty( $input['razorpay_order_id'] ) || empty( $input['razorpay_payment_id'] ) || empty( $input['razorpay_signature'] ) ) {
    http_response_code( 400 );
    echo json_encode( [ 'error' => 'Missing payment parameters' ] );
    exit;
}

$razorpayOrderId  = $input['razorpay_order_id'];
$razorpayPaymentId = $input['razorpay_payment_id'];
$razorpaySignature = $input['razorpay_signature'];

$keyId     = getenv( 'RAZORPAY_KEY_ID' ) ?: 'rzp_test_demo';
$keySecret = getenv( 'RAZORPAY_KEY_SECRET' ) ?: 'demo_secret';

// Demo mode: skip real verification
if ( 'rzp_test_demo' === $keyId || 'demo_secret' === $keySecret ) {
    // In demo mode, just verify the signature format exists
    if ( empty( $razorpaySignature ) ) {
        http_response_code( 400 );
        echo json_encode( [ 'error' => 'Invalid payment signature' ] );
        exit;
    }

    // Mark session booking as paid
    if ( isset( $_SESSION['pending_booking'] ) ) {
        $booking     = $_SESSION['pending_booking'];
        $bookingType = $_SESSION['pending_booking_type'] ?? 'standard';

        $booking['payment_status']  = 'Paid';
        $booking['payment_id']      = $razorpayPaymentId;
        $booking['razorpay_order']  = $razorpayOrderId;
        $booking['status']          = 'Confirmed';
        $booking['paid_at']         = date( 'Y-m-d H:i:s' );

        $dataDir = dirname( __DIR__ ) . '/data';
        if ( ! is_dir( $dataDir ) ) {
            mkdir( $dataDir, 0755, true );
        }

        if ( 'audio_video' === $bookingType ) {
            // Handle audio-video booking
            $bookingsPath = $dataDir . '/audio_video_bookings.json';
            $bookings     = [];
            if ( file_exists( $bookingsPath ) ) {
                $bookings = json_decode( file_get_contents( $bookingsPath ), true ) ?: [];
            }

            // Retrieve uploaded files from session
            $booking['files'] = $_SESSION['pending_upload_files'] ?? [];
            unset( $_SESSION['pending_upload_files'], $_SESSION['pending_upload_dir'] );

            $bookings[] = $booking;
            file_put_contents( $bookingsPath, json_encode( $bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
        } else {
            // Handle standard booking from booking.php
            $bookingsPath = $dataDir . '/bookings.json';
            $bookings     = [];
            if ( file_exists( $bookingsPath ) ) {
                $bookings = json_decode( file_get_contents( $bookingsPath ), true ) ?: [];
            }

            $bookings[] = $booking;
            file_put_contents( $bookingsPath, json_encode( $bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

            // Auto-create customer account for guest bookings
            if ( ! empty( $booking['auto_create_account'] ) ) {
                $customersPath = $dataDir . '/customers.json';
                $customers     = [];
                if ( file_exists( $customersPath ) ) {
                    $customers = json_decode( file_get_contents( $customersPath ), true ) ?: [];
                }

                $normalizedEmail = strtolower( trim( $booking['customer_email'] ) );
                $accountExists   = false;

                foreach ( $customers as $customer ) {
                    if ( $normalizedEmail === ( $customer['email'] ?? '' ) ) {
                        $accountExists = true;
                        break;
                    }
                }

                if ( ! $accountExists ) {
                    $autoPassword = substr( md5( $booking['customer_email'] . time() ), 0, 12 );
                    $newCustomer  = [
                        'id'            => 'CUST-' . strtoupper( substr( md5( $normalizedEmail . time() ), 0, 8 ) ),
                        'name'          => $booking['customer_name'],
                        'email'         => $normalizedEmail,
                        'mobile'        => $booking['customer_mobile'],
                        'password_hash' => password_hash( $autoPassword, PASSWORD_BCRYPT ),
                        'created_at'    => date( 'Y-m-d H:i:s' ),
                        'source'        => 'guest_booking',
                    ];

                    $customers[] = $newCustomer;
                    file_put_contents( $customersPath, json_encode( $customers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

                    $booking['auto_password'] = $autoPassword;
                }
            }

            // Send notification email
            $ownerEmail = getenv( 'BOOKING_OWNER_EMAIL' ) ?: 'bdcmusic37@gmail.com';
            $subject    = 'New BDC Music booking confirmed';
            $emailMsg   = '<h3>Booking Confirmed</h3>'
                . '<p><strong>Customer:</strong> ' . htmlspecialchars( $booking['customer_name'] ) . '</p>'
                . '<p><strong>Email:</strong> ' . htmlspecialchars( $booking['customer_email'] ) . '</p>'
                . '<p><strong>Service:</strong> ' . htmlspecialchars( $booking['service'] ) . '</p>'
                . '<p><strong>Booking ID:</strong> ' . htmlspecialchars( $booking['booking_id'] ) . '</p>'
                . '<p><strong>Amount:</strong> Rs.' . number_format( $booking['price'] ) . '</p>'
                . '<p><strong>Payment ID:</strong> ' . htmlspecialchars( $razorpayPaymentId ) . '</p>';

            @mail( $ownerEmail, $subject, $emailMsg );
        }

        unset( $_SESSION['pending_booking'] );
        unset( $_SESSION['pending_booking_type'] );

        echo json_encode( [
            'success'    => true,
            'booking_id' => $booking['booking_id'] ?? generate_booking_id(),
            'message'    => 'Payment verified and booking confirmed.',
        ] );
        exit;
    }

    http_response_code( 400 );
    echo json_encode( [ 'error' => 'No pending booking found' ] );
    exit;
}

// Production mode: verify signature
$expectedSignature = hash_hmac( 'sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $keySecret );

if ( hash_equals( $expectedSignature, $razorpaySignature ) ) {
    // Payment verified — confirm booking
    if ( isset( $_SESSION['pending_booking'] ) ) {
        $booking     = $_SESSION['pending_booking'];
        $bookingType = $_SESSION['pending_booking_type'] ?? 'standard';

        $booking['payment_status']  = 'Paid';
        $booking['payment_id']      = $razorpayPaymentId;
        $booking['razorpay_order']  = $razorpayOrderId;
        $booking['status']          = 'Confirmed';
        $booking['paid_at']         = date( 'Y-m-d H:i:s' );

        $dataDir = dirname( __DIR__ ) . '/data';
        if ( ! is_dir( $dataDir ) ) {
            mkdir( $dataDir, 0755, true );
        }

        if ( 'audio_video' === $bookingType ) {
            $bookingsPath = $dataDir . '/audio_video_bookings.json';
            $bookings     = [];
            if ( file_exists( $bookingsPath ) ) {
                $bookings = json_decode( file_get_contents( $bookingsPath ), true ) ?: [];
            }

            // Retrieve uploaded files from session
            $booking['files'] = $_SESSION['pending_upload_files'] ?? [];
            unset( $_SESSION['pending_upload_files'], $_SESSION['pending_upload_dir'] );

            $bookings[] = $booking;
            file_put_contents( $bookingsPath, json_encode( $bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
        } else {
            $bookingsPath = $dataDir . '/bookings.json';
            $bookings     = [];
            if ( file_exists( $bookingsPath ) ) {
                $bookings = json_decode( file_get_contents( $bookingsPath ), true ) ?: [];
            }
            $bookings[] = $booking;
            file_put_contents( $bookingsPath, json_encode( $bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

            // Auto-create customer account for guest bookings
            if ( ! empty( $booking['auto_create_account'] ) ) {
                $customersPath = $dataDir . '/customers.json';
                $customers     = [];
                if ( file_exists( $customersPath ) ) {
                    $customers = json_decode( file_get_contents( $customersPath ), true ) ?: [];
                }

                $normalizedEmail = strtolower( trim( $booking['customer_email'] ) );
                $accountExists   = false;

                foreach ( $customers as $customer ) {
                    if ( $normalizedEmail === ( $customer['email'] ?? '' ) ) {
                        $accountExists = true;
                        break;
                    }
                }

                if ( ! $accountExists ) {
                    $autoPassword = substr( md5( $booking['customer_email'] . time() ), 0, 12 );
                    $newCustomer  = [
                        'id'            => 'CUST-' . strtoupper( substr( md5( $normalizedEmail . time() ), 0, 8 ) ),
                        'name'          => $booking['customer_name'],
                        'email'         => $normalizedEmail,
                        'mobile'        => $booking['customer_mobile'],
                        'password_hash' => password_hash( $autoPassword, PASSWORD_BCRYPT ),
                        'created_at'    => date( 'Y-m-d H:i:s' ),
                        'source'        => 'guest_booking',
                    ];

                    $customers[] = $newCustomer;
                    file_put_contents( $customersPath, json_encode( $customers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

                    $booking['auto_password'] = $autoPassword;
                }
            }
        }

        unset( $_SESSION['pending_booking'] );
        unset( $_SESSION['pending_booking_type'] );

        echo json_encode( [
            'success'    => true,
            'booking_id' => $booking['booking_id'] ?? generate_booking_id(),
            'message'    => 'Payment verified and booking confirmed.',
        ] );
        exit;
    }

    http_response_code( 400 );
    echo json_encode( [ 'error' => 'No pending booking found' ] );
    exit;
}

http_response_code( 400 );
echo json_encode( [ 'error' => 'Payment verification failed' ] );
