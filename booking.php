<?php
/**
 * Unified booking checkout.
 *
 * One page serves all six services. The steps that apply are decided per
 * service by booking_steps(), so a quote-mode service is never shown a payment
 * step. What each step asks for comes from includes/booking-registry.php, so
 * this file contains no per-service branching beyond presentation.
 *
 * Flow: (service and package chosen on the service page) -> Details ->
 *       Contact -> Review -> Payment
 *
 * Service and package are not steps. The package is either named by the link
 * that got the customer here or is filled in from the service's default, so a
 * link that arrives without one still starts at the first question actually
 * left to ask. An order holds one package, so naming one replaces it; the

 *
 * Every POST validates, stores into the session draft, then redirects. Nothing
 * is rendered from POST input on success, so a refresh cannot double-submit.
 *
 * Requires: includes/booking-catalog.php, includes/booking-session.php,
 *           includes/booking-registry.php
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/booking-catalog.php';
require_once __DIR__ . '/includes/booking-session.php';
require_once __DIR__ . '/includes/booking-registry.php';

// The draft lives in the session, so it has to be running before any output.
// If it cannot start, say so rather than dropping the customer's progress.
if ( ! booking_ensure_session() ) {
	http_response_code( 500 );
	exit( 'Booking is temporarily unavailable. Please try again.' );
}

/**
 * Redirect and stop. Used after every successful POST so the browser lands on a
 * GET, which keeps a refresh from resubmitting.
 *
 * @param string $url
 */
function booking_redirect( $url ) {
	header( 'Location: ' . $url );
	exit;
}

/**
 * Put the service's default package into the draft.
 *
 * The customer picks the service and the package on the service page, so
 * checkout has nothing to ask them and no step that could ask it. Arriving
 * without a package therefore adopts the one the catalogue already advertises
 * as recommended, which keeps every "Book now" link working without a chooser.
 *
 * @param PDO    $pdb     Connection.
 * @param array  $service A row from booking_service().
 * @return array|null The plan that was applied, or null when nothing is on sale.
 */
function booking_apply_default_plan( $pdb, $service ) {
	$plan = booking_default_plan( $pdb, (int) $service['id'] );

	if ( ! $plan ) {
		return null;
	}

	booking_draft_set( array(
		'plan_id'  => (int) $plan['id'],
		'plan_ids' => array( (int) $plan['id'] ),
		'group'    => (string) $plan['group_key'],
	) );

	return $plan;
}

$pdb = db_connect();

$draft     = booking_draft();
$errors    = array();
$formValue = array();

// Resolve the service
//
// The service comes from the URL when arriving from a service page CTA, and
// otherwise from the draft. A service in the URL that disagrees with the draft
// means the customer switched services, so the draft is thrown away: a package
// id from one service is meaningless against another.

$requestedSlug = isset( $_GET['service'] ) ? clean_text( $_GET['service'] ) : '';
$slug          = $requestedSlug !== '' ? $requestedSlug : (string) ( $draft['service'] ?? '' );
$service       = $slug !== '' ? booking_service( $pdb, $slug ) : null;

if ( ! $service ) {
	booking_draft_reset();
	$draft = booking_draft();
	$slug  = '';
}

if ( $service && (string) ( $draft['service'] ?? '' ) !== $service['slug'] ) {
	booking_draft_reset();
	booking_draft_set( array( 'service' => $service['slug'] ) );
	$draft = booking_draft();
}

// One service, one package. Every multi-line branch is still in place
// downstream, so widening an order to several packages is a one-word change.
$allowMulti = false;

// A service page card can link straight to a chosen package. This is a
// "replace" link, not an "add" one: an order holds a single package, so naming
// one means that one is what the order gets.
$preselectIds = array();

if ( isset( $_GET['plan_ids'] ) ) {
	$preselectIds = (array) $_GET['plan_ids'];
} elseif ( isset( $_GET['plan_id'] ) ) {
	$preselectIds = array( $_GET['plan_id'] );
}

$preselectAdded = array();

