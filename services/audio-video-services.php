	<?php
	$pageTitle = 'Audio & Video Services | BDC Music Studio';
	$metaDescription = 'Book professional audio and video services for recording, music production, mixing, mastering, editing, music videos, reels, events, and corporate videos.';
	$ogTitle = $pageTitle;
	$ogDescription = $metaDescription;

	// Everything priced on this page comes from service_plans, the same table the
	// checkout charges from, so a sub-service cannot be advertised at one price
	// and charged at another. The two arrays of prices this page used to carry
	// are gone; the twelve rows below are rendered by booking_rate_card().
	require_once __DIR__ . '/../includes/config.php';
	require_once __DIR__ . '/../includes/database.php';
	require_once __DIR__ . '/../includes/booking-marketing.php';

	include_once '../header.php';

	// Split the twelve sub-services into the two rate cards the page presents,
	// keeping the order the catalogue publishes them in. The two bundle groups
	// are sold as cards instead and are left out of the rate cards here.
	$avPdb      = booking_marketing_pdb();
	$avService  = booking_service( $avPdb, 'audio-video' );
	$audioKeys  = array();
	$videoKeys  = array();
	$bundleGroups = booking_av_bundle_groups();

	foreach ( $avService ? booking_plan_groups( $avPdb, (int) $avService['id'] ) : array() as $avGroup ) {
		if ( isset( $bundleGroups[ (string) $avGroup['key'] ] ) ) {
			continue;
		}

		if ( booking_av_group_category( $avGroup['key'] ) === 'Audio' ) {
			$audioKeys[] = (string) $avGroup['key'];
		} else {
			$videoKeys[] = (string) $avGroup['key'];
		}
	}
	?>
	<section class="page-hero pb-0">
		<div class="container">
			<div class="breadcrumb">
				<a href="<?php echo $siteUrl; ?>">Home</a>
				<span>/</span>
				<a href="<?php echo $siteUrl; ?>all-services">Services</a>
				<span>/</span>
				<span>Audio & Video Services</span>
			</div>

			<div class="section-hero audio-video-bg">
				<div class="section-hero-content">
					<span class="heading-tag">
						AUDIO & VIDEO SERVICES
					</span>
					<h1>
						Recording, Production,<br>
						Editing & Creative<br>
						<span class="highlight">Studio Solutions</span>
					</h1>
					<p>
						Professional recording, music production, mixing, mastering,
						video editing and creative services — all under one premium
						studio workflow.
					</p>
					<div class="hero-actions">
						<a href="#choose-package" class="btn">
							Find Your Package
						</a>
					</div>
				</div>
			</div>

			<section class="section-block pb-120" id="choose-package">
				<h2>Package Comparison</h2>

				<p class="section-intro">
					Compare our Audio and Video service packages to find the
					perfect solution for your recording, production and editing
					requirements.
				</p>

				<?php
				// includes/plan-enquiry-submit.php bounces a rejected enquiry back
				// here, because the modal is a plain form rather than an XHR.
				if ( isset( $_GET['enquiry_error'] ) ) :
					?>
					<div class="notice error">
						<strong><?php echo booking_esc( clean_text( $_GET['enquiry_error'] ) ); ?></strong>
					</div>
				<?php endif; ?>

				<?php
				// The two sides are shown one at a time. Which one is open is a
				// presentation choice only: the package a customer picks is what
				// decides their Service Category later in the booking, so the two
				// can never disagree.
				?>
				<div class="av-switch" data-av-switch>
					<button type="button" class="av-switch-btn is-active" data-av-target="Audio" aria-pressed="true">Audio</button>
					<button type="button" class="av-switch-btn" data-av-target="Video" aria-pressed="false">Video</button>
				</div>

				<div class="comparison-wrap">
					<!-- ==========================================
						AUDIO SERVICES
					=========================================== -->
					<div class="category-block" data-av-panel="Audio">
						<h3>Audio Production Packages</h3>
						<p>
							Professional recording, music production, mixing,
							mastering and studio sessions for artists,
							creators and commercial projects.
						</p>

						<?php
						// Every cell is a service_plans row, so the price shown is
						// the price the catalogue publishes. These twelve
						// sub-service groups are a reference price list: their
						// plans are marked is_orderable = 0, so the cells render
						// as plain text and booking_resolve_selection() refuses
						// those ids if one is posted anyway.
						echo booking_rate_card( 'audio-video', $audioKeys, $avPdb );
						?>

						<p class="section-intro" style="margin-top:20px;">
							Prefer one booking for the whole audio workflow? The
							four studio bundles below cover recording, production,
							mixing and mastering together.
						</p>

						<?php
						// The four bundle plans, sold as cards rather than as
						// rate-card rows.
						echo booking_plan_grid( 'audio-video', $avPdb, array( 'group_key' => 'audio-bundles' ) );
						?>
					</div>

					<!-- ==========================================
						VIDEO SERVICES
					=========================================== -->
					<div class="category-block" data-av-panel="Video">
						<h3>Video Production Packages</h3>
						<p>
							Professional video production, editing,
							music videos, corporate films,
							social media content and event coverage.
						</p>

						<?php echo booking_rate_card( 'audio-video', $videoKeys, $avPdb ); ?>

						<p class="section-intro" style="margin-top:20px;">
							Prefer one booking for the whole video workflow? The
							four studio bundles below cover production, editing,
							motion graphics and delivery together.
						</p>

						<?php
						echo booking_plan_grid( 'audio-video', $avPdb, array( 'group_key' => 'video-bundles' ) );
						?>

						<p class="section-intro" style="margin-top:20px;">
						Each figure above is our published rate for that
						sub-service. Booking is done through the studio bundles
						on this page, so pick the bundles that cover the work you
						need, or contact us to scope something these tables do
						not cover.
						</p>
					</div>
				</div>

				<p class="section-intro" style="margin-top:40px;">
					A tier marked <strong>Custom Quote</strong> is scoped to the
					project rather than sold at a fixed price. Use
					<strong>Enquire Now</strong> and our team will come back to you
					with a quote for that piece of work.
				</p>
			</section>

	</div>
	</section>

	<?php include_once __DIR__ . '/../includes/plan-enquiry-modal.php'; ?>
	<script src="<?php echo $assetPath; ?>js/audio-video-services.js"></script>
	<?php include_once '../footer.php'; ?>
