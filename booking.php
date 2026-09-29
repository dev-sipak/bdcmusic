<?php
/**
 * Unified booking checkout.
 *
 * One page serves all six services. The steps that apply are decided per
 * service by booking_steps(), so a quote-mode service is never shown a package
 * or payment step, and a service with no add-ons is never shown an add-on step.
 * What each step asks for comes from includes/booking-registry.php, so this file
 * contains no per-service branching beyond presentation.
 *
 * Flow: (chosen on the service page) -> Add-ons -> Details -> Contact ->
 *       Review -> Payment
 *
 * Service and package are not steps. Both are chosen on the service page, so a
 * link that arrives without a package is given the service's default one and
 * the customer starts at the first question that is actually left to ask.
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

// The draft lives in the session, so the session has to be running before a
// single byte goes out. If it cannot start, say so rather than silently
// dropping the customer's progress at the next redirect.
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

// ─── Resolve the service ─────────────────────────────────────────
//
// The service comes from the URL when arriving from a service page CTA, and
// otherwise from the draft. A service in the URL that disagrees with the draft
// means the customer switched services, so the draft is thrown away: a package
// or add-on id from one service is meaningless against another.

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

// A service page card can link straight to a chosen package. The ids are checked
// against this service before they are trusted, so a link cannot be used to pull a
// plan from a different service into this one. This is an "add to order" link, not
// a "replace" one: anything already chosen stays on the order, which is what a
// customer clicking a second card expects to happen. Dropping a package is done
// from the block below the form, where the current order is listed.
$preselectIds = array();

if ( isset( $_GET['plan_ids'] ) ) {
    $preselectIds = (array) $_GET['plan_ids'];
} elseif ( isset( $_GET['plan_id'] ) ) {
    $preselectIds = array( $_GET['plan_id'] );
}

$preselectAdded = array();

if ( $service && $preselectIds ) {
    $existing = booking_draft_plan_ids( $draft );

    foreach ( $preselectIds as $preselectId ) {
        $preselectId = (int) $preselectId;

        if ( $preselectId < 1 ) {
            continue;
        }

        $preselect = booking_plan( $pdb, $preselectId );

        // booking_plan() only returns active plans; the service check is what
        // stops a link pointing at another service's package, an enquiry tier
        // is quoted on the service page rather than bought here, and a
        // reference-only plan (the A/V rate cards) has no buy button to arrive
        // from in the first place.
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

// No package was named, so the default one is taken. If the service has
// nothing on sale at all there is nothing to price or pay for, and the only
// honest thing to do is send the customer back to the page that lists what can
// actually be bought.
if ( $service && ! booking_is_quote_mode( $service ) && ! booking_draft_plan_ids( $draft ) ) {
    if ( ! booking_apply_default_plan( $pdb, $service ) ) {
        booking_redirect( booking_service_page_url( $service['slug'] ) );
    }

    $draft = booking_draft();
}

// ─── Work out the steps and clamp the requested one ─────────────

// An order is for one service and may list as many of that service's packages as
// the customer wants, so every package-mode service is multi-line. A quote-mode
// service has no package to pick and is never multi.
$allowMulti = booking_service_allows_multi_plan( $service );

// No service means no steps and no form: the page renders the chooser instead.
$steps = $service ? booking_steps( $pdb, $service, $draft ) : array();
$step  = isset( $_GET['step'] ) ? clean_text( $_GET['step'] ) : '';

// A retired identifier such as ?step=addons is redirected to the step that
// replaced it, so an old link still lands somewhere real and the dead
// identifier disappears from the URL instead of silently rendering another step
// underneath it. This runs before the step list is consulted because a request
// naming a step but no service would otherwise keep the dead parameter on the
// chooser page.
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
        // booking_furthest_reachable() returns the last completed step, so the
        // furthest a customer may go is the one after it. This is what stops a
        // hand-crafted ?step=payment from skipping the earlier answers.
        $furthest = booking_furthest_reachable( $steps, $draft, $pdb, $service );
        $at       = array_search( $furthest, $steps, true );
        $allowed  = $at === false ? 0 : min( $at + 1, count( $steps ) - 1 );

        $wanted = array_search( $step, $steps, true );
        if ( $wanted === false || $wanted > $allowed ) {
            $step = $steps[ $allowed ];
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

// ─── Handle a submission ─────────────────────────────────────────

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $token = isset( $_POST['csrf_token'] ) ? (string) $_POST['csrf_token'] : '';

    if ( ! verify_csrf_token( $token ) ) {
        http_response_code( 419 );
        $errors['_general'][] = 'Your session has expired. Please review the details and submit again.';
    } else {

        // The related-packages block sits below whatever step the customer is
        // on, so it is identified by its own field rather than by $step.
        $formName = isset( $_POST['form'] ) ? clean_text( $_POST['form'] ) : '';

        switch ( $formName !== '' ? $formName : $step ) {

            // ── Related packages (below the form) ──────────────
            case 'packages':
                $serviceId = (int) $service['id'];
                $posted    = isset( $_POST['plan_ids'] ) ? (array) $_POST['plan_ids'] : array();

                if ( ! $posted && isset( $_POST['plan_id'] ) ) {
                    $posted = array( $_POST['plan_id'] );
                }

                $chosen = array();

                foreach ( $posted as $planId ) {
                    $planId = (int) $planId;
                    if ( $planId < 1 || isset( $chosen[ $planId ] ) ) {
                        continue;
                    }

                    $plan = booking_plan( $pdb, $planId );

                    // booking_plan() only returns active plans, so checking the
                    // service here is what stops a plan from another service
                    // being used to price this one. An enquiry tier is quoted
                    // rather than sold, so it is refused here too, as is a
                    // reference-only plan such as the A/V rate cards: the block
                    // does not list them either, so a posted id must not slip
                    // past a hidden checkbox.
                    if ( ! $plan
                        || (int) $plan['service_id'] !== $serviceId
                        || ! empty( $plan['is_enquiry'] )
                        || empty( $plan['is_orderable'] ) ) {
                        continue;
                    }

                    $chosen[ $planId ] = $plan;
                }

                if ( ! $chosen ) {
                    $errors['_general'][] = 'Please choose at least one package.';
                    break;
                }

                // Every package-mode service is multi-line now, so this only
                // guards a quote-mode service, where there is nothing to select.
                // It refuses rather than quietly keeping one line, because a
                // silent drop is how a customer ends up short of what they asked
                // for without ever being told.
                if ( ! $allowMulti && count( $chosen ) > 1 ) {
                    $errors['_general'][] = 'This service takes one package per order. Please choose a single package.';
                    break;
                }

                $chosenIds = array_keys( $chosen );
                $first     = $chosen[ $chosenIds[0] ];

                // The group is taken from the first chosen plan row rather than
                // the form, so a stale or missing group selector can never price
                // the wrong tier.
                booking_draft_set( array(
                    'plan_id'  => (int) $first['id'],
                    'plan_ids' => $chosenIds,
                    'group'    => (string) $first['group_key'],
                ) );

                booking_redirect( booking_step_url( $step, $service['slug'] ) );
                break;

            // ── Details ─────────────────────────────────────────
            case 'details':
                list( $cleanDetails, $detailErrors, $newUploads ) =
                    booking_validate_details( $service['slug'], $_POST, $_FILES );

                // Files already moved into staging are recorded even when
                // validation fails. Otherwise a rejected submission would leave
                // the files on disk with nothing pointing at them, and they would
                // be promoted alongside the booking or never cleaned up at all.
                $staged = (array) ( $draft['uploads'] ?? array() );

                foreach ( array_unique( array_column( $newUploads, 'field' ) ) as $field ) {
                    $staged = booking_drop_field_uploads( $staged, $field );
                }

                booking_draft_set( array( 'uploads' => array_merge( $staged, $newUploads ) ) );
                $draft = booking_draft();

                if ( $detailErrors ) {
                    $errors    = $detailErrors;
                    $formValue = $_POST;
                    break;
                }

                booking_draft_set( array( 'details' => $cleanDetails ) );
                booking_redirect( booking_step_url( 'contact', $service['slug'] ) );
                break;

            // ── Contact ─────────────────────────────────────────
            case 'contact':
                list( $cleanContact, $contactErrors ) = booking_validate_contact( $_POST );

                if ( $contactErrors ) {
                    $errors    = $contactErrors;
                    $formValue = $_POST;
                    break;
                }

                booking_draft_set( array( 'contact' => $cleanContact ) );
                booking_redirect( booking_step_url( 'review', $service['slug'] ) );
                break;
        }
    }
}

// ─── Values to render ───────────────────────────────────────────

$details  = (array) ( $draft['details'] ?? array() );
$contact  = (array) ( $draft['contact'] ?? array() );
$uploads  = (array) ( $draft['uploads'] ?? array() );
$quoteMode = $service ? booking_is_quote_mode( $service ) : false;

// Every package the customer has ticked so far, in selection order.
$selectedPlanIds = booking_draft_plan_ids( $draft );

// On a failed submission, show what the customer typed rather than the draft.
if ( $step === 'details' && $formValue ) {
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

    // The plan may have been deactivated or repriced since it was chosen. Fall
    // back to the service's default package rather than quoting a total that no
    // longer holds, and say so before the customer reviews it.
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

// ─── Presentation ───────────────────────────────────────────────

$pageTitle = $service
    ? 'Book ' . $service['name'] . ' · BDC Music'
    : 'Book a Service';
$metaDescription = $service
    ? 'Complete your booking for ' . $service['name'] . ' with BDC Music.'
    : 'Choose a service and book with BDC Music Studio.';

// The blocks under the form: what else this service sells, and what else the
// studio sells. Resolved before the header goes out so every part of the page
// can read them.
// Reference-only plans (the A/V rate cards) are deliberately absent: they are a
// published price list, not things to put in an order, so listing them here
// would contradict both the service page and booking_resolve_selection().
$relatedPlans    = ( $service && ! $quoteMode ) ? booking_service_plans( $pdb, (int) $service['id'], true ) : array();
$relatedServices = array();
$relatedGroups   = array();

foreach ( $relatedPlans as $plan ) {
    if ( ! isset( $relatedGroups[ (string) $plan['group_key'] ] ) ) {
        $relatedGroups[ (string) $plan['group_key'] ] = (string) ( $plan['group_label'] !== null && $plan['group_label'] !== ''
            ? $plan['group_label']
            : $plan['group_key'] );
    }
}

foreach ( booking_services( $pdb ) as $otherService ) {
    if ( ! $service || (int) $otherService['id'] !== (int) $service['id'] ) {
        $relatedServices[] = $otherService;
    }
}

$showGroupLabel = count( $relatedGroups ) > 1;
$inputName      = $allowMulti ? 'plan_ids[]' : 'plan_id';
$inputType      = $allowMulti ? 'checkbox' : 'radio';

include_once __DIR__ . '/header.php';
?>

<main class="booking-page">
    <div class="container">

        <?php // The step is named once, by the progress list below. Repeating it
        // as "STEP n OF m" and again as the <h1> said the same thing three
        // times, so the counter is gone and the heading is the service itself. ?>
        <h1><?php echo $service ? booking_esc( $service['name'] ) : 'Choose a service'; ?></h1>

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
                    // The Back target is the previous step in the list, or the
                    // service page when this is the first step for this service.
                    $stepIndex = array_search( $step, $steps, true );
                    $prevStep  = $stepIndex > 0 ? $steps[ $stepIndex - 1 ] : null;
                    $backUrl   = $prevStep
                        ? booking_step_url( $prevStep, $service['slug'] )
                        : booking_service_page_url( $service['slug'] );
                    ?>

                    <?php // ── Step: details ────────────────────────────── ?>
                    <?php if ( $step === 'details' ) : ?>
                        <h2>Tell us about your project</h2>
                        <p class="booking-intro">
                            Tell us what you need for
                            <strong><?php echo booking_esc( $service['name'] ); ?></strong>.
                            Fields marked <span class="required-star">*</span> are required.
                        </p>

                        <form method="post" enctype="multipart/form-data" novalidate>
                            <?php echo csrf_field(); ?>
                            <?php echo booking_render_fields( $service['slug'], $details, $uploads, $errors ); ?>

                            <?php if ( $quoteMode ) : ?>
                                <div class="booking-quote-note">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                    <p>
                                        This service is quoted individually. There is nothing
                                        to pay now. Submit this and we will send you a quote.
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="booking-nav">
                                <a class="btn  btn-dark btn-dark btn-dark" href="<?php echo booking_esc( $backUrl ); ?>">Back</a>
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
                                    <a class="booking-review-edit"
                                       href="<?php echo booking_esc( booking_step_url( 'contact', $service['slug'] ) ); ?>">Change</a>
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
                                <i class="fa-solid fa-circle-info"></i>
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
                                    <a class="btn" href="<?php echo booking_esc( booking_step_url( 'payment', $service['slug'] ) ); ?>">Continue to payment</a>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php // ── Step: payment ────────────────────────────── ?>
                    <?php elseif ( $step === 'payment' ) : ?>
                        <?php if ( ! $selection ) : ?>
                            <h2>Payment</h2>
                            <p class="booking-intro">Please choose a package before paying.</p>
                        <?php else : ?>
                            <h2>Pay <?php echo booking_esc( booking_money( $selection['total'] ) ); ?></h2>
                            <p class="booking-intro">
                                You will be taken to Razorpay's secure checkout to pay
                                <?php echo booking_esc( booking_money( $selection['total'] ) ); ?>.
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
                                            Pay <?php echo booking_esc( booking_money( $selection['total'] ) ); ?>
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
                        <?php if ( $relatedPlans ) : ?>
                            <a class="booking-review-edit" href="#related-packages">Change package</a>
                        <?php endif; ?>
                    </p>
                </aside>
            </div>

            <?php
            // ── Related packages and services ────────────────────────
            // Checkout never asks which package to buy, so this is where a
            // customer who has changed their mind changes it: the same prices
            // as the service page, applied to the draft they are already part
            // way through. Everything else a studio sells is offered underneath
            // so an order can be widened without going back to the menu.
            ?>

            <?php if ( $relatedPlans ) : ?>
                <section class="section-block  booking-related" id="related-packages">
                    <h2><?php echo $allowMulti ? 'Add the services you need' : 'Change your package'; ?></h2>

                    <p class="section-intro">
                        <?php echo $allowMulti
                            ? 'The full catalogue, at the same prices as the service page. Tick everything you need; they are priced together on one order.'
                            : 'The same packages and prices you saw on the service page. Choose a different one and your order total updates straight away.'; ?>
                    </p>

                    <form method="post" action="<?php echo booking_esc( booking_step_url( $step, $service['slug'] ) ); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="form" value="packages">

                        <div class="booking-plans">
                            <?php foreach ( $relatedPlans as $plan ) : ?>
                                <?php if ( ! empty( $plan['is_enquiry'] ) ) : ?>
                                    <a class="booking-plan" href="<?php echo booking_esc( booking_service_page_url( $service['slug'] ) ); ?>">
                                        <h4><?php echo booking_esc( $plan['name'] ); ?></h4>
                                        <span class="booking-plan-price">Custom quote</span>
                                        <p>
                                            <?php echo ! empty( $plan['description'] )
                                                ? booking_esc( $plan['description'] )
                                                : 'Priced to your brief. Send the details and we will quote it.'; ?>
                                        </p>
                                        <span class="btn btn-sm btn-dark">Enquire</span>
                                    </a>
                                <?php else : ?>
                                    <label class="booking-plan">
                                        <input
                                            type="<?php echo booking_esc( $inputType ); ?>"
                                            name="<?php echo booking_esc( $inputName ); ?>"
                                            value="<?php echo (int) $plan['id']; ?>"
                                            <?php echo in_array( (int) $plan['id'], $selectedPlanIds, true ) ? 'checked' : ''; ?>
                                        >

                                        <?php if ( (int) $plan['is_default'] === 1 ) : ?>
                                            <span class="booking-plan-flag">Most popular</span>
                                        <?php endif; ?>

                                        <h4><?php echo booking_esc( $plan['name'] ); ?></h4>
                                        <span class="booking-plan-price"><?php echo booking_esc( booking_money( $plan['price'] ) . $plan['price_note'] ); ?></span>

                                        <?php if ( $showGroupLabel && ! empty( $plan['group_label'] ) ) : ?>
                                            <p><?php echo booking_esc( $plan['group_label'] ); ?></p>
                                        <?php endif; ?>

                                        <?php if ( ! empty( $plan['description'] ) ) : ?>
                                            <p><?php echo booking_esc( $plan['description'] ); ?></p>
                                        <?php endif; ?>

                                        <?php if ( ! empty( $plan['features_list'] ) ) : ?>
                                            <ul class="booking-plan-features">
                                                <?php foreach ( array_slice( $plan['features_list'], 0, 5 ) as $feature ) : ?>
                                                    <li><?php echo booking_esc( $feature ); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="btn-row">
                            <button type="submit" class="btn">Update my packages</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>

            <?php if ( $relatedServices ) : ?>
                <section class="section-block  booking-related" id="related-services">
                    <h2>You might also need</h2>

                    <p class="section-intro">
                        Every other service the studio offers, with the packages it sells.
                        Picking one starts a fresh order for that service.
                    </p>

                    <div class="booking-plans">
                        <?php foreach ( $relatedServices as $other ) : ?>
                            <a class="booking-plan" href="<?php echo booking_esc( booking_service_page_url( $other['slug'] ) ); ?>">
                                <h4><?php echo booking_esc( $other['name'] ); ?></h4>

                                <?php if ( ! empty( $other['price_note'] ) ) : ?>
                                    <span class="booking-plan-price"><?php echo booking_esc( $other['price_note'] ); ?></span>
                                <?php endif; ?>

                                <p>
                                    <?php echo booking_is_quote_mode( $other )
                                        ? 'Tell us what you need and we will send a quote.'
                                        : 'See the packages and book online.'; ?>
                                </p>

                                <span class="btn btn-sm">View packages</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</main>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="<?php echo booking_esc( $assetPath ); ?>js/booking-checkout.js"></script>

<?php include_once __DIR__ . '/footer.php'; ?>
