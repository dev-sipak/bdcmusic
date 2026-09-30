<?php
$pageTitle = 'Online & Offline Music Classes | BDC Music Studio';
$metaDescription = 'Join BDC Music Studio online and offline classes for singing, music production, instruments, and video editing with clear packages and enquiry support.';
$ogTitle = $pageTitle;
$ogDescription = $metaDescription;

/**
 * Each course's packages, keyed by the plan name, holding the plan id and the
 * amount formatted the one way the checkout formats money.
 *
 * The price table below used to be a hand-written PHP array. It is now read from
 * `service_plans`, which is the same table the checkout charges from, so the
 * price a customer reads here and the price they pay cannot drift apart.
 *
 * The feature lists stay hand-written. They are marketing copy that predates the
 * catalogue, and nine of the sixteen differ from what is in the database, so
 * swapping them would silently rewrite what this page promises.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/booking-marketing.php';

include_once '../header.php';

$marketingPdb = booking_marketing_pdb();

/**
 * The course groups, keyed by the label this page uses, mapped to the
 * `service_plans.group_key` that identifies them.
 *
 * The page's own name for a course and the catalogue's key are not derivable
 * from each other ("Instrument Courses" vs `instrument`), so the mapping is
 * stated once here rather than guessed at each use.
 */
$courseGroupKeys = [
	'Singing'            => 'singing',
	'Music Production'   => 'music-production',
	'Instrument Courses' => 'instrument',
	'Video Editing'      => 'video-editing',
];

$classServiceId = (int) booking_service( $marketingPdb, 'online-offline-classes' )['id'];
$courses         = [];

foreach ( $courseGroupKeys as $courseLabel => $groupKey ) {
	$courses[ $courseLabel ] = [];

	foreach ( booking_plans( $marketingPdb, $classServiceId, $groupKey ) as $plan ) {
		$courses[ $courseLabel ][ $plan['name'] ] = [
			'id'    => (int) $plan['id'],
			'price' => booking_money( $plan['price'] ),
		];
	}
}

$planDetails = [

	'Singing' => [

		'Basic' => [
			'Duration: 1 Month',
			'Classes: 8',
			'Mode: Online / Offline',
			'Vocal warm-up',
			'Breathing techniques',
			'Basic voice training',
			'Alankars',
			'Pitch and rhythm',
			'Beginner singing exercises',
			'Practice material',
			'Certificate',
			'WhatsApp support',
		],

		'Standard' => [
			'Duration: 3 Months',
			'Classes: 24',
			'Voice training',
			'Bollywood singing',
			'Classical basics',
			'Song practice',
			'Performance techniques',
			'Priority WhatsApp support',
		],

		'Premium' => [
			'Duration: 6 Months',
			'Classes: 48',
			'Professional vocal training',
			'Advanced techniques',
			'Semi classical',
			'Stage performance',
			'Studio recording',
			'Artist grooming',
			'Personalized practice',
			'Premium support',
		],

		'Enterprise' => [
			'Duration: 12 Months',
			'96+ classes',
			'Complete professional training',
			'Recording sessions',
			'Live performance',
			'Artist grooming',
			'Portfolio building',
			'Career guidance',
			'Dedicated mentor',
		],
	],

	'Music Production' => [

		'Basic' => [
			'DAW introduction',
			'Beat making',
			'MIDI basics',
			'Mixing',
			'Practice projects',
		],

		'Standard' => [
			'Advanced beat making',
			'Melody',
			'Chords',
			'Drum programming',
			'Recording',
			'Mastering basics',
		],

		'Premium' => [
			'Advanced mixing',
			'Mastering',
			'Sound design',
			'Vocal processing',
			'Music arrangement',
			'Portfolio projects',
		],

		'Enterprise' => [
			'Industry-level production',
			'Film music',
			'OTT music',
			'Dolby Atmos basics',
			'Music release strategy',
			'Client projects',
			'Career guidance',
			'Dedicated mentor',
		],
	],

	'Instrument Courses' => [

		'Basic' => [
			'Posture and hand positioning',
			'Scale practice',
			'Rhythm basics',
			'Beginner songs',
			'Practice routine',
		],

		'Standard' => [
			'Chords and progressions',
			'Finger exercises',
			'Notation basics',
			'Song practice',
			'Performance confidence',
		],

		'Premium' => [
			'Advanced techniques',
			'Improvisation',
			'Genre-based practice',
			'Recording readiness',
			'Personalized feedback',
		],

		'Enterprise' => [
			'Professional repertoire',
			'Stage performance',
			'Studio preparation',
			'Portfolio building',
			'Dedicated mentor',
		],
	],

	'Video Editing' => [

		'Basic' => [
			'Software introduction',
			'Timeline editing',
			'Cuts and transitions',
			'Audio sync',
			'Export settings',
		],

		'Standard' => [
			'Story flow',
			'Color correction',
			'Text and titles',
			'Reels and shorts editing',
			'Project workflow',
		],

		'Premium' => [
			'Advanced color grading',
			'Motion graphics basics',
			'Music video editing',
			'Sound design',
			'Portfolio projects',
		],

		'Enterprise' => [
			'Commercial editing workflow',
			'Multi-camera editing',
			'Brand video packaging',
			'Client projects',
			'Career guidance',
		],
	],
];

$instruments = [
	'Guitar',
	'Keyboard',
	'Piano',
	'Violin',
	'Drums',
	'Tabla',
	'Dholak',
	'Flute',
	'Bass Guitar',
	'Electric Guitar',
	'Ukulele',
	'Harmonium',
	'Saxophone',
];
?>

