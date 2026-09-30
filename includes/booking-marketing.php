<?php
/**
 * Marketing-side rendering for the six service pages.
 *
 * The service pages used to hand-write their package names and prices in HTML.
 * That is the one thing this whole platform exists to remove: a price shown on
 * a page and a price charged by the checkout have to be the same number, and
 * the only way to guarantee that is to render both from `service_plans`.
 *
 * Nothing here writes to the database or makes a decision. It reads the same
 * catalogue `booking.php` charges from, so the two cannot drift.
 */

require_once __DIR__ . '/booking-catalog.php';
require_once __DIR__ . '/booking-session.php';

if ( ! function_exists( 'booking_esc' ) ) {
	require_once __DIR__ . '/booking-registry.php';
}

/**
 * A shared read-only connection for the catalogue.
 *
 * The service pages only ever read, so each one lazily gets the same handle
 * instead of every page having to remember to open its own. Passing a
 * connection in is still supported, which is what the booking flow does.
 *
 * @param PDO|null $pdb
 * @return PDO
 */
function booking_marketing_pdb( $pdb = null ) {
	if ( $pdb instanceof PDO ) {
		return $pdb;
	}

	static $shared = null;

	if ( $shared === null ) {
		$shared = db_connect();
	}

	return $shared;
}

/**
 * One package card, as a marketing visitor sees it.
 *
 * The markup and class names are the ones the existing SCSS already styles, so
 * replacing the hand-written cards with this changes no styling.
 *
 * @param array       $plan    A row from service_plans, with `features_list` decoded.
 * @param string      $slug    services.slug, for the booking link.
 * @param array       $options
 *   string price_suffix  Appended after the amount, e.g. " / Year".
 *   bool   show_book     Render the per-card booking link. Default true.
 *   string book_label    Text for the per-card link. Default "Get This Package".
 *   bool   is_default   Mark the card as the recommended one.
 *   bool   show_best_for Render the `best_for` line as a second paragraph.
 *                       Default false: a plan has a `description` already, and on
 *                       the marketplace the two read as a stutter under the
 *                       price. The column is still there for pages that want it.
 * @return string
 */
function booking_plan_card( array $plan, $slug, array $options = array() ) {
	$options += array(
		'price_suffix' => '',
		'show_book'    => true,
		'book_label'   => 'Get This Package',
		'is_default'   => false,
		'show_best_for' => false,
	);

	$price     = (float) $plan['price'];
	$features  = isset( $plan['features_list'] ) ? (array) $plan['features_list'] : array();
	$classes   = 'plan-card';
	$card      = '';

	if ( $options['is_default'] ) {
		$classes .= ' is-featured';
	}

	$card .= '<div class="' . booking_esc( $classes ) . '">';

	if ( $options['is_default'] ) {
		$card .= '<span class="plan-card-flag">Most popular</span>';
	}

	$card .= '<h3>' . booking_esc( $plan['name'] ) . '</h3>';

	// The amount comes from the database, formatted the one way the checkout
	// formats it, so a card can never advertise a price that is not charged.
	// An enquiry tier has no price to advertise, so it shows the note that says
	// so rather than "Rs. 0".
	if ( ! empty( $plan['is_enquiry'] ) ) {
		$note = (string) ( $plan['price_note'] ?? '' );
		$priceHtml = booking_esc( $note !== '' ? $note : 'Custom Quote' );
	} else {
		$priceHtml = booking_esc( booking_money( $price ) ) . booking_esc( $options['price_suffix'] );
	}

	$card .= '<span class="price">' . $priceHtml . '</span>';

	if ( ! empty( $plan['description'] ) ) {
		$card .= '<p>' . booking_esc( $plan['description'] ) . '</p>';
	}

	if ( $options['show_best_for'] && ! empty( $plan['best_for'] ) ) {
		$card .= '<p>' . booking_esc( $plan['best_for'] ) . '</p>';
	}


	if ( $features ) {
		$card .= '<ul class="feature-list">';
		foreach ( $features as $feature ) {
			$card .= '<li>' . booking_esc( $feature ) . '</li>';
		}
		$card .= '</ul>';
	}

	if ( $options['show_book'] ) {
		// An enquiry tier has no price to charge, so it never gets a "Get this
		// package" link into a checkout that would refuse it. The pages that
		// sell one render their own Enquire Now control instead.
		if ( ! empty( $plan['is_enquiry'] ) ) {
			$options['show_book'] = false;
		} else {
			$card .= '<a class="btn btn-book" href="'
				. booking_esc( booking_preselect_url( $slug, (int) $plan['id'] ) ) . '">'
				. booking_esc( $options['book_label'] ) . '</a>';
		}
	}

	$card .= '</div>';

	return $card;
}

/**
 * The full package grid for a service.
 *
 * @param string     $slug    services.slug.
 * @param PDO|null   $pdb     Optional; a shared read handle is opened if omitted.
 * @param array      $options Passed through to booking_plan_card(), plus:
 *   string class      Extra class on the grid wrapper. Default "plans-grid".
 *   string group_key  Restrict the grid to one group. null (default) renders
 *                     every group the service publishes.
 * @return string HTML, or an empty string when the service has no packages.
 */