if ( $service && $preselectIds ) {
	// With more than one package allowed this is "add to order" and anything already
	// chosen stays. With a single package it replaces, or the link would appear to
	// do nothing.
	$existing = $allowMulti ? booking_draft_plan_ids( $draft ) : array();

	foreach ( $preselectIds as $preselectId ) {
		$preselectId = (int) $preselectId;

		if ( $preselectId < 1 ) {
			continue;
		}

		$preselect = booking_plan( $pdb, $preselectId );

		// booking_plan() only returns active plans. The service check is what stops a
		// link naming another service's package, an enquiry tier (quoted, not sold here)
		// or a reference-only rate card from being bought.
		if ( ! $preselect
			|| (int) $preselect['service_id'] !== (int) $service['id']
			|| ! empty( $preselect['is_enquiry'] )
			|| empty( $preselect['is_orderable'] ) ) {
			continue;
		}

		if ( in_array( $preselectId, $existing, true ) ) {
			continue;
		}

		$preselectAdded[] = $preselect;
		$existing[]       = $preselectId;

		// One package per order: the first valid id is the whole of the request. A link
		// naming several is hand-crafted or stale, and taking the extras would build a
		// multi-line order out of a single-package service.
		if ( ! $allowMulti ) {
			break;
		}
	}

	if ( $preselectAdded ) {
		$merged = array_merge( $preselectAdded, booking_draft_plans( $pdb, $existing ) );

		booking_draft_set( array(
			// plan_id / group keep mirroring the first line for the readers that
			// still expect a single package (dashboards, invoices, the summary).
			'plan_id'  => (int) $merged[0]['id'],
			'plan_ids' => $existing,
			'group'    => (string) $merged[0]['group_key'],
		) );

		$draft = booking_draft();
	}
}

// No package was named, so the default one is taken. If the service has nothing
// on sale there is nothing to price or pay for, so send the customer back to the
// page listing what can actually be bought.
if ( $service && ! booking_is_quote_mode( $service ) && ! booking_draft_plan_ids( $draft ) ) {
	if ( ! booking_apply_default_plan( $pdb, $service ) ) {
		booking_redirect( booking_service_page_url( $service['slug'] ) );
	}

	$draft = booking_draft();
}

// The chosen package settles the Service Category on the Audio & Video listing,
// so it is written into the draft here. The step that would have asked the
// question is dropped by booking_steps() once this has a value, and the details
// form reads it from the draft rather than from the request, so a package and a
// category can never end up describing different jobs.
$planCategory = $service ? booking_draft_plan_category( $draft, $pdb ) : '';

if ( $planCategory && ( $draft['details']['service_category'] ?? '' ) !== $planCategory ) {
	booking_draft_set( array(
		'details' => array_merge(
			(array) ( $draft['details'] ?? array() ),
			array( 'service_category' => $planCategory )
		),
	) );
	$draft = booking_draft();
}

// Signed-in customer
//
// A signed-in customer has already given us their details, so the account values
// go into the draft and the contact step is dropped. Done before the steps are
// computed, because removing a step changes which ones are reachable.
if ( ! empty( $_SESSION['user_id'] ) && empty( $draft['contact']['email'] ) ) {
	$accountContact = booking_account_contact( $pdb, (string) $_SESSION['user_id'] );

	if ( $accountContact ) {
		booking_draft_set( array( 'contact' => $accountContact ) );
		$draft = booking_draft();
	}
}

// Work out the steps and clamp the requested one

// No service means no steps and no form: the page renders the chooser instead.
$steps = $service ? booking_steps( $pdb, $service, $draft ) : array();
$step  = isset( $_GET['step'] ) ? clean_text( $_GET['step'] ) : '';

// A retired identifier such as ?step=addons is redirected to the step that
// replaced it, so the dead parameter disappears from the URL instead of lingering.
// This runs before the step list is consulted, so a request naming a step but no
// service still drops it.
$retired = booking_retired_steps();
if ( isset( $retired[ $step ] ) ) {
	$replacement = in_array( $retired[ $step ], $steps, true ) ? $retired[ $step ] : '';

	booking_redirect(
		$replacement !== ''
			? booking_step_url( $replacement, $service['slug'] )
			: url( 'booking.php' )
	);
}

if ( ! $steps ) {
	$step = '';
} else {
	if ( ! in_array( $step, $steps, true ) ) {
		$step = $steps[0];
	}

	if ( $service ) {
		// The furthest a customer may go is the first step they have not answered yet.
		// This is what stops a hand-crafted ?step=payment from skipping earlier answers.
		$allowed = array_search( booking_furthest_reachable( $steps, $draft, $pdb, $service ), $steps, true );

		$wanted = array_search( $step, $steps, true );
		if ( $allowed === false || $wanted === false || $wanted > $allowed ) {
			$step = $steps[ $allowed === false ? 0 : $allowed ];
		}
	}
}