<section class="page-hero">

	<div class="container">

		<!-- BREADCRUMB -->
		<div class="breadcrumb">

			<a href="<?php echo $siteUrl; ?>">
				Home
			</a>

			<span>/</span>

			<a href="<?php echo $siteUrl; ?>services">
				Services
			</a>

			<span>/</span>

			<span>
				Online & Offline Classes
			</span>

		</div>

		<!-- HERO -->
		<div class="section-hero online-offline-bg">

			<div class="section-hero-content">

				<span class="heading-tag">
					ONLINE & OFFLINE CLASSES
				</span>

				<h1>
					Learn Music,
					Production &
					<span class="highlight">Creative Skills</span>
				</h1>

				<p>
					Learn from experienced trainers with flexible online
					and offline classes for singing, instruments,
					music production and video editing.
				</p>

				<div class="hero-actions">

					<a
href="#choose-package"
						class="btn"
					>
						Start Your Learning Journey
					</a>

				</div>

			</div>

		</div>

		<!-- PROGRAM SELECTION -->
		<div class="section-block">

			<h2>
				Choose Your Learning Program
			</h2>

			<p class="section-intro">
				Whether you're starting from scratch or looking to build
				professional-level skills, choose a program designed
				around your goals.
			</p>

			<div class="instrument-cloud">

				<a
					href="#singing"
				>
					Singing
				</a>

				<a
					href="#music-production"
				>
					Music Production
				</a>

				<a
					href="#instrument-courses"
				>
					Instrument Training
				</a>

				<a
					href="#video-editing"
				>
					Video Editing
				</a>

			</div>

		</div>

		<!-- PACKAGE COMPARISON -->
<div class="section-block pt-0" id="choose-package">

			<h2>
				Choose Your Package
			</h2>

			<p class="section-intro">
				Compare our professionally designed learning programs
				and choose the package that best matches your goals,
				experience and learning level.
			</p>

			<div class="comparison-wrap">

				<?php foreach ( $planDetails as $courseName => $plans ) : ?>

					<?php

					$priceKey = $courseName;

					$sectionId = strtolower(
						str_replace(
							' ',
							'-',
							$courseName
						)
					);

					?>

					<!-- COURSE CATEGORY -->
					<div
						class="category-block"
						id="<?php echo htmlspecialchars( $sectionId ); ?>"
					>

						<h3>
							<?php echo htmlspecialchars( $courseName ); ?>
						</h3>

						<?php if ( $courseName === 'Singing' ) : ?>

							<p>
								Learn professional vocal techniques,
								breathing, pitch control, rhythm and
								performance skills through structured
								online and offline classes.
							</p>

						<?php elseif ( $courseName === 'Music Production' ) : ?>

							<p>
								Master beat making, recording, mixing,
								mastering and professional music production
								workflows using industry-standard software.
							</p>

						<?php elseif ( $courseName === 'Instrument Courses' ) : ?>

							<p>
								Learn your favourite instrument through
								practical lessons designed for beginners
								and advanced musicians.
							</p>

							<div class="instruments">

								<?php foreach ( $instruments as $instrument ) : ?>

									<span>
										<?php echo htmlspecialchars( $instrument ); ?>
									</span>

								<?php endforeach; ?>

							</div>

						<?php elseif ( $courseName === 'Video Editing' ) : ?>

							<p>
								Build professional editing skills including
								storytelling, colour grading, motion graphics,
								social media content and commercial workflows.
							</p>

						<?php endif; ?>

						<!-- PLANS -->
						<div class="details-grid">

							<?php foreach ( $plans as $planName => $features ) : ?>

								<?php

								$plan         = $courses[ $priceKey ][ $planName ] ?? null;

								// A hand-written feature list can name a package the
								// catalogue no longer sells. Skip the card rather
								// than render a price that is not there.
								if ( $plan === null ) {
									continue;
								}

								$isRecommended =
									( $planName === 'Standard' );

								?>

								<div
									class="detail-card"
									<?php
									if ( $isRecommended ) {
										echo 'data-recommended="true"';
									}
									?>
								>

									<?php if ( $isRecommended ) : ?>

										<span class="most-popular">
											MOST POPULAR
										</span>

									<?php endif; ?>

									<h3>
										<?php echo htmlspecialchars( $planName ); ?>
									</h3>

									<span class="price">
										<?php
										echo htmlspecialchars( $plan['price'] );
										?>
									</span>

									<ul class="feature-list">

										<?php foreach ( $features as $feature ) : ?>

											<li>
												<?php
												echo htmlspecialchars( $feature );
												?>
											</li>

										<?php endforeach; ?>

									</ul>

									<a
										class="btn btn-book"
										href="<?php
										echo htmlspecialchars(
											booking_preselect_url(
												'online-offline-classes',
												$plan['id']
											)
										);
										?>"
									>
										Get This Package
									</a>

								</div>

							<?php endforeach; ?>

						</div>

					</div>

				<?php endforeach; ?>

			</div>

		</div>

		<!-- ASSISTANCE CTA -->
		<div class="section-block pt-0">

			<h2>
				Not Sure Which Package to Choose?
			</h2>

			<p class="section-intro">
				Tell us about your goals and preferred class mode.
				Our team can help you choose the right program
				and learning plan.
			</p>

			<div class="hero-actions text-center">

				<a
href="#choose-package"
					class="btn"
				>
					Talk to Our Team
				</a>

			</div>

		</div>

	</div>

</section>

<script src="<?php echo $assetPath; ?>js/online-offline-classes-form.js"></script>
<?php include_once '../footer.php'; ?>