function booking_plan_grid( $slug, $pdb = null, array $options = array() ) {
	$pdb     = booking_marketing_pdb( $pdb );
	$service = booking_service( $pdb, $slug );

	if ( ! $service ) {
		return '';
	}

	$options += array( 'class' => 'plans-grid', 'group_key' => null );

	$groups  = booking_plan_groups( $pdb, (int) $service['id'] );
	$cards   = '';

	// A service with more than one package group gets one row per group, so the
	// course picker keeps the shape the Classes page already has.
	$groupKeys = $groups ? $groups : array( array( 'key' => '' ) );

	// A page that wants one particular group gets one, and only one. Audio &
	// Video uses this to show the four bundle cards while its twelve
	// sub-services are sold through the rate card instead.
	if ( $options['group_key'] !== null ) {
		$wanted = (string) $options['group_key'];
		$groupKeys = array_values( array_filter( $groupKeys, function ( $group ) use ( $wanted ) {
			return (string) $group['key'] === $wanted;
		} ) );
	}

	// Work out which card gets the "Most popular" flag before rendering anything.
	//
	// `is_default` is the same column the checkout preselects a package from, so
	// it is read rather than reimplemented as "whichever card is first". If the
	// catalogue ever moves the default to a different plan, the highlight and the
	// preselected package move with it instead of drifting apart.
	$defaultId = 0;

	foreach ( $groupKeys as $group ) {
		foreach ( booking_plans( $pdb, (int) $service['id'], (string) $group['key'] ) as $plan ) {
			if ( ! empty( $plan['is_default'] ) ) {
				$defaultId = (int) $plan['id'];
				break 2;
			}
		}
	}

	foreach ( $groupKeys as $group ) {
		$plans = booking_plans( $pdb, (int) $service['id'], (string) $group['key'] );

		if ( ! $plans ) {
			continue;
		}

		$cards .= '<div class="' . booking_esc( $options['class'] ) . '">';

		if ( ! empty( $group['label'] ) && count( $groupKeys ) > 1 ) {
			$cards .= '<h3 class="plans-grid-title">' . booking_esc( $group['label'] ) . '</h3>';
		}

		foreach ( $plans as $plan ) {
			$cardOptions = $options;

			unset( $cardOptions['class'] );
			$cardOptions['is_default'] = ( $defaultId === (int) $plan['id'] );

			$cards .= booking_plan_card( $plan, $slug, $cardOptions );
		}

		$cards .= '</div>';
	}

	return $cards;
}

/**
 * A rate card: one row per package group, one column per tier.
 *
 * The twelve Audio & Video rows this replaced were two PHP arrays typed into
 * the service page, which is exactly how a page ends up advertising a price the
 * checkout cannot charge. Here every cell is a `service_plans` row, so the cell
 * is also the booking link for that row.
 *
 * The tiers are the `name`s of the plans in the given groups, taken in
 * first-seen order. A group that does not publish a tier gets an em dash rather
 * than shifting every later price one column left, so the table can never lie
 * about which price belongs to which tier.
 *
 * A tier marked `is_enquiry = 1` has nothing to charge, so it renders an
 * "Enquire Now" button carrying the plan id for the page's enquiry modal rather
 * than a link into a checkout that would refuse it.
 *
 * @param string   $slug      services.slug.
 * @param array    $groupKeys service_plans.group_key values, in row order.
 * @param PDO|null $pdb       Optional; a shared read handle is opened if omitted.
 * @param array    $options
 *   string table_class Extra class on the table. Default "comparison-table".
 * @return string HTML, or '' when none of the groups publish a plan.
 */
