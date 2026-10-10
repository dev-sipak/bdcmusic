<?php
$pageTitle = ' Promotion Services | BDC Music';
$metaDescription = 'Promote your music videos, reels, and brand content with targeted video promotion strategies for better reach and engagement.';
$ogTitle = ' Promotion Services | BDC Music';
$ogDescription = 'Promote your music videos, reels, and brand content with targeted video promotion strategies for better reach and engagement.';

// The catalogue is read for the call to action, so the page and the checkout
// can never quote a different price. Promotion runs in quote mode: it has no
// published packages, so no grid is rendered here.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/booking-marketing.php';

include_once '../header.php';
?>

<section class="page-hero pb-0">
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
					href="#choose-promotion"
					class="btn"
				>
					Book Promotion Support
				</a>

			</div>

		</div>
	</div>
</section>
<section class="section-block pb-0">

	<h2>
		About the Service
	</h2>

	<p class="section-intro">
		Our promo services help artists and brands gain traction by positioning video content in front of the right audience. Whether you are launching a new track or building a social presence, we make sure your content is seen and remembered.
	</p>

</section>

<section class="section-block pt-0">

	<h2 class="pb-3">
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

</section>
<!-- Promotion Options -->
<section class="section-block pt-0 promotion-services" id="choose-promotion">

	<h2>
		Choose Your Promotion Service
	</h2>

	<p class="section-intro">
		Whether you want to promote your own music or collaborate
		with BDC talent to promote your content, choose the option
		that best suits your needs.
	</p>

	<div class="plans-grid">

		<!-- Card 1: Audio & Video Promotion -->
		<div class="plan-card">

			<h3>Audio &amp; Video Promotion</h3>

			<p>
				Promote your audio tracks, music videos, reels,
				and other content through BDC Music's
				promotional services.
			</p>

			<ul class="feature-list">
				<li>Audio track promotion</li>
				<li>Music video and reels promotion</li>
				<li>Reach a wider audience</li>
				<li>Promotion support tailored to your project</li>
			</ul>

			<a
				class="btn btn-book"
				href="<?php echo booking_esc( booking_preselect_url( 'promotion' ) ); ?>"
			>
				Apply for Promotion
			</a>

		</div>

		<!-- Card 2: BDC Talent Promotion -->
		<div class="plan-card">

			<h3>Promote Through BDC Talent</h3>

			<p>
				Discover BDC talent and connect with suitable
				creators or artists to promote your music,
				videos, or brand content.
			</p>

			<ul class="feature-list">
				<li>Explore available BDC talent</li>
				<li>View talent profiles</li>
				<li>Find talent for your promotion needs</li>
				<li>Apply to collaborate for promotion</li>
			</ul>

			<a
				class="btn btn-book"
				href="<?php echo $siteUrl; ?>artists/"
			>
				Explore BDC Talent
			</a>

		</div>

	</div>

</section>

<?php include_once '../footer.php'; ?>
