<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/artist-registrations.php';
require_once __DIR__ . '/includes/panel-guard.php';

// This page is the marketplace service's verification step: what the artist
// bought, and what the studio issued for it.
$panelPage = 'verification';

if ( ! customer_has_service( ARTIST_REGISTRATION_SLUG ) ) {
	header( 'Location: ' . $panelBase . 'customer-dashboard' );
	exit;
}

$pageTitle       = 'Artist Verification - BDC Music';
$metaDescription = 'Track your BDC Artists Marketplace verification and view or download your Artist ID Card and Certificate of Registration.';
$currentPage     = 'dashboard/artist-verification';

// Every package the artist has bought is its own registration, because each
// purchase is reviewed on its own, so the page shows the newest first. A failed
// query renders the empty state rather than a fatal, matching how panel-guard
// degrades when the database is briefly unavailable.
$artistRegistrations = array();

try {
	$artistRegistrations = customer_artist_registrations( db_connect(), $_SESSION['user_id'] );
} catch ( Throwable $e ) {
	app_log( 'artist-verification', 'registrations unavailable for ' . $_SESSION['user_id'], $e );
}

include_once __DIR__ . '/../header.php';

/**
 * Where a document link points, and what the artist sees while waiting.
 *
 * Every link goes through includes/artist-document.php rather than straight to
 * the file on disk: the documents live outside the web root's reach and must
 * never open for anyone but the owner.
 */
function verification_document_url( $basePath, $registrationId, $kind, $mode ) {
	return $basePath . 'includes/artist-document.php?registration=' . rawurlencode( $registrationId )
		. '&doc=' . rawurlencode( $kind )
		. '&mode=' . rawurlencode( $mode );
}

function verification_status_badge_class( $status ) {
	if ( $status === 'verified' ) {
		return 'status-delivered';
	}

	if ( $status === 'rejected' ) {
		return 'status-cancelled';
	}

	return 'status-pending';
}

/**
 * A stored datetime in the artist's local format, or a dash when there is none.
 *
 * @param string|null $value MySQL datetime.
 * @return string
 */
function verification_date_display( $value ) {
	$value = trim( (string) $value );

	if ( $value === '' || $value === null || strtotime( $value ) === false ) {
		return '—';
	}

	return date( 'd M Y, g:i a', strtotime( $value ) );
}

/**
 * The explanation for a status, in the artist's own terms: what is happening
 * next, and what they can do about it.
 */
