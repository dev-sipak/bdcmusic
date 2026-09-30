<?php
/**
 * Service catalogue metadata shared by the admin panel and the customer dashboard.
 *
 * The database stays the source of truth for which services exist (the
 * `services` table) and for which ones a customer bought (the `bookings`
 * table). This file only describes HOW each service is presented:
 *
 *   - the label and icon used in the customer sidebar
 *   - the ordered map of `bookings.meta` keys to human labels, so the customer
 *     sees every field they filled in at order time instead of raw JSON keys
 *   - the per-service meaning of the `service_records` columns, so one generic
 *     admin table drives six differently-worded customer sections
 *   - the progress values the admin can pick from for that service
 *
 * Add a new key here when a service is added to the `services` table; no
 * database change is needed for the presentation layer.
 */

if ( ! defined( 'SERVICE_FIELDS_LOADED' ) ) {
	define( 'SERVICE_FIELDS_LOADED', true );
}

// Unlock rule
//
// A purchased service unlocks its customer-facing section once one of its
// bookings reaches a "required / completed" status. This reuses the existing
// bookings.status enum, so no schema change is involved: 'processing' and
// 'delivered' unlock the section, while 'pending', 'hold' and 'cancelled' do not.

if ( ! defined( 'SERVICE_UNLOCK_STATUSES' ) ) {
	define( 'SERVICE_UNLOCK_STATUSES', array( 'processing', 'delivered' ) );
}

/**
 * Whether a booking status unlocks the service section.
 *
 * @param string $status bookings.status value.
 * @return bool
 */
function service_status_unlocks( $status ) {
	return in_array( strtolower( (string) $status ), SERVICE_UNLOCK_STATUSES, true );
}

/**
 * All service presentation definitions, keyed by `services.slug`.
 *
 * @return array
 */
