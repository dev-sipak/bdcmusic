<?php
session_start();
include_once 'header.php';

$booking = $_SESSION['booking_success'] ?? null;
$bookingId = $_GET['booking_id'] ?? '';

if (!$booking && $bookingId) {
    $path = __DIR__ . '/data/bookings.json';
    if (file_exists($path)) {
        $bookings = json_decode(file_get_contents($path), true) ?: [];
        foreach ($bookings as $entry) {
            if (($entry['booking_id'] ?? '') === $bookingId) {
                $booking = $entry;
                break;
            }
        }
    }
}

if (!$booking) {
    echo '<main class="booking-page"><div class="container"><div class="booking-shell"><div class="booking-body"><h1>Booking not found</h1><p>We could not find your booking details.</p></div></div></div></main>';
    include_once 'footer.php';
    exit;
}
?>

<style>
    .booking-page { padding: 120px 0 80px; }
    .booking-shell { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.08); overflow: hidden; }
    .booking-body { padding: 40px; }
    .thanks-box { background: #f7fff9; border: 1px solid #ccefd0; border-radius: 18px; padding: 24px; margin-top: 20px; }
    .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 20px; }
    .detail-card { background: #fafafa; border: 1px solid #ececec; border-radius: 14px; padding: 14px; }
    @media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } .booking-body { padding: 24px; } }
</style>

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
                        <div class="detail-card"><strong>Booking ID</strong><br><?= htmlspecialchars($booking['booking_id'] ?? '') ?></div>
                        <div class="detail-card"><strong>Invoice No</strong><br><?= htmlspecialchars($booking['invoice_no'] ?? '') ?></div>
                        <div class="detail-card"><strong>Service</strong><br><?= htmlspecialchars($booking['service'] ?? '') ?></div>
                        <div class="detail-card"><strong>Price</strong><br>₹<?= number_format($booking['price'] ?? 0) ?></div>
                        <div class="detail-card"><strong>Status</strong><br><?= htmlspecialchars($booking['status'] ?? 'Pending Confirmation') ?></div>
                        <div class="detail-card"><strong>Payment Status</strong><br><?= htmlspecialchars($booking['payment_status'] ?? 'Awaiting Payment') ?></div>
                    </div>
                </div>

                <div class="thanks-box">
                    <h3>What happens next?</h3>
                    <ul>
                        <li>Your account has been created automatically for future bookings.</li>
                        <li>The studio owner will review your request and contact you shortly.</li>
                        <li>You will receive email updates for your booking confirmation and service details.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'footer.php'; ?>
