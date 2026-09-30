<?php
$pageTitle = ' Promotion Services | BDC Music Studio';
$metaDescription = 'Promote your music videos, reels, and brand content with targeted video promotion strategies for better reach and engagement.';
$ogTitle = ' Promotion Services | BDC Music Studio';
$ogDescription = 'Promote your music videos, reels, and brand content with targeted video promotion strategies for better reach and engagement.';

// The catalogue is read for the call to action, so the page and the checkout
// can never quote a different price. Promotion runs in quote mode: it has no
// published packages, so no grid is rendered here.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/booking-marketing.php';

include_once '../header.php';
?>

<section class="page-hero">
	<div class="container">

		<div class="breadcrumb">

			<a href="<?php echo $siteUrl; ?>">Home</a>

			<span>/</span>

			<a href="<?php echo $siteUrl; ?>all-services">Services</a>

			<span>/</span>

			<span>Promotion Services</span>

		</div>

		<div class="section-hero promotional-bg">

			<div class="section-hero-content">

				<span class="heading-tag">
					MARKETING &amp; PROMOTION
				</span>

				<h1>
					Audio &amp; Video Promotion Services to Grow Reach and Engagement
				</h1>

				<p>
					Drive visibility for your reels, short films,
					music videos, and promotional content with a strategy
					built for discovery and audience growth.
				</p>

				<a
					href="<?php echo booking_esc( booking_preselect_url( 'promotion' ) ); ?>"
					class="btn"
				>
					Book Promotion Support
				</a>

			</div>

		</div>

		<section class="section-block">

			<h2>
				About the Service
			</h2>

			<p class="section-intro">
				Our promo services help artists and brands gain traction by positioning video content in front of the right audience. Whether you are launching a new track or building a social presence, we make sure your content is seen and remembered.
			</p>

		</section>

		<div class="section-block pt-0">

			<h2>
				Benefits
			</h2>

			<div class="card-grid-2">

				<!-- Benefit 1 -->

				<div class="card-inline">

					<div class="benefit-icon">
						<i data-lucide="eye"></i>
					</div>

					<div class="benefit-content">

						<h3>
							Better Visibility
						</h3>

						<p>
							Increase reach on YouTube, Instagram Reels, and other short-form platforms.
						</p>

					</div>

				</div>

				<!-- Benefit 2 -->

				<div class="card-inline">

					<div class="benefit-icon">
						<i data-lucide="users"></i>
					</div>

					<div class="benefit-content">

						<h3>
							Audience Growth
						</h3>

						<p>
							Reach viewers with stronger targeting and better content positioning.
						</p>

					</div>

				</div>

				<!-- Benefit 3 -->

				<div class="card-inline">

					<div class="benefit-icon">
						<i data-lucide="target"></i>
					</div>

					<div class="benefit-content">

						<h3>
							Targeted Promotion
						</h3>

						<p>
							Connect your music and video content with audiences who are more likely to engage with your work.
						</p>

					</div>

				</div>

				<!-- Benefit 4 -->

				<div class="card-inline">

					<div class="benefit-icon">
						<i data-lucide="chart-line"></i>
					</div>

					<div class="benefit-content">

						<h3>
							Stronger Engagement
						</h3>

						<p>
							Build greater attention and engagement around your music releases, videos, reels, and promotional content.
						</p>

					</div>

				</div>

			</div>

		</div>

		<!-- Pricing -->

		<div class="section-block pt-0">

			<?php
			// This service has no package list, because it is quoted per
			// project. The note explaining that used to sit inside the
			// enquiry section at the foot of this page. That section was a
			// booking form, not information, so removing it must not leave a
			// quote-mode service saying nothing at all about how it is
			// priced. booking_quote_note() only renders for quote-mode
			// services, so this stays empty if promotion ever gains plans.
			echo booking_quote_note( 'promotion' );
			?>

		</div>

	</div>

</section>

<?php include_once '../footer.php'; ?>
