<?php
/**
 * Booking catalogue — the single source of truth for every price.
 *
 * Nothing in the booking flow is allowed to take an amount from the browser.
 * The client posts identifiers only (service slug, plan group, plan id, add-on
 * ids); every price used from that point on is read back out of
 * `service_plans` through this file.
 *
 * The three functions that matter:
 *
 *   booking_services()          the six active services, for the catalogue
 *   booking_plans()             a service's packages, optionally per group
 *   booking_resolve_selection() validates a whole selection and returns the
 *                               authoritative price breakdown, or null if
 *                               anything about the selection is unacceptable
 *
 * `booking_resolve_selection()` is the security boundary. It is called on
 * every step transition, again when the booking row is created, and a third
 * time at payment verification, so a hand-crafted POST carrying a foreign
 * `plan_id` or an add-on belonging to another service is rejected rather than
 * priced.
 *
 * Requires: includes/config.php, includes/database.php (for db_connect()).
 */

if ( ! defined( 'BOOKING_CATALOG_LOADED' ) ) {
	define( 'BOOKING_CATALOG_LOADED', true );
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * Every active service, ordered as the site presents them.
 *
 * @param PDO|null $pdb Optional connection.
 * @return array List of service rows: id, slug, name, booking_mode, price_note.
 */
function booking_services( $pdb = null ) {
	$pdb = $pdb ? $pdb : db_connect();

	$stmt = $pdb->query(
		'SELECT id, slug, name, booking_mode, price_note
         FROM services
         WHERE is_active = 1
         ORDER BY id'
	);

	return $stmt->fetchAll();
}

/**
 * One service by slug.
 *
 * @param PDO      $pdb  Connection.
 * @param string   $slug services.slug.
 * @param bool     $activeOnly Require is_active = 1.
 * @return array|null
 */
function booking_service( $pdb, $slug, $activeOnly = true ) {
	$sql = 'SELECT id, slug, name, booking_mode, price_note
            FROM services
            WHERE slug = :slug';
	if ( $activeOnly ) {
		$sql .= ' AND is_active = 1';
	}
	$sql .= ' LIMIT 1';

	$stmt = $pdb->prepare( $sql );
	$stmt->execute( array( ':slug' => (string) $slug ) );
	$row = $stmt->fetch();

	return $row ? $row : null;
}

/**
 * Whether a service is priced up front or quoted by the team.
 *
 * @param array $service A row from booking_service().
 * @return bool
 */
function booking_is_quote_mode( $service ) {
	return isset( $service['booking_mode'] ) && $service['booking_mode'] === 'quote';
}

/**
 * One service by primary key.
 *
 * A package row knows its `service_id` but not the slug, and the enquiry handler
 * has only the plan the customer clicked, so it needs the service from that id
 * rather than guessing the slug.
 *
 * @param PDO  $pdb       Connection.
 * @param int  $serviceId services.id.
 * @param bool $activeOnly Require is_active = 1.
 * @return array|null
 */
function booking_service_by_id( $pdb, $serviceId, $activeOnly = true ) {
	$sql = 'SELECT id, slug, name, booking_mode, price_note
            FROM services
            WHERE id = :id';
	if ( $activeOnly ) {
		$sql .= ' AND is_active = 1';
	}
	$sql .= ' LIMIT 1';

	$stmt = $pdb->prepare( $sql );
	$stmt->execute( array( ':id' => (int) $serviceId ) );
	$row = $stmt->fetch();

	return $row ? $row : null;
}

/**
 * Whether one order for this service may carry several packages.
 *
 * An order is for one service and may list as many packages of that service as
 * the customer wants: a studio session that also needs mixing and mastering, a
 * membership that also wants the verification badge. `bookings` still records a
 * single `service_id` and `booking_items` holds one row per package, so nothing
 * downstream has to care how many lines there are.
 *
 * A quote-mode service has nothing to select, so it is never multi.
 *
 * @param array|null $service A row from booking_service().
 * @return bool
 */
function booking_service_allows_multi_plan( $service ) {
	if ( ! $service || booking_is_quote_mode( $service ) ) {
		return false;
	}

	return true;
}

/**
 * Which side of Audio & Video a package group belongs to.
 *
 * The twelve sub-services are stored as twelve `service_plans` groups and the
 * page sells them as two rate cards, so the split has to live somewhere both the
 * marketing page and the enquiry handler can read. It is derived rather than
 * asked, because the customer already answered it by picking a sub-service that
 * only exists on one side.
 *
 * The two bundle groups are in here too, which is what lets a chosen package
 * decide the Service Category rather than the two being able to disagree.
 *
 * @param string $groupKey service_plans.group_key.
 * @return string 'Audio' or 'Video'.
 */
function booking_av_group_category( $groupKey ) {
	static $audio = array( 'recording', 'music-production', 'mixing', 'mastering', 'audio-bundles' );

	return in_array( (string) $groupKey, $audio, true ) ? 'Audio' : 'Video';
}

/**
 * The Audio & Video groups that are sold as bundles and settle the category.
 *
 * A bundle is one of the four studio packages on each side, as opposed to the
 * rate-card sub-services, which are reference prices and never fix the category.
 *
 * @return array Map of service_plans.group_key to 'Audio' or 'Video'.
 */
function booking_av_bundle_groups() {
	return array( 'audio-bundles' => 'Audio', 'video-bundles' => 'Video' );
}

/**
 * The Service Category a package belongs to, or '' when it decides nothing.
 *
 * Only Audio & Video bundles answer this. Every other service returns '' and
 * keeps its original step list, and the rate-card rows return '' too so a
 * reference price is never mistaken for something that settles the category.
 *
 * @param array $plan A row from booking_service_plans() or booking_default_plan().
 * @return string 'Audio', 'Video', or ''.
 */
function booking_plan_category( array $plan ) {
	$bundles = booking_av_bundle_groups();

	return $bundles[ (string) ( $plan['group_key'] ?? '' ) ] ?? '';
}

/**
 * The package groups a service publishes.
 *
 * A service with a single flat package list returns one entry with an empty
 * key, which the checkout renders as no group selector at all. Classes returns
 * four: singing, music-production, instrument, video-editing.
 *
 * @param PDO  $pdb       Connection.
 * @param int  $serviceId services.id.
 * @return array List of array( 'key' => string, 'label' => string ).
 */
function booking_plan_groups( $pdb, $serviceId ) {
	// GROUP BY, not DISTINCT: the groups are ordered by the sort_order of their
	// first plan, and an aggregate in ORDER BY over a DISTINCT result is an error
	// under ONLY_FULL_GROUP_BY (ER_WRONG_FIELD_WITH_GROUP, 3029). group_label is
	// identical for every plan in a group, so MIN() just picks it back out.
	$stmt = $pdb->prepare(
		'SELECT group_key, MIN(group_label) AS group_label, MIN(sort_order) AS first_sort
         FROM service_plans
         WHERE service_id = :sid AND is_active = 1
         GROUP BY group_key
         ORDER BY first_sort, group_key'
	);
	$stmt->execute( array( ':sid' => (int) $serviceId ) );

	$groups = array();
	foreach ( $stmt->fetchAll() as $row ) {
		$groups[] = array(
			'key'   => (string) $row['group_key'],
			'label' => (string) ( $row['group_label'] !== null && $row['group_label'] !== ''
				? $row['group_label']
				: $row['group_key'] ),
		);
	}

	return $groups;
}

/**
 * Whether a service has more than one package group, i.e. whether the checkout
 * needs to ask which one before it can show packages.
 *
 * @param PDO  $pdb       Connection.
 * @param int  $serviceId services.id.
 * @return bool
 */
function booking_has_plan_groups( $pdb, $serviceId ) {
	return count( booking_plan_groups( $pdb, $serviceId ) ) > 1;
}

/**
 * A service's active packages.
 *
 * @param PDO   $pdb       Connection.
 * @param int   $serviceId services.id.
 * @param string $groupKey Group key, or '' for the flat list.
 * @param bool  $orderableOnly Skip plans published as reference only.
 * @return array List of plan rows with a decoded `features_list`.
 */
function booking_plans( $pdb, $serviceId, $groupKey = '', $orderableOnly = false ) {
	$sql = 'SELECT id, service_id, group_key, group_label, name, price, price_note,
                   description, features, best_for, is_default, is_enquiry, is_orderable
            FROM service_plans
            WHERE service_id = :sid AND group_key = :grp AND is_active = 1';

	if ( $orderableOnly ) {
		$sql .= ' AND is_orderable = 1';
	}

	$sql .= ' ORDER BY sort_order, id';

	$stmt = $pdb->prepare( $sql );
	$stmt->execute( array(
		':sid' => (int) $serviceId,
		':grp' => (string) $groupKey,
	) );

	return booking_decode_plan_features( $stmt->fetchAll() );
}

/**
 * Every active package a service publishes, across all of its groups.
 *
 * Used by the rate card, which needs one flat list: this is
 * booking_plans() without the group filter.
 *
 * @param PDO  $pdb       Connection.
 * @param int  $serviceId services.id.
 * @param bool $orderableOnly Skip plans published as reference only.
 * @return array List of plan rows with a decoded `features_list`.
 */
function booking_service_plans( $pdb, $serviceId, $orderableOnly = false ) {
	$sql = 'SELECT id, service_id, group_key, group_label, name, price, price_note,
                   description, features, best_for, is_default, is_enquiry, is_orderable
            FROM service_plans
            WHERE service_id = :sid AND is_active = 1';

	if ( $orderableOnly ) {
		$sql .= ' AND is_orderable = 1';
	}

	$sql .= ' ORDER BY sort_order, id';

	$stmt = $pdb->prepare( $sql );
	$stmt->execute( array( ':sid' => (int) $serviceId ) );

	return booking_decode_plan_features( $stmt->fetchAll() );
}

/**
 * One package by id, or null when it is not there.
 *
 * Used to read which side of Audio & Video a chosen package belongs to, so the
 * Service Category follows the package rather than being asked again. A plan
 * published as reference only is still returned: this is a lookup, not a sale,
 * and booking_resolve_selection() is what refuses to price one.
 *
 * @param PDO $pdb    Connection.
 * @param int  $planId service_plans.id.
 * @return array|null A plan row, or null.
 */
function booking_plan_by_id( $pdb, $planId ) {
	$stmt = $pdb->prepare(
		'SELECT id, service_id, group_key, group_label, name, price, price_note,
                description, features, best_for, is_default, is_enquiry, is_orderable
         FROM service_plans
         WHERE id = :id
         LIMIT 1'
	);
	$stmt->execute( array( ':id' => (int) $planId ) );
	$row = $stmt->fetch();

	if ( ! $row ) {
		return null;
	}

	return booking_decode_plan_features( array( $row ) )[0];
}

/**
 * The package a service opens with when no package has been chosen yet.
 *
 * Checkout no longer asks which package to buy, so a link that arrives without
 * one is given the package the catalogue already advertises as the recommended
 * one. Enquiry tiers are quoted rather than sold and cannot price an order, so
 * they are never returned here, and neither is a plan published as reference
 * only.
 *
 * @param PDO  $pdb       Connection.
 * @param int  $serviceId services.id.
 * @return array|null A plan row, or null when nothing is on sale.
 */
function booking_default_plan( $pdb, $serviceId ) {
	$stmt = $pdb->prepare(
		'SELECT id, service_id, group_key, group_label, name, price, price_note,
                description, features, best_for, is_default, is_enquiry, is_orderable
         FROM service_plans
         WHERE service_id = :sid AND is_active = 1
           AND is_enquiry = 0 AND is_orderable = 1
         ORDER BY is_default DESC, sort_order, id
         LIMIT 1'
	);
	$stmt->execute( array( ':sid' => (int) $serviceId ) );
	$row = $stmt->fetch();

	if ( ! $row ) {
		return null;
	}

	return booking_decode_plan_features( array( $row ) )[0];
}

/**
 * Decode the JSON `features` column into a plain list of strings.
 *
 * @param array $plans Raw plan rows.
 * @return array
 */
function booking_decode_plan_features( $plans ) {
	foreach ( $plans as $i => $plan ) {
		$features = json_decode( (string) ( $plan['features'] ?? '' ), true );
		$plans[ $i ]['features_list'] = is_array( $features )
			? array_values( array_filter( array_map( 'strval', $features ), 'strlen' ) )
			: array();
		$plans[ $i ]['price']      = (float) $plan['price'];
		$plans[ $i ]['group_key']  = (string) $plan['group_key'];
		$plans[ $i ]['price_note'] = (string) ( $plan['price_note'] ?? '' );
		$plans[ $i ]['is_enquiry'] = ! empty( $plan['is_enquiry'] );
		$plans[ $i ]['is_orderable'] = ! empty( $plan['is_orderable'] );
	}

	return $plans;
}

/**
 * One package by id, with its features decoded.
 *
 * A reference-only plan is still returned, so a page can read its name and
 * price, but its `is_orderable` is false and nothing may price an order with it.
 *
 * @param PDO $pdb    Connection.
 * @param int $planId service_plans.id.
 * @return array|null
 */
function booking_plan( $pdb, $planId ) {
	$stmt = $pdb->prepare(
		'SELECT id, service_id, group_key, group_label, name, price, price_note,
                description, features, is_default, is_enquiry, is_orderable
         FROM service_plans
         WHERE id = :id AND is_active = 1
         LIMIT 1'
	);
	$stmt->execute( array( ':id' => (int) $planId ) );
	$row = $stmt->fetch();

	if ( ! $row ) {
		return null;
	}

	$decoded = booking_decode_plan_features( array( $row ) );

	return $decoded[0];
}

/**
 * Validate a whole selection and produce the authoritative price breakdown.
 *
 * Returns null when any part of the selection is unacceptable, so a caller
 * cannot accidentally price something it should have rejected. Rejected cases:
 *
 *   - unknown or inactive service
 *   - a package required but none chosen
 *   - `plan_id` that does not exist, is inactive, or belongs to another service
 *   - `plan_id` whose group_key does not match the group being booked
 *   - `plan_id` on an enquiry tier (`is_enquiry = 1`), which is quoted and never sold
 *   - any add-on id that does not exist, is inactive, belongs to another
 *     service, or is restricted to a different package
 *
 * Quote-mode services deliberately resolve to a zero total with no package; the
 * order is created for the team to price.
 *
 * @param PDO    $pdb      Connection.
 * @param string $slug     services.slug.
 * @param string $groupKey Package group key, '' for a flat list.
 * @param int    $planId   service_plans.id, 0 when none chosen.
 * @param array  $addonIds Accepted and ignored. Add-ons are gone from the
 *                         catalogue; the slot is kept so existing positional
 *                         callers keep working.
 * @param array  $planIds  Several service_plans.id values for one order. When
 *                         supplied it wins over `$planId`, and the order may
 *                         span several groups of the same service.
 * @return array|null {
 *     @type array $service
 *     @type array|null $plan      The first line; kept for existing readers.
 *     @type array $plans          Every package line on the order.
 *     @type float $subtotal
 *     @type float $addons_total   Always 0; the NOT NULL column, never a charge.
 *     @type float $total
 *     @type string $currency
 *     @type bool   $quote_mode
 * }
 */
function booking_resolve_selection( $pdb, $slug, $groupKey, $planId, $addonIds = array(), $planIds = array() ) {
	$service = booking_service( $pdb, $slug );
	if ( ! $service ) {
		return null;
	}

	$serviceId = (int) $service['id'];
	$groupKey  = (string) $groupKey;
	$quoteMode = booking_is_quote_mode( $service );

	// The packages on this order. `plan_ids[]` is the multi-select form; a lone
	// `plan_id` is the single-package form every existing caller uses. Duplicates
	// are dropped so one package can never be charged twice in the same order.
	$requested = array();
	foreach ( (array) $planIds as $id ) {
		$requested[] = (int) $id;
	}
	if ( ! $requested && (int) $planId > 0 ) {
		$requested[] = (int) $planId;
	}
	$requested = array_values( array_unique( array_filter( $requested ) ) );

	$plans = array();

	if ( ! $quoteMode ) {
		if ( ! $requested ) {
			return null;
		}

		foreach ( $requested as $id ) {
			$plan = booking_plan( $pdb, $id );
			if ( ! $plan ) {
				return null;
			}

			// The package must belong to the service being booked, and an enquiry tier
			// is quoted, never sold, so it is refused rather than priced at its
			// placeholder.
			if ( (int) $plan['service_id'] !== $serviceId ) {
				return null;
			}

			if ( ! empty( $plan['is_enquiry'] ) ) {
				return null;
			}

			// ... and so is a plan published as a price list only. The A/V rate cards
			// stay on the service page for their prices, but nothing may buy one:
			// hiding the button is the polite half, this stops a hand-crafted
			// ?plan_id= or a posted plan_ids[] from pricing one anyway.
			if ( empty( $plan['is_orderable'] ) ) {
				return null;
			}

			// ... and, for a single-package order, to the group being booked, so a
			// Classes customer cannot price a Singing package while the UI shows the
			// Video Editing group. A multi-package order carries one group per line,
			// checked above.
			if ( count( $requested ) === 1 && (string) $plan['group_key'] !== $groupKey ) {
				return null;
			}

			$plans[] = $plan;
		}
	}

	// Every line is priced from the catalogue and summed in integer paise, so the
	// order total is the sum of its lines with no float drift in between.
	$subtotalPaise = 0;
	foreach ( $plans as $selected ) {
		$subtotalPaise += (int) round( (float) $selected['price'] * 100 );
	}

	// An order is a list of packages and nothing else. `addons_total` stays because
	// `bookings.addons_total` is NOT NULL and older orders carry a figure in it; it
	// is always 0 on anything created from here on.
	return array(
		'service'      => $service,
		'plan'         => $plans ? $plans[0] : null,
		'plans'        => $plans,
		'subtotal'     => (float) ( $subtotalPaise / 100 ),
		'addons_total' => 0.0,
		'total'        => (float) ( $subtotalPaise / 100 ),
		'total_paise'  => $subtotalPaise,
		'currency'     => 'INR',
		'quote_mode'   => $quoteMode,
	);
}

/**
 * Format an amount for display.
 *
 * The trailing ".00" is dropped for whole amounts. Every price on the site goes
 * through this one function, so a service page, the checkout summary and the
 * confirmation page all show the same figure for the same package.
 *
 * @param float|string $amount
 * @return string e.g. "Rs. 23,500" or "Rs. 199.50"
 */
function booking_money( $amount ) {
	$value = (float) $amount;

	return 'Rs. ' . ( fmod( $value, 1.0 ) === 0.0
		? number_format( $value, 0 )
		: number_format( $value, 2 ) );
}

/**
 * Convert rupees to the integer paise Razorpay expects.
 *
 * @param float $amount Rupees.
 * @return int
 */
function booking_to_paise( $amount ) {
	return (int) round( (float) $amount * 100 );
}

/**
 * Convert integer paise back to rupees.
 *
 * @param int $paise
 * @return float
 */
function booking_from_paise( $paise ) {
	// round() returns a float because the divisor is a float literal, but the cast
	// keeps the documented float contract even for whole amounts.
	return (float) round( (int) $paise / 100, 2 );
}
