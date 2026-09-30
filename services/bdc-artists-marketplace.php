<?php
$pageTitle = 'BDC Artist Marketplace | BDC Music Studio';
$metaDescription = 'Join the BDC Artist Marketplace to showcase your talent, connect with clients, receive bookings, and grow your creative career.';
$ogTitle = 'BDC Artist Marketplace | BDC Music Studio';
$ogDescription = 'Register as an artist, get verified, receive bookings and connect with clients through the BDC Artist Marketplace.';

// The membership cards and the price they quote used to be hand-written here.
// They now come from service_plans, the same table the checkout charges from,
// so the price a customer reads is the price they pay.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/booking-marketing.php';

include_once '../header.php';
?>

<section class="page-hero pb-0" id="creator-services">

	<!-- Breadcrumb -->
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo $siteUrl; ?>">Home</a>
			<span>/</span>
			<a href="<?php echo $siteUrl; ?>all-services">Services</a>
			<span>/</span>
			<span>BDC Artist Marketplace</span>
		</div>
	</div>

	<!-- Hero -->
	<div class="container">
		<div class="section-hero reveal creator-marketplace-bg">
			<div class="section-hero-content">
				<span class="heading-tag">
					CREATOR MARKETPLACE
				</span>
				<h1>
					BDC Artist Marketplace for Creative Professionals
				</h1>
				<p>
					Join India's growing creative marketplace where artists,
					musicians, actors, filmmakers and creators connect with
					clients, receive bookings and grow their careers.
				</p>
				<a href="#choose-package" class="btn">
					Join The Marketplace
				</a>
			</div>
		</div>
	</div>

	<!-- About -->
	<section class="section-block pb-0">
		<div class="container">
			<h2>
				About BDC Artist Marketplace
			</h2>
			<p class="section-intro mb-0 reveal">
				The BDC Artist Marketplace is a professional platform that
				connects verified creative professionals with individuals,
				brands, agencies and production companies looking for talent.
				Whether you are a singer, producer, actor, musician, dancer,
				or filmmaker, you can showcase your portfolio and receive
				direct booking opportunities.
			</p>
		</div>
	</section>

	<!-- ============================================================
	 Creative Categories
	============================================================ -->

	<section class="creative-categories-section section-block">
		<div class="container">

			<h2 class="creative-categories-title">
				Creative Categories
			</h2>

			<p class="section-intro reveal is-visible">
				Explore creative opportunities across music, film and performance.
				Find your category, showcase your talent and connect with the right
				opportunities.
			</p>

			<div class="creative-categories-grid">

				<!-- Singer -->
				<article class="creative-category-card creative-category-singer">
					<span class="creative-category-icon">
						<i data-lucide="mic-vocal"></i>
					</span>

					<div class="creative-category-content">
						<h3>Singer</h3>
						<p>
							Live performances, recordings and collaborations.
						</p>
					</div>
				</article>

				<!-- Producer -->
				<article class="creative-category-card creative-category-producer">
					<span class="creative-category-icon">
						<i data-lucide="sliders-horizontal"></i>
					</span>

					<div class="creative-category-content">
						<h3>Producer</h3>
						<p>
							Beat production, mixing and music production.
						</p>
					</div>
				</article>

				<!-- Composer -->
				<article class="creative-category-card creative-category-composer">
					<span class="creative-category-icon">
						<i data-lucide="guitar"></i>
					</span>

					<div class="creative-category-content">
						<h3>Composer</h3>
						<p>
							Original music for films, albums and commercials.
						</p>
					</div>
				</article>

				<!-- Actor -->
				<article class="creative-category-card creative-category-actor">
					<span class="creative-category-icon">
						<i data-lucide="clapperboard"></i>
					</span>

					<div class="creative-category-content">
						<h3>Actor</h3>
						<p>
							Films, advertisements, web series and events.
						</p>
					</div>
				</article>

				<!-- Director -->
				<article class="creative-category-card creative-category-director">
					<span class="creative-category-icon">
						<i data-lucide="video"></i>
					</span>

					<div class="creative-category-content">
						<h3>Director</h3>
						<p>
							Creative direction for music videos and films.
						</p>
					</div>
				</article>

				<!-- Musician -->
				<article class="creative-category-card creative-category-musician">
					<span class="creative-category-icon">
						<i data-lucide="drum"></i>
					</span>

					<div class="creative-category-content">
						<h3>Musician</h3>
						<p>
							Session musicians and live performers.
						</p>
					</div>
				</article>

				<!-- Dancer -->
				<article class="creative-category-card creative-category-dancer">
					<span class="creative-category-icon">
						<i data-lucide="footprints"></i>
					</span>

					<div class="creative-category-content">
						<h3>Dancer</h3>
						<p>
							Professional choreography and live performances.
						</p>
					</div>
				</article>

				<!-- Writer -->
				<article class="creative-category-card creative-category-writer">
					<span class="creative-category-icon">
						<i data-lucide="pen-tool"></i>
					</span>

					<div class="creative-category-content">
						<h3>Writer</h3>
						<p>
							Lyrics, scripts and creative storytelling.
						</p>
					</div>
				</article>

			</div>
		</div>
	</section>



	<!-- Marketplace Process -->
	<section class="section-block marketplace-process">
		<div class="container-fluid">

			<div class="marketplace-process-header">
				<span class="marketplace-eyebrow">YOUR JOURNEY</span>

				<h2>How the Marketplace Works</h2>

				<p>
					From registration to career growth — here's how it all comes together.
				</p>
			</div>

			<div class="marketplace-timeline">

				<!-- Step 01 -->
				<div class="marketplace-step">
					<div class="marketplace-step-number">
						01
					</div>

					<div class="marketplace-step-content">
						<h3>Register</h3>
						<p>
							Create your account and set up your profile.
						</p>
					</div>
				</div>

				<!-- Step 02 -->
				<div class="marketplace-step">
					<div class="marketplace-step-number">
						02
					</div>

					<div class="marketplace-step-content">
						<h3>Upload Portfolio</h3>
						<p>
							Showcase your work, skills and experience.
						</p>
					</div>
				</div>

				<!-- Step 03 -->
				<div class="marketplace-step">
					<div class="marketplace-step-number">
						03
					</div>

					<div class="marketplace-step-content">
						<h3>Verification</h3>
						<p>
							Get verified for trust and credibility.
						</p>
					</div>
				</div>

				<!-- Step 04 -->
				<div class="marketplace-step">
					<div class="marketplace-step-number">
						04
					</div>

					<div class="marketplace-step-content">
						<h3>Receive Bookings</h3>
						<p>
							Find projects and collaborate with clients.
						</p>
					</div>
				</div>

				<!-- Step 05 -->
				<div class="marketplace-step">
					<div class="marketplace-step-number">
						05
					</div>

					<div class="marketplace-step-content">
						<h3>Complete Projects</h3>
						<p>
							Deliver your work and get paid securely.
						</p>
					</div>
				</div>

				<!-- Step 06 -->
				<div class="marketplace-step">
					<div class="marketplace-step-number">
						06
					</div>

					<div class="marketplace-step-content">
						<h3>Grow Career</h3>
						<p>
							Build your reputation, get more opportunities and grow.
						</p>
					</div>
				</div>

			</div>
		</div>
	</section>

	<!-- Membership Plans -->
	<section class="section-block" id="choose-package">
		<div class="container">

			<h2>
				Artist Membership Plans
			</h2>

			<p class="section-intro">
				Choose the membership that best matches your creative journey.
				Every plan includes a verified BDC Artist profile and access
				to marketplace opportunities.
			</p>

			<?php
			// The membership cards, rendered from service_plans.
			//
			// These cards used to read "/ Year" after the amount. The checkout
			// takes a one-time payment, so that suffix promised recurring billing
			// that does not exist. The displayed amount is now exactly the amount
			// charged, and renewal can be added properly when recurring billing
			// exists to back it.
			echo booking_plan_grid('artists-marketplace');
			?>

		</div>
	</section>

</section>

<?php
include_once '../footer.php';
?>