function service_definitions() {
	static $defs = null;

	if ( $defs !== null ) {
		return $defs;
	}

	$defs = array(

		// BDC Artists Marketplace
		'artists-marketplace' => array(
			'nav'   => 'Artists Marketplace',
			'icon'  => 'star',
			'blurb' => 'Your artist booking, the artist assigned to you, and the event arrangements for the date.',
			// bookings.meta keys, in display order.
			'meta'  => array(
				array( 'key' => 'artist_goal', 'label' => 'Artist Goal', 'type' => 'text' ),
				array( 'key' => 'artist_category', 'label' => 'Category', 'type' => 'text' ),
				array( 'key' => 'event_type', 'label' => 'Event Type', 'type' => 'text' ),
				array( 'key' => 'event_date', 'label' => 'Event Date', 'type' => 'date' ),
				array( 'key' => 'event_location', 'label' => 'Event Location', 'type' => 'text' ),
				array( 'key' => 'budget', 'label' => 'Budget', 'type' => 'text' ),
			),
			// Meaning of the service_records columns for this service.
			'arrangement' => array(
				'headline'     => array( 'label' => 'Assigned Artist',      'icon' => 'user' ),
				'sub_headline' => array( 'label' => 'Role / Package',       'icon' => 'tag' ),
				'location'     => array( 'label' => 'Event Venue / City',   'icon' => 'map-pin' ),
				'starts_on'    => array( 'label' => 'Event Date',           'icon' => 'calendar' ),
				'ends_on'      => array( 'label' => 'Booking Close Date',   'icon' => 'calendar' ),
			),
			'progress' => array( 'Confirmed', 'Shortlisting', 'Profile Shared', 'Booking Confirmed', 'Completed' ),
		),

		// Audio & Video Services
		'audio-video' => array(
			'nav'   => 'Audio & Video',
			'icon'  => 'film',
			'blurb' => 'Your project scope, the production schedule, and the delivery link for the finished files.',
			'meta'  => array(
				array( 'key' => 'service_category', 'label' => 'Category',         'type' => 'text' ),
				array( 'key' => 'service_type',      'label' => 'Service Type',     'type' => 'text' ),
				array( 'key' => 'budget',            'label' => 'Budget',           'type' => 'text' ),
				array( 'key' => 'deadline',          'label' => 'Deadline',         'type' => 'text' ),
				array( 'key' => 'delivery_formats',  'label' => 'Delivery Formats', 'type' => 'list' ),
			),
			'arrangement' => array(
				'headline'     => array( 'label' => 'Deliverable',        'icon' => 'package-open' ),
				'sub_headline' => array( 'label' => 'Package',            'icon' => 'tag' ),
				'location'     => array( 'label' => 'Delivery Link',      'icon' => 'cloud-download' ),
				'starts_on'    => array( 'label' => 'Expected Start',     'icon' => 'calendar' ),
				'ends_on'      => array( 'label' => 'Expected Delivery',  'icon' => 'calendar' ),
			),
			'progress' => array( 'Confirmed', 'Scheduled', 'In Production', 'Editing', 'Delivered' ),
		),

		// Online/Offline Classes
		'online-offline-classes' => array(
			'nav'   => 'Classes',
			'icon'  => 'graduation-cap',
			'blurb' => 'Your batch, faculty, class schedule, and the join link for each mode of class.',
			'meta'  => array(
				array( 'key' => 'selected_course', 'label' => 'Course',      'type' => 'text' ),
				array( 'key' => 'selected_plan',   'label' => 'Plan',        'type' => 'text' ),
				array( 'key' => 'course',          'label' => 'Course',      'type' => 'text' ),
				array( 'key' => 'level',           'label' => 'Level',       'type' => 'text' ),
				array( 'key' => 'class_mode',      'label' => 'Class Mode',  'type' => 'text' ),
				array( 'key' => 'age',             'label' => 'Age',         'type' => 'text' ),
			),
			'arrangement' => array(
				'headline'     => array( 'label' => 'Batch & Faculty',     'icon' => 'presentation' ),
				'sub_headline' => array( 'label' => 'Level',               'icon' => 'signal' ),
				'location'     => array( 'label' => 'Class Link / Venue',  'icon' => 'video' ),
				'starts_on'    => array( 'label' => 'Batch Start',         'icon' => 'calendar' ),
				'ends_on'      => array( 'label' => 'Batch End',           'icon' => 'calendar' ),
			),
			'progress' => array( 'Enrolled', 'Scheduled', 'Ongoing', 'Completed' ),
		),

		// Digital Music Distribution
// This service has its own customer page (dashboard/releases.php) because it is
// release-centric rather than order-centric. The arrangement rows below are
// still available so admin can attach a distributor reference or delivery
// dashboard link to a distribution order.
		'digital-distribution' => array(
			'nav'   => 'My Releases',
			'icon'  => 'disc',
			'blurb' => 'Your albums, singles and EPs with their tracks and live platform links.',
			'meta'  => array(
				array( 'key' => 'release_title',  'label' => 'Release Title',   'type' => 'text' ),
				array( 'key' => 'release_type',   'label' => 'Release Type',   'type' => 'text' ),
				array( 'key' => 'artist_name',    'label' => 'Artist',         'type' => 'text' ),
				array( 'key' => 'genre',          'label' => 'Genre',          'type' => 'text' ),
				array( 'key' => 'language',       'label' => 'Language',       'type' => 'text' ),
				array( 'key' => 'release_date',   'label' => 'Release Date',   'type' => 'date' ),
				array( 'key' => 'isrc',           'label' => 'ISRC',           'type' => 'text' ),
				array( 'key' => 'upc',            'label' => 'UPC',            'type' => 'text' ),
				array( 'key' => 'copyright_help', 'label' => 'Copyright Help', 'type' => 'text' ),
			),
			'arrangement' => array(
				'headline'     => array( 'label' => 'Distributor Reference', 'icon' => 'barcode' ),
				'sub_headline' => array( 'label' => 'Label / Partner',        'icon' => 'tag' ),
				'location'     => array( 'label' => 'Delivery Dashboard',    'icon' => 'chart-line' ),
				'starts_on'    => array( 'label' => 'Submission Date',       'icon' => 'calendar' ),
				'ends_on'      => array( 'label' => 'Live Date',             'icon' => 'calendar' ),
			),
			'progress' => array( 'Received', 'Metadata Review', 'Approved', 'Distributing', 'Delivered' ),
		),

		// Promotion Services
		'promotion' => array(
			'nav'   => 'Promotion',
			'icon'  => 'megaphone',
			'blurb' => 'Your campaign scope, campaign schedule, and the performance report link.',
			'meta'  => array(
				array( 'key' => 'campaign_type',    'label' => 'Campaign Type',    'type' => 'text' ),
				array( 'key' => 'target_platform',  'label' => 'Target Platform',  'type' => 'text' ),
				array( 'key' => 'budget',           'label' => 'Budget',           'type' => 'text' ),
				array( 'key' => 'release_title',    'label' => 'Release / Content','type' => 'text' ),
				array( 'key' => 'campaign_duration','label' => 'Duration',         'type' => 'text' ),
			),
			'arrangement' => array(
				'headline'     => array( 'label' => 'Campaign',             'icon' => 'megaphone' ),
				'sub_headline' => array( 'label' => 'Campaign Type',        'icon' => 'tag' ),
				'location'     => array( 'label' => 'Report / Result Link', 'icon' => 'file-text' ),
				'starts_on'    => array( 'label' => 'Campaign Start',       'icon' => 'calendar' ),
				'ends_on'      => array( 'label' => 'Campaign End',         'icon' => 'calendar' ),
			),
			'progress' => array( 'Brief Received', 'Campaign Planning', 'Campaign Live', 'Reporting', 'Completed' ),
		),

		// IPRS Services
		'iprs' => array(
			'nav'   => 'IPRS',
			'icon'  => 'copyright',
			'blurb' => 'Your IPRS application, membership type, and the registration number once issued.',
			'meta'  => array(
				array( 'key' => 'applicant_type',  'label' => 'Applicant Type',  'type' => 'text' ),
				array( 'key' => 'membership_type', 'label' => 'Membership Type', 'type' => 'text' ),
				array( 'key' => 'song_title',      'label' => 'Song / Work Title','type' => 'text' ),
				array( 'key' => 'artist_name',     'label' => 'Artist / Author',  'type' => 'text' ),
				array( 'key' => 'song_released',   'label' => 'Already Released', 'type' => 'text' ),
				array( 'key' => 'song_links',      'label' => 'Song Links',       'type' => 'text' ),
				array( 'key' => 'account_holder',  'label' => 'Account Holder',   'type' => 'text' ),
				array( 'key' => 'account_number',  'label' => 'Account Number',   'type' => 'text' ),
				array( 'key' => 'bank_name',       'label' => 'Bank Name',        'type' => 'text' ),
				array( 'key' => 'ifsc',            'label' => 'IFSC',             'type' => 'text' ),
			),
			'arrangement' => array(
				'headline'     => array( 'label' => 'Registration Number',   'icon' => 'hash' ),
				'sub_headline' => array( 'label' => 'Membership Type',      'icon' => 'contact' ),
				'location'     => array( 'label' => 'Registered Work / Link','icon' => 'link' ),
				'starts_on'    => array( 'label' => 'Applied On',           'icon' => 'calendar' ),
				'ends_on'      => array( 'label' => 'Registered On',        'icon' => 'calendar' ),
			),
			'progress' => array( 'Application Received', 'Under Review', 'Submitted to IPRS', 'Registered', 'Completed' ),
		),
	);

	return $defs;
}

