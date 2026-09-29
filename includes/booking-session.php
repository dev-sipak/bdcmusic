<?php
/**
 * Booking draft state and step machine.
 *
 * The customer's in-progress booking lives in `$_SESSION['booking_draft']`
 * while they move through the checkout. This is deliberately NOT a cart of
 * separate bookings: one booking row is one service engagement carrying one or
 * more package lines (`booking_items`) plus that service's add-ons. Razorpay
 * takes one order per payment, so N bookings per payment would make a refund
 * ambiguous, and `bookings.service_id` keeps meaning what the dashboards read
 * it as.
 *
 * Draft shape:
 *
 *   service  string  services.slug
 *   group    string  service_plans.group_key ('' for a flat package list)
 *   plan_id  int     service_plans.id of the first selected package
 *   plan_ids array   every service_plans.id on the order; plan_id mirrors the
 *                    first one so any reader that expects a single package
 *                    still sees a real one
 *   details  array   registry field key => submitted value
 *   contact  array   name, email, phone, whatsapp
 *   uploads  array   staged file metadata, promoted to the booking on create
 *   upload_dir string staging directory for the staged uploads
 *
 * Requires: includes/booking-catalog.php
 */

if ( ! defined( 'BOOKING_SESSION_LOADED' ) ) {
    define( 'BOOKING_SESSION_LOADED', true );
}

require_once __DIR__ . '/booking-catalog.php';
require_once __DIR__ . '/session.php';

/**
 * Step identifiers in journey order, with their indicator labels.
 *
 * Service, package and add-ons are deliberately absent. Service and package are
 * chosen on the service page or in the block below the form, and the former
 * "Extra Studio Hour" style extras are now ordinary packages in the catalogue,
 * so an order is a list of the things being bought and nothing else.
 */
function booking_step_labels() {
    return array(
        'details' => 'Details',
        'contact' => 'Contact',
        'review'  => 'Review',
        'payment' => 'Payment',
    );
}

/**
 * Make sure a session is available to hold the draft.
 *
 * The draft lives in the session, so if the session cannot be started the
 * customer would silently lose their progress at the next redirect. That is
 * worth an explicit failure rather than a warning nobody reads, so callers
 * that can recover (booking.php) check this before rendering.
 *
 * @return bool
 */
function booking_ensure_session() {
    if ( session_status() === PHP_SESSION_ACTIVE ) {
        return true;
    }

    // Output has already begun, so a session can no longer be started.
    if ( headers_sent() ) {
        return false;
    }

    // Same hardened cookie and strict-mode settings as the logged-in session.
    // Configured without forcing a session, because this is the lazy public
    // booking session that must not start on every page view.
    app_session_configure();

    return (bool) session_start();
}

/**
 * The current draft, with defaults applied.
 *
 * @return array
 */
function booking_draft() {
    booking_ensure_session();

    if ( ! isset( $_SESSION['booking_draft'] ) || ! is_array( $_SESSION['booking_draft'] ) ) {
        $_SESSION['booking_draft'] = array(
            'service'   => '',
            'group'     => '',
            'plan_id'   => 0,
            'plan_ids'  => array(),
            'details'   => array(),
            'contact'   => array(),
            'uploads'   => array(),
            'upload_dir' => '',
        );
    }

    return $_SESSION['booking_draft'];
}

/**
 * Every package id currently on the draft, in selection order.
 *
 * A draft saved before the multi-select existed has only `plan_id`, and one
 * saved by the new package step has both, so this reads whichever is present
 * and never returns an empty list while `plan_id` still holds a value.
 *
 * @param array $draft The current draft.
 * @return array Unique, positive service_plans.id values.
 */
