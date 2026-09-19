<?php
session_start();
include_once 'header.php';

$booking   = $_SESSION['booking_success'] ?? null;
$bookingId = $_GET['booking_id'] ?? '';

if ( ! $booking && $bookingId ) {
    $path = __DIR__ . '/data/bookings.json';
    if ( file_exists( $path ) ) {
        $bookings = json_decode( file_get_contents( $path ), true ) ?: [];
        foreach ( $bookings as $entry ) {
            if ( $bookingId === ( $entry['booking_id'] ?? '' ) ) {
                $booking = $entry;
                break;
            }
        }
    }
}

if ( ! $booking ) {
    echo '<main class="booking-page"><div class="container"><div class="booking-shell"><div class="booking-body"><h1>Booking not found</h1><p>We could not find your booking details.</p></div></div></div></main>';
    include_once 'footer.php';
    exit;
}
?>

<main class="booking-page">
    <div class="container">
        <div class="booking-shell">
            <div class="booking-body">
                <p class="eyebrow">BOOKING CONFIRMED</p>
                <h1>Thank you for booking with BDC Music</h1>
                <p>Your service request has been received successfully. A confirmation email has been sent to you and to the studio owner.</p>

                <div class="thanks-box">
                    <h3>Booking Details</h3>
                    <div class="detail-grid">
                        <div class="detail-card"><strong>Booking ID</strong><br><?= htmlspecialchars( $booking['booking_id'] ?? '' ) ?></div>
                        <div class="detail-card"><strong>Invoice No</strong><br><?= htmlspecialchars( $booking['invoice_no'] ?? '' ) ?></div>
                        <div class="detail-card"><strong>Service</strong><br><?= htmlspecialchars( $booking['service'] ?? '' ) ?></div>
                        <div class="detail-card"><strong>Price</strong><br>₹<?= number_format( $booking['price'] ?? 0 ) ?></div>
                        <div class="detail-card"><strong>Status</strong><br><?= htmlspecialchars( $booking['status'] ?? 'Pending Confirmation' ) ?></div>
                        <div class="detail-card"><strong>Payment Status</strong><br><?= htmlspecialchars( $booking['payment_status'] ?? 'Awaiting Payment' ) ?></div>
                    </div>
                </div>

                <div class="thanks-box">
                    <h3>What happens next?</h3>
                    <ul>
                        <?php if ( ! empty( $booking['auto_password'] ) ) : ?>
                            <li>A guest account has been created for you. Your login email is <strong><?= htmlspecialchars( $booking['customer_email'] ?? '' ) ?></strong> and your temporary password is <strong><?= htmlspecialchars( $booking['auto_password'] ) ?></strong>. Please save this or reset your password after logging in.</li>
                        <?php else : ?>
                            <li>You can manage your bookings from your dashboard.</li>
                        <?php endif; ?>
                        <li>The studio owner will review your request and contact you shortly.</li>
                        <li>You will receive email updates for your booking confirmation and service details.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'footer.php'; ?>
