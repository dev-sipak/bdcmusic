<?php
/**
 * Booking draft state and step machine.
 *
 * One draft is one service engagement, not a cart: a booking row carries its
 * package lines in `booking_items`. Razorpay takes one order per payment, so
 * several bookings under one payment would make a refund ambiguous.
 *
 * Draft keys: service (slug), group (service_plans.group_key), plan_id and
 * plan_ids (selected service_plans.id values), details (registry field key =>
 * value), contact (name, email, phone, whatsapp), uploads and upload_dir
 * (staged file metadata, promoted to the booking on create).
 *
 * Requires: includes/booking-catalog.php, includes/booking-registry.php
 */

if ( ! defined( 'BOOKING_SESSION_LOADED' ) ) {
	define( 'BOOKING_SESSION_LOADED', true );
}

require_once __DIR__ . '/booking-catalog.php';
require_once __DIR__ . '/booking-registry.php';
require_once __DIR__ . '/session.php';

/**
 * Step identifiers in journey order, with their indicator labels.
 *
 * `category` is per-service: only a service declaring a field on that step
 * shows it. Service and package are named by the link that got the customer
 * here, never chosen on a step.
 */
function booking_step_labels() {
	return array(
		'category' => 'Category',
		'details'  => 'Details',
		'contact'  => 'Contact',
		'review'   => 'Review',
		'payment'  => 'Payment',
	);
}

/**
 * Make sure a session is available to hold the draft.
 *
 * The draft lives in the session, so a session that cannot be started means the
 * customer silently loses their progress at the next redirect. Callers that can
 * recover (booking.php) check this before rendering.
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

	// Same cookie settings as the logged-in session, but without forcing one:
	// this is the lazy public session and must not start on every page view.
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
 * Reads plan_ids and falls back to plan_id, so a draft written before
 * multi-select still resolves.
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
 * Ids that no longer resolve to an active plan are skipped, because a
 * deactivated package is not something a customer can be charged for.
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
 * customer switches service, because a package id from one service is
 * meaningless against another.
 */
function booking_draft_reset() {
	$draft = booking_draft();

	booking_discard_uploads( $draft );

	unset( $_SESSION['booking_draft'] );
}

/**
 * The Service Category the draft's chosen package settles, or '' when it does not.
 *
 * A package that belongs to one side of Audio & Video has already answered the
 * question, so the step asking it is dropped rather than shown with one option
 * greyed out. Every other service, and a draft with no package yet, returns '' and
 * keeps asking.
 *
 * @param array $draft The current draft.
 * @param PDO   $pdb   Connection.
 * @return string 'Audio', 'Video', or ''.
 */
function booking_draft_plan_category( array $draft, $pdb ) {
	$planId = (int) ( $draft['plan_id'] ?? 0 );

	if ( $planId < 1 ) {
		return '';
	}

	$plan = booking_plan_by_id( $pdb, $planId );

	return $plan ? booking_plan_category( $plan ) : '';
}

/**
 * The steps that actually apply to a service, in order.
 *
 * A service that sells two different jobs under one listing (Audio & Video)
 * puts the choice between them first, so the details form is never asked about
 * a job the customer has not settled on. A quote-mode service then runs details
 * -> contact -> review; a package-mode service adds payment.
 *
 * The category step disappears once a package has answered it for us, the same
 * way the contact step disappears for a signed-in customer.
 *
 * @param PDO   $pdb     Connection.
 * @param array $service A row from booking_service().
 * @param array $draft   The current draft.
 * @return array Ordered list of step identifiers.
 */
function booking_steps( $pdb, $service, $draft ) {
	$quoteMode = booking_is_quote_mode( $service );

	// A signed-in customer's contact details are already on their account, so
	// the contact step is dropped. It stays when no contact is known.
	$skipContact = ! empty( $_SESSION['user_id'] ) && ! empty( $draft['contact']['email'] );

	$steps = array();

	if ( booking_has_category_step( (string) $service['slug'] )
		&& '' === booking_draft_plan_category( $draft, $pdb ) ) {
		$steps[] = 'category';
	}

	$steps[] = 'details';

	if ( ! $skipContact ) {
		$steps[] = 'contact';
	}

	$steps[] = 'review';

	if ( ! $quoteMode ) {
		$steps[] = 'payment';
	}

	return $steps;
}

/**
 * The contact details held on a customer's account, in booking contact shape.
 *
 * @param PDO    $pdb
 * @param string $userId users.id.
 * @return array|null Null when the account or its email is missing.
 */
function booking_account_contact( $pdb, $userId ) {
	$stmt = $pdb->prepare( 'SELECT name, email, mobile FROM users WHERE id = :id LIMIT 1' );
	$stmt->execute( array( ':id' => (string) $userId ) );
	$user = $stmt->fetch();

	if ( ! $user || empty( $user['email'] ) ) {
		return null;
	}

	return array(
		'name'     => (string) $user['name'],
		'email'    => (string) $user['email'],
		'phone'    => (string) ( $user['mobile'] ?? '' ),
		'whatsapp' => '',
	);
}

/**
 * Steps that used to exist, and the step each one now belongs at.
 *
 * Anyone still holding one of these URLs from a bookmark or browser history is
 * sent to the step that replaced it, so a retired identifier never renders.
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
 * The furthest a customer may go is the first step they have not answered yet,
 * or the last step when they have answered them all. That is what stops a
 * hand-crafted ?step=payment from skipping the earlier questions.
 *
 * @param array $steps   From booking_steps().
 * @param array $draft   The current draft.
 * @param PDO   $pdb     Connection.
 * @param array $service A row from booking_service().
 * @return string
 */
function booking_furthest_reachable( array $steps, $draft, $pdb, $service ) {
	foreach ( $steps as $step ) {
		if ( ! booking_step_satisfied( $step, $draft, $pdb, $service ) ) {
			return $step;
		}
	}

	return $steps[ count( $steps ) - 1 ];
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
		case 'category':
			return ! empty( $draft['details']['service_category'] );

		case 'details':
	// Every required field on this step has to hold a value: a draft carrying
	// only an earlier step's answer is not a completed details form.

			foreach ( booking_fields_for_step( (string) $service['slug'], 'details' ) as $field ) {
				if ( empty( $field['required'] ) ) {
					continue;
				}

				$value = $draft['details'][ $field['key'] ] ?? null;

				if ( $value === null || $value === '' || $value === array() ) {
					return false;
				}
			}

			return true;

		case 'contact':
			return ! empty( $draft['contact']['email'] );

		case 'review':
		case 'payment':
	// A quote-mode service has no plan, so requiring one here would make review
	// permanently unreachable and the quote request impossible to submit.

			$hasSelection = booking_is_quote_mode( $service )
				|| count( booking_draft_plan_ids( $draft ) ) > 0;

			return $hasSelection
				&& ! empty( $draft['contact']['email'] )
				&& booking_step_satisfied( 'details', $draft, $pdb, $service );

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
 * The id is validated against the service before use, so a hand-edited URL
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

// Files arrive on the details step, long before a booking id exists. They are
// staged in a draft-scoped directory and promoted into
// data/uploads/<bookingId>/ on create, never against a booking that was not created.

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
 * The path is only unlinked when it sits inside this draft's own staging
 * directory, so a tampered record cannot delete a promoted upload.
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