function booking_draft_plan_ids( array $draft ) {
    $ids = array();

    foreach ( (array) ( $draft['plan_ids'] ?? array() ) as $id ) {
        $ids[] = (int) $id;
    }

    if ( (int) ( $draft['plan_id'] ?? 0 ) > 0 ) {
        $ids[] = (int) $draft['plan_id'];
    }

    return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * The catalogue rows for the packages on the draft, in selection order.
 *
 * The draft stores ids, not rows, so anything that needs a package's name or
 * price has to read the catalogue. Ids that no longer resolve to an active plan
 * are skipped rather than returned as holes, because a deactivated package is not
 * something a customer can be charged for.
 *
 * @param PDO   $pdb   Connection.
 * @param array $draft The current draft.
 * @return array Rows from service_plans, ordered as the ids were given.
 */
function booking_draft_plans( $pdb, array $draft ) {
    $plans = array();

    foreach ( booking_draft_plan_ids( $draft ) as $id ) {
        $plan = booking_plan( $pdb, $id );

        if ( $plan ) {
            $plans[] = $plan;
        }
    }

    return $plans;
}

/**
 * Merge values into the draft.
 *
 * @param array $patch Keys to overwrite.
 * @return array The updated draft.
 */
function booking_draft_set( array $patch ) {
    $draft = array_merge( booking_draft(), $patch );

    $_SESSION['booking_draft'] = $draft;

    return $draft;
}

/**
 * Throw the draft away. Called when a booking is created, and whenever the
 * customer switches to a different service, because a package or add-on id
 * from one service is meaningless against another.
 */
function booking_draft_reset() {
    $draft = booking_draft();

    booking_discard_uploads( $draft );

    unset( $_SESSION['booking_draft'] );
}

/**
 * The steps that actually apply to a service, in order.
 *
 * Service, package and add-ons are all chosen outside the step machine: the first
 * two on the service page or in the block below the form, the third not at all
 * (extras are ordinary packages now). So a quote-mode service runs
 * details → contact → review and drops payment, and a package-mode service adds
 * payment. The indicator is built per service rather than hard-coded, so no
 * service is ever shown a step it cannot complete.
 *
 * @param PDO   $pdb     Connection.
 * @param array $service A row from booking_service().
 * @param array $draft   The current draft.
 * @return array Ordered list of step identifiers.
 */
function booking_steps( $pdb, $service, $draft ) {
    $quoteMode = booking_is_quote_mode( $service );

    $steps = array();

    $steps[] = 'details';
    $steps[] = 'contact';
    $steps[] = 'review';

    if ( ! $quoteMode ) {
        $steps[] = 'payment';
    }

    return $steps;
}

/**
 * Steps that used to exist, and the step each one now belongs at.
 *
 * Service, package and add-ons were all removed from the journey: the service
 * and its packages are chosen before the form, and an order is nothing but the
 * list of packages being bought. Anyone still holding one of these URLs from a
 * bookmark, an old confirmation link or a browser history gets sent to the step
 * that replaced it, so the retired identifier never renders and never sits in
 * the address bar.
 *
 * @return array Map of retired step identifier => current step identifier.
 */
function booking_retired_steps() {
    return array(
        'service'  => 'details',
        'service_select' => 'details',
        'package'  => 'details',
        'packages' => 'details',
        'addons'   => 'details',
        'addon'    => 'details',
    );
}

/**
 * The step that must be answered before the given one can be reached.
 *
 * Used to stop a customer deep-linking to step 7 with a hand-crafted URL.
 *
 * @param array  $steps   From booking_steps().
 * @param string $step    The requested step.
 * @return string|null The furthest reachable step.
 */
function booking_furthest_reachable( array $steps, $draft, $pdb, $service ) {
    $reachable = $steps[0];

    foreach ( $steps as $step ) {
        if ( ! booking_step_satisfied( $step, $draft, $pdb, $service ) ) {
            break;
        }
        $reachable = $step;
    }

    return $reachable;
}

/**
 * Whether the data a step needs has been supplied.
 *
 * @param string $step    Step identifier.
 * @param array  $draft   The current draft.
 * @param PDO    $pdb     Connection.
 * @param array  $service A row from booking_service().
 * @return bool
 */
function booking_step_satisfied( $step, $draft, $pdb, $service ) {
    switch ( $step ) {
        case 'details':
            return ! empty( $draft['details'] );

        case 'contact':
            return ! empty( $draft['contact']['email'] );

        case 'review':
        case 'payment':
            // A quote-mode service has no plan, so requiring one here would make
            // review permanently unreachable for Promotion and there would be no
            // way to submit the quote request at all.
            $hasSelection = booking_is_quote_mode( $service )
                || count( booking_draft_plan_ids( $draft ) ) > 0;

            return $hasSelection
                && ! empty( $draft['details'] )
                && ! empty( $draft['contact']['email'] );

        default:
            return true;
    }
}

/**
 * URL for a step, preserving the service.
 *
 * @param string $step   Step identifier.
 * @param string $slug   services.slug.
 * @return string
 */
function booking_step_url( $step, $slug ) {
    return url( 'booking.php?step=' . rawurlencode( $step ) . '&service=' . rawurlencode( $slug ) );
}

/**
 * The URL that starts a fresh booking for a service.
 *
 * There is no step in the URL: the checkout works out its own first step, and
 * picks the service's default package when the caller did not name one.
 *
 * @param string $slug services.slug.
 * @return string
 */
function booking_start_url( $slug ) {
    return url( 'booking.php?service=' . rawurlencode( $slug ) );
}

/**
 * The URL that starts a booking with a package already chosen.
 *
 * A service page card links here, so "Book Premium" lands on the next step with
 * Premium selected instead of making the customer find it again. The id is
 * still validated against the service before it is used, so a hand-edited URL
 * cannot carry a plan from another service into this one.
 *
 * @param string $slug    services.slug.
 * @param int    $planId  service_plans.id, 0 for none.
 * @return string
 */
function booking_preselect_url( $slug, $planId = 0 ) {
    $url = booking_start_url( $slug );

    if ( (int) $planId > 0 ) {
        $url .= '&plan_id=' . (int) $planId;
    }

    return $url;
}

/**
 * Where "back to the service page" points.
 *
 * @param string $slug services.slug.
 * @return string
 */
function booking_service_page_url( $slug ) {
    $map = array(
        'artists-marketplace'    => 'services/bdc-artists-marketplace/',
        'audio-video'            => 'services/audio-video-services/',
        'online-offline-classes' => 'services/online-offline-classes/',
        'digital-distribution'   => 'services/digital-music-distribution/',
        'promotion'              => 'services/promotion-services/',
        'iprs'                   => 'services/iprs-services/',
    );

    return url( isset( $map[ $slug ] ) ? $map[ $slug ] : 'all-services' );
}

// ─── Staged uploads ──────────────────────────────────────────────
//
// Files arrive on the details step, long before a booking id exists. They are
// written to a draft-scoped staging directory and promoted into
// data/uploads/<bookingId>/ when the booking row is created. Nothing is left
// in staging, and no upload is ever recorded against a booking that was not
// created.

/**
 * The staging directory for the current draft's uploads.
 *
 * @return string Absolute path, created if needed.
 */
function booking_staging_dir() {
    $draft = booking_draft();

    if ( ! empty( $draft['upload_dir'] ) && is_dir( $draft['upload_dir'] ) ) {
        return $draft['upload_dir'];
    }

    $token = bin2hex( random_bytes( 8 ) );
    $dir   = dirname( __DIR__ ) . '/data/uploads/_draft/' . $token;

    if ( ! is_dir( $dir ) ) {
        mkdir( $dir, 0755, true );
    }

    booking_draft_set( array( 'upload_dir' => $dir ) );

    return $dir;
}

/**
 * Forget staged upload metadata, and delete the files if nothing was booked.
 *
 * @param array $draft
 */
function booking_discard_uploads( $draft ) {
    $dir = $draft['upload_dir'] ?? '';
    if ( ! $dir || ! is_dir( $dir ) ) {
        return;
    }

    // A directory that has been promoted to a real booking id must survive.
    if ( basename( dirname( $dir ) ) === '_draft' ) {
        $files = glob( rtrim( $dir, '/\\' ) . '/*' );
        if ( is_array( $files ) ) {
            foreach ( $files as $file ) {
                if ( is_file( $file ) ) {
                    @unlink( $file );
                }
            }
        }
        @rmdir( $dir );
    }
}

/**
 * Forget the staged uploads belonging to one field, deleting the files.
 *
 * Used when a customer re-uploads a field: keeping the old file as well would
 * promote both to the booking and leave the customer with a duplicate they did
 * not ask for. The path is only unlinked when it sits inside this draft's own
 * staging directory, so a tampered record cannot be used to delete a promoted
 * upload.
 *
 * @param array  $uploads Staged upload records.
 * @param string $key     Field key whose uploads should go.
 * @return array The remaining upload records.
 */
function booking_drop_field_uploads( array $uploads, $key ) {
    $staging = (string) ( booking_draft()['upload_dir'] ?? '' );
    $kept    = array();

    foreach ( $uploads as $upload ) {
        if ( ! isset( $upload['field'] ) || $upload['field'] !== $key ) {
            $kept[] = $upload;
            continue;
        }

        $path = (string) ( $upload['path'] ?? '' );

        if ( $path !== '' && $staging !== '' && strpos( $path, $staging ) === 0 && is_file( $path ) ) {
            @unlink( $path );
        }
    }

    return $kept;
}

/**
 * Delete every staged upload and clear the draft's upload fields.
 */
function booking_clear_staged_uploads() {
    $draft = booking_draft();

    booking_discard_uploads( $draft );

    booking_draft_set( array( 'uploads' => array(), 'upload_dir' => '' ) );
}