function booking_rate_card( $slug, array $groupKeys, $pdb = null, array $options = array() ) {
	$pdb     = booking_marketing_pdb( $pdb );
	$service = booking_service( $pdb, $slug );

	if ( ! $service ) {
		return '';
	}

	$options += array( 'table_class' => 'comparison-table' );

	$serviceId = (int) $service['id'];
	$labels    = array();
	foreach ( booking_plan_groups( $pdb, $serviceId ) as $group ) {
		$labels[ (string) $group['key'] ] = (string) $group['label'];
	}

	$rows  = array();
	$tiers = array();

	foreach ( $groupKeys as $key ) {
		$key   = (string) $key;
		$plans = booking_plans( $pdb, $serviceId, $key );

		if ( ! $plans ) {
			continue;
		}

		$byTier = array();
		foreach ( $plans as $plan ) {
			$tier = (string) $plan['name'];

			if ( ! in_array( $tier, $tiers, true ) ) {
				$tiers[] = $tier;
			}

			$byTier[ $tier ] = $plan;
		}

		$rows[] = array(
			'key'    => $key,
			'label'  => isset( $labels[ $key ] ) && $labels[ $key ] !== '' ? $labels[ $key ] : $key,
			'plans'  => $byTier,
		);
	}

	if ( ! $rows || ! $tiers ) {
		return '';
	}

	$html = '<div class="table-scroll"><table class="' . booking_esc( $options['table_class'] ) . '">';

	$html .= '<thead><tr><th>Service</th>';
	foreach ( $tiers as $tier ) {
		$html .= '<th>' . booking_esc( $tier ) . '</th>';
	}
	$html .= '</tr></thead><tbody>';

	foreach ( $rows as $row ) {
		$html .= '<tr><td><strong>' . booking_esc( $row['label'] ) . '</strong></td>';

		foreach ( $tiers as $tier ) {
			if ( ! isset( $row['plans'][ $tier ] ) ) {
				$html .= '<td>&mdash;</td>';
				continue;
			}

			$plan  = $row['plans'][ $tier ];
			$price = (float) $plan['price'];
			$note  = (string) $plan['price_note'];
			$label = $note !== '' ? $note : 'Custom Quote';

			// A reference-only rate card publishes its price but cannot be
			// bought, so the cell is plain text: no link into the checkout and
			// no enquiry button either. booking_resolve_selection() refuses
			// these ids as well, so this is presentation, not the only guard.
			if ( empty( $plan['is_orderable'] ) ) {
				$html .= '<td><span class="rate-card-reference">'
					. booking_esc( $plan['is_enquiry'] ? $label : booking_money( $price ) )
					. ( ! $plan['is_enquiry'] && $note !== '' ? booking_esc( ' ' . $note ) : '' )
					. '</span></td>';
				continue;
			}

			if ( ! empty( $plan['is_enquiry'] ) ) {
				$html .= '<td>'
					. '<span style="display:block;color:var(--muted);font-size:0.85rem;">'
					. booking_esc( $label )
					. '</span>'
					. '<button type="button" class="btn btn-sm btn-dark" data-plan-enquiry="'
					. booking_esc( (int) $plan['id'] )
					. '" data-plan-name="' . booking_esc( $row['label'] . ' ' . $plan['name'] ) . '">'
					. 'Enquire Now</button>'
					. '</td>';
				continue;
			}

			$html .= '<td><a class="btn btn-sm" href="'
				. booking_esc( booking_preselect_url( $slug, (int) $plan['id'] ) )
				. '" title="' . booking_esc( 'Book ' . $row['label'] . ' — ' . $plan['name'] ) . '">'
				. booking_esc( booking_money( $price ) )
				. ( $note !== '' ? booking_esc( ' ' . $note ) : '' )
				. '</a></td>';
		}

		$html .= '</tr>';
	}

	return $html . '</tbody></table></div>';
}


/**
 * The main "Book now" call to action for a service page.
 *
 * A quote-mode service says so rather than pretending there is a fixed price,
 * because there is not one.
 *
 * @param string   $slug services.slug.
 * @param PDO|null $pdb  Optional; a shared read handle is opened if omitted.
 * @param array    $options
 *   string label   Button text. Defaults per booking mode.
 *   string sub     Optional line under the button.
 *   int    plan_id Preselect a package.
 * @return string
 */
function booking_cta( $slug, $pdb = null, array $options = array() ) {
	$pdb     = booking_marketing_pdb( $pdb );
	$service = booking_service( $pdb, $slug );

	if ( ! $service ) {
		return '';
	}

	$options += array( 'label' => '', 'sub' => '', 'plan_id' => 0 );
	$isQuote = booking_is_quote_mode( $service );

	if ( $options['label'] === '' ) {
		$options['label'] = $isQuote ? 'Request a quote' : 'Book now';
	}

	if ( $options['sub'] === '' ) {
		$options['sub'] = $isQuote
			? 'Tell us what you need. We will review it and send you a quote, with nothing to pay today.'
			: 'Choose a package, share your details and pay securely online.';
	}

	$html  = '<div class="booking-cta">';

	// `.btn` is already the primary button in this design system; there is no
	// `.btn-primary` modifier to add.
	$html .= '<a class="btn btn-lg" href="'
		. booking_esc( booking_preselect_url( $slug, (int) $options['plan_id'] ) ) . '">'
		. booking_esc( $options['label'] ) . '</a>';

	if ( $options['sub'] !== '' ) {
		$html .= '<p class="booking-cta-sub">' . booking_esc( $options['sub'] ) . '</p>';
	}

	$html .= '</div>';

	return $html;
}

/**
 * The plan grid for a service that has no published prices, as a prompt.
 *
 * Only used by quote-mode services, so the visitor is not left wondering
 * whether the absence of a price is an oversight.
 *
 * @param string   $slug services.slug.
 * @param PDO|null $pdb  Optional; a shared read handle is opened if omitted.
 * @return string
 */
function booking_quote_note( $slug, $pdb = null ) {
	$pdb     = booking_marketing_pdb( $pdb );
	$service = booking_service( $pdb, $slug );

	if ( ! $service || ! booking_is_quote_mode( $service ) ) {
		return '';
	}

	return '<div class="booking-quote-note">'
		. '<p><strong>Priced per project.</strong> This service is quoted individually, so there is '
		. 'no package list to choose from. Send us your brief and we will come back to you with a '
		. 'quote that matches it.</p></div>';
}