/**
 * Presentation definition for a single service slug.
 *
 * @param string $slug services.slug.
 * @return array|null Null when the slug is not a known service.
 */
function service_definition( $slug ) {
	$defs = service_definitions();
	return $defs[ $slug ] ?? null;
}

/**
 * Short label for the customer sidebar.
 *
 * @param string $slug services.slug.
 * @return string Falls back to the slug itself for unknown services.
 */
function service_nav_label( $slug ) {
	$def = service_definition( $slug );
	return $def['nav'] ?? $slug;
}

/**
 * Icon class for the customer sidebar.
 *
 * @param string $slug services.slug.
 * @return string
 */
function service_nav_icon( $slug ) {
	$def = service_definition( $slug );
	return $def['icon'] ?? 'circle';
}

/**
 * Ordered map of bookings.meta keys to labels for a service.
 *
 * @param string $slug services.slug.
 * @return array List of array( 'key', 'label', 'type' ).
 */
function service_meta_fields( $slug ) {
	$def = service_definition( $slug );
	return $def['meta'] ?? array();
}

/**
 * Per-service meaning of the service_records columns.
 *
 * @param string $slug services.slug.
 * @return array Map of column => array( 'label', 'icon' ).
 */
function service_arrangement_fields( $slug ) {
	$def = service_definition( $slug );
	return $def['arrangement'] ?? array();
}