/**
 * The step that follows the current one, for a redirect after a valid POST.
 *
 * @param array  $steps
 * @param string $step
 * @return string
 */
function booking_next_step( array $steps, $step ) {
	$index = array_search( $step, $steps, true );
	if ( $index === false || ! isset( $steps[ $index + 1 ] ) ) {
		return $steps[0];
	}

	return $steps[ $index + 1 ];
}

// Handle a submission

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$token = isset( $_POST['csrf_token'] ) ? (string) $_POST['csrf_token'] : '';

	if ( ! verify_csrf_token( $token ) ) {
		http_response_code( 403 );
		$errors['_general'][] = 'Your session has expired. Please review the details and submit again.';
	} else {
		switch ( $step ) {

			// Category
			case 'category':
				list( $cleanCategory, $categoryErrors ) =
					booking_validate_details( $service['slug'], $_POST, array(), array(), 'category' );

				if ( $categoryErrors ) {
					$errors    = $categoryErrors;
					$formValue = $_POST;
					break;
				}

				// Merged rather than replacing, so details already given survive a return visit.
				booking_draft_set( array(
					'details' => array_merge( (array) ( $draft['details'] ?? array() ), $cleanCategory ),
				) );

				booking_redirect( booking_step_url( booking_next_step( $steps, 'category' ), $service['slug'] ) );
				break;

			// Details
			case 'details':
				// Files already in staging count as attachments: a second pass over a stored
				// draft has no $_FILES to read, so without this a document the customer already
				// sent would be reported back as missing.
				$staged = (array) ( $draft['uploads'] ?? array() );

				// The Service Category was answered on its own step, so it is not on this form.
				// Seeded from the draft rather than the request, and unconditionally, so a
				// posted value cannot contradict an answer already given.
				$posted          = $_POST;
				$answerCategory  = ! empty( $draft['details']['service_category'] );

				if ( $answerCategory ) {
					$posted['service_category'] = $draft['details']['service_category'];
				}

				list( $cleanDetails, $detailErrors, $newUploads ) =
					booking_validate_details( $service['slug'], $posted, $_FILES, $staged, 'details' );

				// A newly uploaded file replaces whatever was staged for that field. New uploads
				// are recorded even when validation fails, or a rejected submission leaves files
				// on disk with nothing pointing at them.
				foreach ( array_unique( array_column( $newUploads, 'field' ) ) as $field ) {
					$staged = booking_drop_field_uploads( $staged, $field );
				}

				booking_draft_set( array( 'uploads' => array_merge( $staged, $newUploads ) ) );
				$draft = booking_draft();

				if ( $detailErrors ) {
					$errors    = $detailErrors;
					$formValue = $posted;
					break;
				}

				// The category lives on its own step, so its value has to survive a details
				// submission that never carried it.
				$cleanDetails = array_merge( (array) ( $draft['details'] ?? array() ), $cleanDetails );

				if ( $answerCategory ) {
					$cleanDetails['service_category'] = $draft['details']['service_category'];
				}

				booking_draft_set( array( 'details' => $cleanDetails ) );

				// The next step comes from the customer's own step list rather than being named
				// here, because a signed-in customer has no contact step.
				booking_redirect( booking_step_url( booking_next_step( $steps, 'details' ), $service['slug'] ) );
				break;

			// Contact
			case 'contact':
				list( $cleanContact, $contactErrors ) = booking_validate_contact( $_POST );

				if ( $contactErrors ) {
					$errors    = $contactErrors;
					$formValue = $_POST;
					break;
				}

				booking_draft_set( array( 'contact' => $cleanContact ) );
				booking_redirect( booking_step_url( booking_next_step( $steps, 'contact' ), $service['slug'] ) );
				break;
		}
	}
}

// Values to render

$details  = (array) ( $draft['details'] ?? array() );
$contact  = (array) ( $draft['contact'] ?? array() );
$uploads  = (array) ( $draft['uploads'] ?? array() );
$quoteMode = $service ? booking_is_quote_mode( $service ) : false;