function verification_status_message( $registration ) {
	$status = (string) $registration['status'];

	if ( $status === 'verified' ) {
		return 'Your registration has been verified. Keep your Artist ID Card and Certificate with you; quote your Artist ID when you book studio services.';
	}

	if ( $status === 'rejected' ) {
		$note = trim( (string) ( $registration['review_note'] ?? '' ) );

		if ( $note !== '' ) {
			return 'We could not verify this registration. Reason given: "' . $note . '" Reply to the email we sent, or book again with updated details.';
		}

		return 'We could not verify this registration. Reply to the email we sent us and we will take another look.';
	}

	return 'Your registration has reached the studio and is waiting to be checked. We will email you as soon as it has been reviewed.';
}
?>
<section class="panel-page">
	<div class="container">
		<div class="panel-layout">
			<?php include __DIR__ . '/includes/panel-nav.php'; ?>
			<main class="panel-main">
				<div class="panel-mobile-toggle">
					<button type="button" class="btn panel-menu-btn" id="dash-menu-toggle">
						<i data-lucide="menu"></i> Menu
					</button>
				</div>
				<div class="panel-tab active" id="tab-verification">
					<div class="panel-tab-header">
						<h2>Artist Verification</h2>
						<p>Your BDC Artists Marketplace registrations, and the documents issued for them.</p>
					</div>
					<?php if ( empty( $artistRegistrations ) ) : ?>
						<div class="panel-empty">
							<i data-lucide="badge-check"></i>
							<p>You have not registered with the BDC Artists Marketplace yet.</p>
							<a class="btn" href="<?php echo htmlspecialchars( $basePath . 'artists-marketplace' ); ?>"><i data-lucide="palette"></i> View Marketplace Packages</a>
						</div>
					<?php else : ?>
						<?php foreach ( $artistRegistrations as $artistRegistration ) : ?>
							<?php
							$registrationIsVerified = $artistRegistration['status'] === 'verified';
							$reviewNote             = trim( (string) ( $artistRegistration['review_note'] ?? '' ) );
							?>
							<div class="panel-profile-card" style="margin-bottom:18px;">
								<div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-start;justify-content:space-between;">
									<div>
										<h3 style="margin:0 0 4px;font-size:1.05rem;">
											<?php echo htmlspecialchars( artist_registration_category_label( $artistRegistration['category_slug'] ) ); ?>
										</h3>
										<span style="color:var(--muted);font-size:0.85rem;">
											<?php echo htmlspecialchars( (string) ( $artistRegistration['plan_name'] ?? 'Marketplace package' ) ); ?>
											<?php if ( ! empty( $artistRegistration['plan_price'] ) ) : ?>
												&middot; <?php echo htmlspecialchars( (string) $artistRegistration['plan_price'] ); ?>
											<?php endif; ?>
											&middot; Order <?php echo htmlspecialchars( (string) $artistRegistration['booking_id'] ); ?>
										</span>
									</div>
									<span class="panel-status-badge <?php echo verification_status_badge_class( $artistRegistration['status'] ); ?>">
										<?php echo htmlspecialchars( artist_registration_status_label( $artistRegistration['status'] ) ); ?>
									</span>
								</div>
								<p style="margin:14px 0 0;font-size:0.9rem;line-height:1.6;">
									<?php echo htmlspecialchars( verification_status_message( $artistRegistration ) ); ?>
								</p>
								<div class="detail-grid" style="margin:16px 0 0;">
									<div class="panel-detail-row">
										<span class="panel-label">Artist ID</span>
										<span class="panel-value"><?php
											echo htmlspecialchars(
												(string) ( $artistRegistration['artist_code'] ?? '' ) !== ''
													? (string) $artistRegistration['artist_code']
													: 'Issued on verification'
											);
										?></span>
									</div>
									<div class="panel-detail-row">
										<span class="panel-label">Registered on</span>
										<span class="panel-value"><?php echo htmlspecialchars( verification_date_display( $artistRegistration['created_at'] ?? '' ) ); ?></span>
									</div>
									<div class="panel-detail-row">
										<span class="panel-label">Reviewed on</span>
										<span class="panel-value"><?php echo htmlspecialchars( verification_date_display( $artistRegistration['reviewed_at'] ?? '' ) ); ?></span>
									</div>
								</div>
								<?php if ( $registrationIsVerified && ! empty( $artistRegistration['artist_code'] ) ) : ?>
									<div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;">
										<a class="btn" style="background:var(--secondary);color:var(--text);" href="<?php echo htmlspecialchars( verification_document_url( $basePath, $artistRegistration['id'], 'id-card', 'view' ) ); ?>" target="_blank" rel="noopener"><i data-lucide="eye"></i> View Artist ID Card</a>
										<a class="btn" style="background:var(--secondary);color:var(--text);" href="<?php echo htmlspecialchars( verification_document_url( $basePath, $artistRegistration['id'], 'id-card', 'download' ) ); ?>" target="_blank" rel="noopener"><i data-lucide="download"></i> Download ID Card</a>
										<a class="btn" href="<?php echo htmlspecialchars( verification_document_url( $basePath, $artistRegistration['id'], 'certificate', 'view' ) ); ?>" target="_blank" rel="noopener"><i data-lucide="award"></i> View Certificate</a>
										<a class="btn" href="<?php echo htmlspecialchars( verification_document_url( $basePath, $artistRegistration['id'], 'certificate', 'download' ) ); ?>" target="_blank" rel="noopener"><i data-lucide="download"></i> Download Certificate</a>
									</div>
									<p class="panel-hint" style="margin-top:10px;">
										Both documents open in your browser and can be saved as PDF from the print dialog.
									</p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</main>
		</div>
	</div>
</section>
<?php include __DIR__ . '/includes/panel-config.php'; ?>
<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>
<?php include_once __DIR__ . '/../footer.php'; ?>