/**
 * Progress values the admin can choose from for a service.
 *
 * @param string $slug services.slug.
 * @return array
 */
function service_progress_options( $slug ) {
	$def = service_definition( $slug );
	return $def['progress'] ?? array( 'Confirmed', 'In Progress', 'Completed' );
}

/**
 * One-line description of a service, shown at the top of its customer section.
 *
 * @param string $slug services.slug.
 * @return string
 */
function service_blurb( $slug ) {
	$def = service_definition( $slug );
	return $def['blurb'] ?? '';
}

/**
 * Turn a decoded bookings.meta value into a single display string.
 *
 * `bookings.meta` is a JSON column, so a field may hold a scalar, a list of
 * strings, or null. This normalises all three into something printable.
 *
 * @param mixed $value Decoded meta value.
 * @return string Empty string when there is nothing to show.
 */
function service_meta_display( $value ) {
	if ( is_array( $value ) ) {
		$parts = array();
		foreach ( $value as $item ) {
			if ( is_array( $item ) ) {
				$item = implode( ', ', array_filter( $item, 'is_scalar' ) );
			}
			$item = trim( (string) $item );
			if ( $item !== '' ) {
				$parts[] = $item;
			}
		}
		return implode( ', ', $parts );
	}

	return trim( (string) $value );
}

/**
 * Human label for a bookings.status value.
 *
 * @param string $status Raw enum value, e.g. 'delivered'.
 * @return string
 */
function service_status_label( $status ) {
	return ucfirst( (string) $status );
}

/**
 * Decoded bookings.meta rendered as a display-ready list of label/value pairs.
 *
 * Known keys come first, in the order given by service_meta_fields(), then any
 * key the definition does not mention is appended with a title-cased label so a
 * new order form field is never silently dropped from the customer's view.
 * Keys whose value is empty are omitted.
 *
 * Shared by the customer and admin order-detail endpoints so both describe an
 * order identically.
 *
 * @param string $slug services.slug.
 * @param mixed  $meta Decoded bookings.meta (array expected).
 * @return array List of array( 'label', 'value', 'type' ).
 */
function service_meta_pairs( $slug, $meta ) {
	$meta = is_array( $meta ) ? $meta : array();
	$pairs = array();
	$known = array();

	foreach ( service_meta_fields( $slug ) as $field ) {
		$known[] = $field['key'];
		$value   = service_meta_display( $meta[ $field['key'] ] ?? '' );
		if ( $value === '' ) {
			continue;
		}
		$pairs[] = array(
			'label' => $field['label'],
			'value' => $value,
			'type'  => $field['type'] ?? 'text',
		);
	}

	foreach ( $meta as $key => $value ) {
		if ( in_array( $key, $known, true ) ) {
			continue;
		}
		$value = service_meta_display( $value );
		if ( $value === '' ) {
			continue;
		}
		$pairs[] = array(
			'label' => ucwords( str_replace( '_', ' ', (string) $key ) ),
			'value' => $value,
			'type'  => 'text',
		);
	}

	return $pairs;
}
