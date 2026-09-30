<?php
$pageTitle = 'Digital Music Distribution Services | BDC Music Studio';
$metaDescription = 'Release your music worldwide on Spotify, Apple Music, YouTube Music and major streaming platforms with BDC Music Studio.';
$ogTitle = 'Digital Music Distribution Services | BDC Music Studio';
$ogDescription = 'Distribute your songs globally with professional music release support.';

// The distribution plans and their prices used to be hand-written here. They now
// come from service_plans, the same table the checkout charges from, so the price
// a customer reads is the price they pay.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/booking-marketing.php';

include_once '../header.php';
?>

<section class="page-hero pb-0">
	<div class="container">
		<!-- Breadcrumb -->
		<div class="breadcrumb">
			<a href="<?php echo $siteUrl; ?>">Home</a>
			<span>/</span>
			<a href="<?php echo $siteUrl; ?>all-services">Services</a>
			<span>/</span>
			<span>Digital Music Distribution</span>
		</div>
		<!-- Hero -->
		<div class="section-hero music-distribution-bg reveal">
			<div class="section-hero-content">
				<span class="heading-tag"> DISTRIBUTION SERVICES </span>
				<h1> Digital Music Distribution Services for Independent Artists </h1>
				<p> Release your music globally across Spotify, Apple Music, YouTube Music, Amazon Music and other streaming platforms with complete distribution support. </p>
				<a href="#choose-package"class="btn"> Start Distribution </a>
			</div>
		</div>
		<!-- About -->
		<section class="section-block">
			<h2> About Digital Music Distribution </h2>
			<p class="section-intro"> BDC Music Studio helps independent artists distribute their music worldwide. From release preparation, metadata management, artwork, audio checks, and platform delivery, we support every step of your music release journey. </p>
		</section>
	 
	</div><!-- /.container -->
	<div class="container-fluid">
		<!-- Platforms -->
		<section class="section-block pt-0">

			<h2>Where Your Music Gets Distributed</h2>

			<p class="section-intro">
				Release your music on the world's leading streaming platforms
				and reach listeners globally.
			</p>

			<div class="platform-marquee">

				<!-- Row 1: Left to Right -->
				<div class="platform-track platform-track-right">

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/spotify.png" alt="Spotify">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/apple-music.png" alt="Apple Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/youtube-music.png" alt="YouTube Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/amazon-music.png" alt="Amazon Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/jiosaavn.png" alt="JioSaavn">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/gaana.png" alt="Gaana">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/deezer.png" alt="Deezer">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/tidal.png" alt="TIDAL">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/pandora.png" alt="Pandora">
					</div>

					<!-- Duplicate for seamless loop -->
					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/spotify.png" alt="Spotify">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/apple-music.png" alt="Apple Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/youtube-music.png" alt="YouTube Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/amazon-music.png" alt="Amazon Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/jiosaavn.png" alt="JioSaavn">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/gaana.png" alt="Gaana">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/deezer.png" alt="Deezer">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/tidal.png" alt="TIDAL">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/pandora.png" alt="Pandora">
					</div>

				</div>


				<!-- Row 2: Right to Left -->
				<div class="platform-track platform-track-left">

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/pandora.png" alt="Pandora">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/tidal.png" alt="TIDAL">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/deezer.png" alt="Deezer">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/gaana.png" alt="Gaana">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/jiosaavn.png" alt="JioSaavn">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/amazon-music.png" alt="Amazon Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/youtube-music.png" alt="YouTube Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/apple-music.png" alt="Apple Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/spotify.png" alt="Spotify">
					</div>

					<!-- Duplicate for seamless loop -->
					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/pandora.png" alt="Pandora">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/tidal.png" alt="TIDAL">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/deezer.png" alt="Deezer">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/gaana.png" alt="Gaana">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/jiosaavn.png" alt="JioSaavn">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/amazon-music.png" alt="Amazon Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/youtube-music.png" alt="YouTube Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/apple-music.png" alt="Apple Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/spotify.png" alt="Spotify">
					</div>

				</div>

			</div>

		</section>
	</div><!-- /.container-fluid -->
	<div class="container">
		
		<!-- Pricing -->
		<section class="section-block pt-0" id="choose-package">
			<h2> Distribution Plans </h2>
			<?php
			// The four distribution plans, rendered from service_plans.
			//
			// Two of these cards used to read "/ Year" after the amount. The
			// checkout takes a one-time payment, so that suffix promised
			// recurring billing that does not exist. The displayed amount is
			// now exactly the amount charged.
			echo booking_plan_grid( 'digital-distribution' );
			?>
		</section>
	</div>
</section>
<script src="<?php echo $assetPath; ?>js/distribution-form.js"></script>
<?php
include_once '../footer.php';
?>
