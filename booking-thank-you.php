<?php
/**
 * Booking confirmation.
 *
 * Reads the booking from the database rather than from a session or a JSON file,
 * because the confirmation has to survive the session being cleared, and because
 * it is reachable again later from the customer's dashboard.
 *
 * Ownership: a booking is only ever shown to the signed-in customer who owns it,
 * or to the guest whose browser created it in this session. Guessing an order id
 * is therefore not enough to read someone else's booking, which is why the guest
 * check is against the session rather than against the id alone.
 *
 * Query: ?order=BDCM-XXXXXX
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/booking-catalog.php';
require_once __DIR__ . '/includes/booking-session.php';
require_once __DIR__ . '/includes/booking-registry.php';
require_once __DIR__ . '/includes/service-fields.php';

if ( ! booking_ensure_session() ) {
	http_response_code( 500 );
	exit( 'Booking is temporarily unavailable. Please try again.' );
}

$orderParam = isset( $_GET['order'] ) ? clean_text( $_GET['order'] ) : '';
$booking    = null;
$files      = array();
$owned      = false;

if ( $orderParam !== '' ) {
	$pdo  = db_connect();
	$stmt = $pdo->prepare( 'SELECT * FROM bookings WHERE booking_id = :bid LIMIT 1' );
	$stmt->execute( array( ':bid' => $orderParam ) );
	$booking = $stmt->fetch( PDO::FETCH_ASSOC );

	if ( $booking ) {
		// Both sides are the varchar customer id (e.g. CUST-A1B2C3D4), so this
		// must be a string comparison. Casting to int would make it 0 for every
		// real customer and lock them out of their own booking.
		$sessionUser = ! empty( $_SESSION['user_id'] ) ? (string) $_SESSION['user_id'] : '';
		$isOwner     = $sessionUser !== ''
			&& (string) $booking['customer_id'] === $sessionUser;

		// A guest has no account row, so the only thing tying them to this
		// booking is the session that created it.
		$isGuestHere = ( $booking['customer_type'] === 'guest' )
			&& ! empty( $_SESSION['booking_confirmed'] )
			&& hash_equals( (string) $_SESSION['booking_confirmed'], (string) $booking['booking_id'] );

		$owned = $isOwner || $isGuestHere;

		if ( $owned ) {
			$fileStmt = $pdo->prepare( 'SELECT original_name, field_name, mime_type, file_size
                                        FROM uploaded_files
                                        WHERE booking_id = :bid
                                        ORDER BY uploaded_at' );
			$fileStmt->execute( array( ':bid' => $booking['booking_id'] ) );
			$files = $fileStmt->fetchAll( PDO::FETCH_ASSOC );
		}
	}
}

// Quote or not is a property of the service, not of how the order happens to be
// settled. It used to be read off payment_provider === 'manual', which was true
// of every quote order but is also true of a priced order placed with no gateway
// switched on - so an offline booking showed "REQUEST RECEIVED" and "Awaiting
// quote" against a real total. The service row is the same authority the
// checkout, the creator and the session all use. Resolved up here because the
// page title is wrong for a quote otherwise.
$slug = $booking ? (string) ( $booking['service_slug'] ?? '' ) : '';

if ( $booking && $slug !== '' ) {
	$service = booking_service( $pdo, $slug, false );
	$isQuote = $service
		? booking_is_quote_mode( $service )
		// Service since deleted or deactivated: fall back to the evidence the
		// order itself carries. A quote has no package and no price; anything
		// else is a real order.
		: ( ( $booking['plan_id'] ?? '' ) === '' || (float) $booking['price'] <= 0 );
} else {
	$isQuote = false;
}

$pageTitle       = $isQuote ? 'Quote Request Received' : 'Booking Confirmed';
$metaDescription = $isQuote
	? 'Your request to BDC Music Studio. Our team will review your brief and send a quote.'
	: 'Your booking with BDC Music Studio.';

include_once __DIR__ . '/header.php';
?>

<main class="booking-page">
	<div class="container">
		<div class="booking-shell">
			<div class="booking-body">

				<?php if ( ! $booking ) : ?>

					<h1>Booking not found</h1>
					<p>We could not find a booking with that reference. Please check the link in your confirmation email, or contact us and we will look it up.</p>
					<p><a class="btn" href="<?php echo booking_esc( url( 'all-services.php' ) ); ?>">Browse our services</a></p>

				<?php elseif ( ! $owned ) : ?>

					<h1>This booking is not yours</h1>
					<p>
						That booking belongs to another account, so we cannot show it here.
						If you believe this is wrong, please contact us with your email address
						and we will help.
					</p>

				<?php else : ?>

					<?php
					// $isQuote and $slug are resolved above, before the page title.
					$isPaid   = ( $booking['payment_status'] ?? '' ) === 'paid';
					$meta       = json_decode( (string) $booking['meta'], true );
					$meta       = is_array( $meta ) ? $meta : array();
					$metaPairs  = service_meta_pairs( $slug, $meta );
					$nextSteps  = service_progress_options( $slug );
					?>

					<p class="booking-step-count"><?php echo $isQuote ? 'REQUEST RECEIVED' : 'BOOKING CONFIRMED'; ?></p>
					<h1><?php echo $isQuote
						? 'We have your brief'
						: 'Thank you, ' . booking_esc( $booking['customer_name'] ); ?></h1>

					<?php if ( $isQuote ) : ?>
						<p>
							We have received your requirements for
							<strong><?php echo booking_esc( $booking['service_name'] ); ?></strong>.
							There is nothing to pay today. Our team will review the brief and send you a
							quote, and you will hear from us at
							<strong><?php echo booking_esc( $booking['customer_email'] ); ?></strong>.
						</p>
					<?php elseif ( $isPaid ) : ?>
						<p>
							We have received your payment of
							<strong><?php echo booking_esc( booking_money( $booking['price'] ) ); ?></strong>
							for <strong><?php echo booking_esc( $booking['service_name'] ); ?></strong>.
							A confirmation has been sent to
							<strong><?php echo booking_esc( $booking['customer_email'] ); ?></strong>.
						</p>
					<?php else : ?>
						<p>
							Your booking for <strong><?php echo booking_esc( $booking['service_name'] ); ?></strong>
							is saved but the payment has not completed. You can pay from your dashboard, or
							contact us and we will take the booking directly.
						</p>
					<?php endif; ?>

					<div class="thanks-box">
						<h3>Booking details</h3>
						<div class="detail-grid">
							<div class="detail-card">
								<strong>Booking ID</strong><br>
								<?php echo booking_esc( $booking['booking_id'] ); ?>
							</div>
							<?php if ( ! empty( $booking['invoice_no'] ) ) : ?>
								<div class="detail-card">
									<strong>Invoice No</strong><br>
									<?php echo booking_esc( $booking['invoice_no'] ); ?>
								</div>
							<?php endif; ?>
							<div class="detail-card">
								<strong>Service</strong><br>
								<?php echo booking_esc( $booking['service_name'] ); ?>
							</div>
							<?php if ( ! empty( $booking['plan_name'] ) ) : ?>
								<div class="detail-card">
									<strong>Package</strong><br>
									<?php echo booking_esc( $booking['plan_name'] ); ?>
									<?php if ( ! empty( $booking['plan_group_label'] ) ) : ?>
										<br><small><?php echo booking_esc( $booking['plan_group_label'] ); ?></small>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<div class="detail-card">
								<strong><?php echo $isQuote ? 'Quoted on request' : 'Total'; ?></strong><br>
								<?php echo $isQuote
									? 'Awaiting quote'
									: booking_esc( booking_money( $booking['price'] ) ); ?>
							</div>
							<div class="detail-card">
								<strong>Status</strong><br>
								<?php echo booking_esc( ucfirst( (string) $booking['status'] ) ); ?>
							</div>
							<div class="detail-card">
								<strong>Payment</strong><br>
								<?php echo $isQuote
									? 'Not applicable'
									: booking_esc( ucfirst( (string) $booking['payment_status'] ) ); ?>
							</div>
							<?php if ( $isPaid && ! empty( $booking['payment_id'] ) ) : ?>
								<div class="detail-card">
									<strong>Payment ID</strong><br>
									<?php echo booking_esc( $booking['payment_id'] ); ?>
								</div>
							<?php endif; ?>
						</div>

						</div>

					<?php if ( $metaPairs ) : ?>
						<div class="thanks-box">
							<h3>What you told us</h3>
							<dl class="booking-review-list">
								<?php foreach ( $metaPairs as $pair ) : ?>
									<dt><?php echo booking_esc( $pair['label'] ); ?></dt>
									<dd><?php echo booking_esc( $pair['value'] ); ?></dd>
								<?php endforeach; ?>
							</dl>
						</div>
					<?php endif; ?>

					<?php if ( $files ) : ?>
						<div class="thanks-box">
							<h3>Files you uploaded</h3>
							<ul>
								<?php foreach ( $files as $file ) : ?>
									<li>
										<?php echo booking_esc( $file['original_name'] ); ?>
										<?php if ( ! empty( $file['field_name'] ) ) : ?>
											<small>(<?php echo booking_esc( str_replace( '_', ' ', (string) $file['field_name'] ) ); ?>)</small>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
					<div class="thanks-box">
						<h3>What happens next?</h3>
						<ul>
							<?php if ( is_array( $nextSteps ) && $nextSteps ) : ?>
								<?php foreach ( $nextSteps as $step ) : ?>
									<li><?php echo booking_esc( is_array( $step ) ? ( $step['label'] ?? '' ) : $step ); ?></li>
								<?php endforeach; ?>
							<?php else : ?>
								<li>The studio owner will review your request and contact you shortly.</li>
								<li>You will receive email updates for your confirmation and service details.</li>
							<?php endif; ?>

							<?php if ( ( $booking['customer_type'] ?? '' ) === 'registered' ) : ?>
								<li>You can manage this booking from your dashboard.</li>
							<?php endif; ?>
						</ul>
					</div>

					<p>
						<a class="btn" href="<?php echo booking_esc( url( 'all-services.php' ) ); ?>">Back to services</a>
					</p>

				<?php endif; ?>

			</div>
		</div>
	</div>
</main>

<?php include_once __DIR__ . '/footer.php'; ?>