// Whether this customer is asked for contact details at all. A signed-in customer
// is not, so the review block shows their account details with no step link.
$showContactStep = $service && in_array( 'contact', $steps, true );

// Every package the customer has ticked so far, in selection order.
$selectedPlanIds = booking_draft_plan_ids( $draft );

// On a failed submission, show what the customer typed rather than the draft.
if ( ( $step === 'details' || $step === 'category' ) && $formValue ) {
	$details = array_merge( $details, array_map(
		function ( $value ) {
			return is_array( $value ) ? array_map( 'strval', $value ) : $value;
		},
		array_filter( $formValue, 'is_scalar', ARRAY_FILTER_USE_KEY )
	) );
}

if ( $step === 'contact' && $formValue ) {
	$contact = array_merge( $contact, array_intersect_key( $formValue, booking_contact_fields() ) );
}

// The priced selection, recomputed from the database on every render. Nothing on
// this page trusts a total that was calculated earlier.
$selection = null;

if ( $service && ! $quoteMode && $selectedPlanIds ) {
	$selection = booking_resolve_selection(
		$pdb,
		$service['slug'],
		(string) ( $draft['group'] ?? '' ),
		(int) ( $draft['plan_id'] ?? 0 ),
		array(),
		$selectedPlanIds
	);

	// The plan may have been deactivated or repriced since it was chosen, so fall
	// back to the default package and say so before the customer reviews it.
	if ( ! $selection ) {
		$errors['_general'][] = 'That package is no longer available. Please review the packages listed below and pick another.';

		booking_draft_set( array(
			'plan_id'  => 0,
			'plan_ids' => array(),
			'group'    => '',
		) );
		$draft           = booking_draft();
		$selectedPlanIds = array();

		if ( booking_apply_default_plan( $pdb, $service ) ) {
			$draft           = booking_draft();
			$selectedPlanIds = booking_draft_plan_ids( $draft );
			$step            = $steps[0];
			$selection       = booking_resolve_selection(
				$pdb,
				$service['slug'],
				(string) ( $draft['group'] ?? '' ),
				(int) ( $draft['plan_id'] ?? 0 ),
				array(),
				$selectedPlanIds
			);
		}
	}
}

// Presentation

$pageTitle = $service
	? 'Book ' . $service['name'] . ' · BDC Music'
	: 'Book a Service';
$metaDescription = $service
	? 'Complete your booking for ' . $service['name'] . ' with BDC Music.'
	: 'Choose a service and book with BDC Music Studio.';

include_once __DIR__ . '/header.php';
?>

<main class="booking-page">
	<div class="container">

		<?php // The step is named once, by the progress list below, and the h1 is
		// the service itself. ?>
		<h1><?php echo $service ? booking_esc( $service['name'] ) : 'Choose a service'; ?></h1>

		<p class="booking-note">
			<i data-lucide="info"></i>
			<span>Note: You can book one service at a time.</span>
		</p>

		<?php if ( $service ) : ?>
			<ol class="booking-progress">
				<?php foreach ( $steps as $index => $each ) : ?>
					<?php
					$class = $each === $step ? 'is-current'
						: ( $index < array_search( $step, $steps, true ) ? 'is-done' : '' );
					?>
					<li class="<?php echo booking_esc( $class ); ?>">
						<?php echo booking_esc( booking_step_labels()[ $each ] ?? $each ); ?>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<?php echo booking_render_error_notice( $errors ); ?>

		<?php if ( ! $service ) : ?>

			<!-- ── Step: choose a service ─────────────────────────── -->
			<div class="booking-panel">
				<h2>What do you need?</h2>
				<p class="booking-intro">
					Pick the service you want to book. You will choose a package and
					share your details on the next steps.
				</p>

				<div class="booking-plans">
					<?php foreach ( booking_services( $pdb ) as $card ) : ?>
						<a class="booking-plan" href="<?php echo booking_esc( booking_start_url( $card['slug'] ) ); ?>">
							<h4><?php echo booking_esc( $card['name'] ); ?></h4>

							<?php if ( ! empty( $card['price_note'] ) ) : ?>
								<span class="booking-plan-price"><?php echo booking_esc( $card['price_note'] ); ?></span>
							<?php endif; ?>

							<p>
								<?php echo booking_is_quote_mode( $card )
									? 'Tell us what you need and we will send a quote.'
									: 'Choose a package and book online.'; ?>
							</p>

							<span class="btn">Continue</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

		<?php else : ?>

			<div class="booking-layout">
				<div class="booking-panel">
					<?php
					// Back is the previous step in the list, or the service page when this is the
					// first step for this service.
					$stepIndex = array_search( $step, $steps, true );
					$prevStep  = $stepIndex > 0 ? $steps[ $stepIndex - 1 ] : null;
					$backUrl   = $prevStep
						? booking_step_url( $prevStep, $service['slug'] )
						: booking_service_page_url( $service['slug'] );
					?>

					<?php // ── Step: category ──────────────────────────── ?>
					<?php if ( $step === 'category' ) : ?>
						<h2>Choose a Service Category</h2>
						<p class="booking-intro">
							Tell us whether this is an
							<strong><?php echo booking_esc( $service['name'] ); ?></strong>
							audio job or a video job, and we will ask only for what that side needs.
						</p>

						<form method="post" novalidate>
							<?php echo csrf_field(); ?>
							<?php echo booking_render_fields( $service['slug'], $details, array(), $errors, 'category' ); ?>

							<div class="booking-nav">
								<a class="btn btn-dark" href="<?php echo booking_esc( $backUrl ); ?>">Back</a>
								<div class="booking-nav-right">
									<button type="submit" class="btn">Continue</button>
								</div>
							</div>
						</form>

					<?php // ── Step: details ────────────────────────────── ?>
					<?php elseif ( $step === 'details' ) : ?>
						<h2>Tell us about your project</h2>
						<p class="booking-intro">
							Tell us what you need for
							<strong><?php echo booking_esc( $service['name'] ); ?></strong>.
							Fields marked <span class="required-star">*</span> are required.
						</p>

						<?php if ( $planCategory ) : ?>
							<div class="booking-category">
								<span class="booking-category-badge">
									<i data-lucide="layers"></i>
									<span><?php echo booking_esc( $planCategory ); ?></span>
								</span>

								<p class="booking-category-note">
									Set by your
									<strong><?php echo booking_esc( (string) ( $selection['plan']['name'] ?? 'package' ) ); ?></strong>
									package — the upload and delivery options below match it.
									<?php if ( $service ) : ?>
										<a href="<?php echo booking_esc( booking_service_page_url( $service['slug'] ) ); ?>">Change package</a>
									<?php endif; ?>
								</p>
							</div>
						<?php endif; ?>

						<form method="post" enctype="multipart/form-data" novalidate>
							<?php echo csrf_field(); ?>
							<?php echo booking_render_fields( $service['slug'], $details, $uploads, $errors, 'details' ); ?>

							<?php if ( $quoteMode ) : ?>
								<div class="booking-quote-note">
									<i data-lucide="file-text"></i>
									<p>
										This service is quoted individually. There is nothing
										to pay now. Submit this and we will send you a quote.
									</p>
								</div>
							<?php endif; ?>

							<div class="booking-nav">
								<a class="btn btn-dark" href="<?php echo booking_esc( $backUrl ); ?>">Back</a>
								<div class="booking-nav-right">
									<button type="submit" class="btn">Continue</button>
								</div>
							</div>
						</form>

					<?php // ── Step: contact ────────────────────────────── ?>
					<?php elseif ( $step === 'contact' ) : ?>
						<h2>How can we reach you?</h2>
						<p class="booking-intro">We will send your confirmation and service updates here.</p>

						<form method="post" novalidate>
							<?php echo csrf_field(); ?>
							<?php echo booking_render_contact_fields( $contact, $errors ); ?>

							<div class="booking-nav">
								<a class="btn btn-dark" href="<?php echo booking_esc( $backUrl ); ?>">Back</a>
								<div class="booking-nav-right">
									<button type="submit" class="btn">Review booking</button>
								</div>
							</div>
						</form>

					<?php // ── Step: review ─────────────────────────────── ?>
					<?php elseif ( $step === 'review' ) : ?>
						<h2>Check your booking</h2>
						<p class="booking-intro">Make sure everything below is correct before you continue.</p>

						<div class="booking-review">
							<div class="booking-review-block">
								<h3>Service <a class="booking-review-edit" href="<?php echo booking_esc( $backUrl ); ?>">Change</a></h3>
								<dl class="booking-review-list">
									<dt>Service</dt>
									<dd><?php echo booking_esc( $service['name'] ); ?></dd>
									<?php if ( ! empty( $selection['plans'] ) ) : ?>
										<dt>Package<?php echo count( $selection['plans'] ) > 1 ? 's' : ''; ?></dt>
										<dd>
											<?php foreach ( $selection['plans'] as $lineIndex => $line ) : ?>
												<?php echo $lineIndex ? '<br>' : ''; ?>
												<?php echo booking_esc( $line['name'] ); ?>
												<?php if ( ! empty( $line['group_label'] ) ) : ?>
													<small>(<?php echo booking_esc( $line['group_label'] ); ?>)</small>
												<?php endif; ?>
												<small><?php echo booking_esc( booking_money( $line['price'] ) ); ?></small>
											<?php endforeach; ?>
										</dd>
									<?php endif; ?>
								</dl>
							</div>

							<div class="booking-review-block">
								<h3>Contact
									<?php if ( $showContactStep ) : ?>
										<a class="booking-review-edit"
										   href="<?php echo booking_esc( booking_step_url( 'contact', $service['slug'] ) ); ?>">Change</a>
									<?php else : ?>
										<span class="booking-review-note">From your account</span>
									<?php endif; ?>
								</h3>
								<dl class="booking-review-list">
									<dt>Name</dt>
									<dd><?php echo booking_esc( $contact['name'] ?? '' ); ?></dd>
									<dt>Email</dt>
									<dd><?php echo booking_esc( $contact['email'] ?? '' ); ?></dd>
									<dt>Phone</dt>
									<dd><?php echo booking_esc( $contact['phone'] ?? '' ); ?></dd>
									<?php if ( ! empty( $contact['whatsapp'] ) ) : ?>
										<dt>WhatsApp</dt>
										<dd><?php echo booking_esc( $contact['whatsapp'] ); ?></dd>
									<?php endif; ?>
								</dl>
							</div>

							<div class="booking-review-block">
								<h3>Your details
									<a class="booking-review-edit"
									   href="<?php echo booking_esc( booking_step_url( 'details', $service['slug'] ) ); ?>">Change</a>
								</h3>
								<dl class="booking-review-list">
									<?php if ( $details ) : ?>
										<?php foreach ( $details as $key => $value ) : ?>
											<?php
											$field = booking_field( $service['slug'], $key );
											if ( ! $field ) {
												continue;
											}
											$shown = is_array( $value ) ? implode( ', ', $value ) : (string) $value;
											?>
											<dt><?php echo booking_esc( $field['label'] ); ?></dt>
											<dd><?php echo booking_esc( $shown ); ?></dd>
										<?php endforeach; ?>
									<?php else : ?>
										<dd>No additional details provided.</dd>
									<?php endif; ?>

									<?php if ( $uploads ) : ?>
										<dt>Files</dt>
										<dd>
											<?php echo booking_esc( implode( ', ', array_column( $uploads, 'name' ) ) ); ?>
										</dd>
									<?php endif; ?>
								</dl>
							</div>
						</div>

						<?php if ( $quoteMode ) : ?>
							<div class="booking-quote-note">
								<i data-lucide="info"></i>
								<p>
									<strong>Nothing to pay today.</strong> We will review your
									requirements and send you a quote with the final price.
								</p>
							</div>

							<form method="post" action="<?php echo booking_esc( url( 'includes/booking-create.php' ) ); ?>">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="action" value="quote">
								<div class="booking-nav">
									<a class="btn btn-dark" href="<?php echo booking_esc( $backUrl ); ?>">Back</a>
									<div class="booking-nav-right">
										<button type="submit" class="btn">Submit for a quote</button>
									</div>
								</div>
							</form>
						<?php else : ?>
							<div class="booking-nav">
								<a class="btn btn-dark" href="<?php echo booking_esc( $backUrl ); ?>">Back</a>
								<div class="booking-nav-right">
									<a class="btn" href="<?php echo booking_esc( booking_step_url( 'payment', $service['slug'] ) ); ?>">
										<?php echo booking_payment_ready() ? 'Continue to payment' : 'Place your order'; ?>
									</a>
								</div>
							</div>
						<?php endif; ?>

					<?php // ── Step: payment ────────────────────────────── ?>
					<?php elseif ( $step === 'payment' ) : ?>
						<?php if ( ! $selection ) : ?>
							<h2>Payment</h2>
							<p class="booking-intro">Please choose a package before paying.</p>
						<?php else : ?>
							<?php // With no gateway configured there is nothing to pay through yet, so
							// the step becomes placing the order. ?>
							<?php $payOnline = booking_payment_ready(); ?>

							<h2><?php echo $payOnline
								? 'Pay ' . booking_esc( booking_money( $selection['total'] ) )
								: 'Place your order'; ?></h2>
							<p class="booking-intro">
								<?php if ( $payOnline ) : ?>
									You will be taken to Razorpay's secure checkout to pay
									<?php echo booking_esc( booking_money( $selection['total'] ) ); ?>.
								<?php else : ?>
									Your order for
									<strong><?php echo booking_esc( $service['name'] ); ?></strong>
									is <?php echo booking_esc( booking_money( $selection['total'] ) ); ?>.
									Place the order now and our team will contact you to take
									payment.
								<?php endif; ?>
							</p>

							<form method="post"
								  action="<?php echo booking_esc( url( 'includes/booking-create.php' ) ); ?>"
								  data-booking-payment
								  data-booking-create-url="<?php echo booking_esc( url( 'includes/booking-create.php' ) ); ?>"
								  data-booking-verify-url="<?php echo booking_esc( url( 'includes/razorpay-verify.php' ) ); ?>"
								  data-booking-thanks-url="<?php echo booking_esc( url( 'booking-thank-you.php' ) ); ?>">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="action" value="pay">

								<div class="booking-nav">
									<a class="btn btn-dark" href="<?php echo booking_esc( $backUrl ); ?>">Back</a>
									<div class="booking-nav-right">
										<button type="submit" class="btn" data-booking-pay-button>
											<?php echo $payOnline
												? 'Pay ' . booking_esc( booking_money( $selection['total'] ) )
												: 'Place order'; ?>
										</button>
									</div>
								</div>
							</form>

							<p class="booking-intro" data-booking-payment-status role="status"></p>
						<?php endif; ?>

					<?php endif; ?>
				</div>

				<?php // ── Order summary ────────────────────────────────── ?>
				<aside class="booking-aside">
					<div class="booking-summary">
						<h3>Order summary</h3>

						<dl>
							<div class="booking-summary-row">
								<dt>Service</dt>
								<dd><?php echo booking_esc( $service['name'] ); ?></dd>
							</div>

							<?php if ( ! empty( $selection['plans'] ) ) : ?>
								<div class="booking-summary-row">
									<dt>Package<?php echo count( $selection['plans'] ) > 1 ? 's' : ''; ?></dt>
									<dd>
										<?php foreach ( $selection['plans'] as $lineIndex => $line ) : ?>
											<?php echo $lineIndex ? '<br>' : ''; ?>
											<?php echo booking_esc( $line['name'] ); ?>
											<?php if ( ! empty( $line['group_label'] ) ) : ?>
												<br><small><?php echo booking_esc( $line['group_label'] ); ?> · <?php echo booking_esc( booking_money( $line['price'] ) ); ?></small>
											<?php else : ?>
												<small><?php echo booking_esc( booking_money( $line['price'] ) ); ?></small>
											<?php endif; ?>
										<?php endforeach; ?>
									</dd>
								</div>
							<?php elseif ( $quoteMode ) : ?>
								<div class="booking-summary-row">
									<dt>Package</dt>
									<dd>Quoted individually</dd>
								</div>
							<?php endif; ?>
						</dl>

						<?php if ( $quoteMode ) : ?>
							<div class="booking-summary-total">
								<span>Payable</span>
								<span>On quote</span>
							</div>
						<?php elseif ( $selection ) : ?>
							<div class="booking-summary-total">
								<span>Total</span>
								<span><?php echo booking_esc( booking_money( $selection['total'] ) ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<p>
						<a class="btn btn-dark" href="<?php echo booking_esc( booking_service_page_url( $service['slug'] ) ); ?>">
							Back to <?php echo booking_esc( $service['name'] ); ?>
						</a>
					</p>
				</aside>
			</div>



		<?php endif; ?>
	</div>
</main>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="<?php echo booking_esc( $assetPath ); ?>js/booking-checkout.js"></script>

<?php include_once __DIR__ . '/footer.php'; ?>
