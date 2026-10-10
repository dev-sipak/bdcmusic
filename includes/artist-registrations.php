<?php
/**
 * BDC Marketplace artist registrations.
 *
 * A registration is what an artist gets in exchange for buying a BDC Marketplace
 * package: one row per marketplace order, created when the order is created, and
 * left at status `pending` until the studio has looked at it. Verifying a
 * registration assigns the artist a code and generates their ID Card and
 * Certificate; rejecting it records why, so the artist can be told.
 *
 * This file holds the data model and the labels. The documents themselves are
 * in includes/artist-documents.php, because they are only needed by the
 * verification endpoint and the document viewer.
 *
 * The `artists` table is a different thing and is left alone: it is the public
 * artist directory that the Artists admin page edits, and a paid-for
 * registration must not appear in it until somebody has verified it.
 */

require_once __DIR__ . '/service-fields.php';
require_once __DIR__ . '/booking-registry.php';

if ( ! defined( 'ARTIST_REGISTRATION_SLUG' ) ) {
	// The service this workflow belongs to. Declared once so the booking hook,
	// the admin queue and the customer page cannot drift apart.
	define( 'ARTIST_REGISTRATION_SLUG', 'artists-marketplace' );
}

/**
 * Human label for a registration status.
 *
 * @param string $status One of pending, verified, rejected.
 * @return string
 */
function artist_registration_status_label( $status ) {
	$labels = array(
		'pending'  => 'Pending Verification',
		'verified' => 'Verified',
		'rejected' => 'Rejected',
	);

	return $labels[ $status ] ?? ucfirst( (string) $status );
}

/**
 * Readable name for the category the artist registered under.
 *
 * The marketplace form stores the option key ('singer'), because that is what
 * the registry validates against. The documents, the admin queue and the
 * customer's own page want the label ('Singer'), so it is resolved from the
 * booking registry rather than stored twice.
 *
 * The registry is the authority here, not service_meta_fields(): the registry is
 * the form the artist actually filled in, and its option list is the complete
 * one. The display definition is only consulted as a fallback, for the case
 * where a category key is still offered there but not in the form.
 *
 * @param string $slug Stored category key.
 * @return string The label, or the key itself when it is no longer offered.
 */
function artist_registration_category_label( $slug ) {
	$slug = (string) $slug;

	if ( $slug === '' ) {
		return '';
	}

	if ( function_exists( 'booking_field' ) ) {
		$field = booking_field( ARTIST_REGISTRATION_SLUG, 'artist_category' );

		if ( isset( $field['options'][ $slug ] ) ) {
			return (string) $field['options'][ $slug ];
		}
	}

	foreach ( service_meta_fields( ARTIST_REGISTRATION_SLUG ) as $field ) {
		if ( ( $field['key'] ?? '' ) === 'artist_category' && isset( $field['options'][ $slug ] ) ) {
			return (string) $field['options'][ $slug ];
		}
	}

	return $slug;
}

/**
 * The order-time form submission held in `details`.
 *
 * @param array $row An artist_registrations row.
 * @return array
 */
function artist_registration_meta( array $row ) {
	if ( empty( $row['details'] ) ) {
		return array();
	}

	$meta = json_decode( (string) $row['details'], true );

	return is_array( $meta ) ? $meta : array();
}

/**
 * Every field the artist submitted, as label/value pairs for the admin review.
 *
 * Every submitted field is listed, including ones the registration columns do
 * not hold, so nothing the artist typed can be missed at review time.
 *
 * @param array $row An artist_registrations row.
 * @return array List of array( 'label', 'value', 'type' ).
 */
function artist_registration_detail_pairs( array $row ) {
	$pairs = service_meta_pairs( ARTIST_REGISTRATION_SLUG, artist_registration_meta( $row ) );

	// service_meta_pairs() prints the stored option key for a select. The
	// category is the one field an admin has to recognise at a glance.
	foreach ( $pairs as $index => $pair ) {
		if ( $pair['label'] === 'Category' && ! empty( $row['category_slug'] ) ) {
			$pairs[ $index ]['value'] = artist_registration_category_label( $row['category_slug'] );
		}
	}

	return $pairs;
}

/**
 * Create the pending registration for a marketplace order.
 *
 * Called from the booking flow once the order row exists, so a paid-for package
 * always has something for the studio to review. One row per order, enforced by
 * the unique key on booking_id: an artist may buy more than one package, and
 * each purchase is its own registration.
 *
 * @param PDO  $pdo
 * @param array $booking A bookings row, including booking_id, customer_*,
 *                       plan_name, price, currency and meta.
 * @return int|false The new registration id, or false when the service is not
 *                    the marketplace or the row could not be written.
 */
function artist_registration_create( PDO $pdo, array $booking ) {
	$slug = (string) ( $booking['service_slug'] ?? '' );

	if ( $slug !== ARTIST_REGISTRATION_SLUG ) {
		return false;
	}

	// Without a name there is nothing to review or print, and artist_name is
	// NOT NULL. The booking endpoint is the caller, so this is a bug rather than
	// bad user input; log it instead of failing the order.
	$name = trim( (string) ( $booking['customer_name'] ?? '' ) );

	if ( $name === '' ) {
		app_log( 'artist-registrations', 'order ' . ( $booking['booking_id'] ?? '?' ) . ' has no customer name' );
		return false;
	}

	$meta = $booking['meta'] ? json_decode( (string) $booking['meta'], true ) : array();
	$meta = is_array( $meta ) ? $meta : array();

	$email = trim( (string) ( $booking['customer_email'] ?? '' ) );

	if ( $email === '' ) {
		app_log( 'artist-registrations', 'order ' . ( $booking['booking_id'] ?? '?' ) . ' has no customer email' );
		return false;
	}

	$stmt = $pdo->prepare(
		'INSERT IGNORE INTO artist_registrations
			(booking_id, customer_id, artist_name, artist_email, artist_phone,
			 category_slug, experience, city, goal, about, portfolio_url,
			 plan_name, plan_price, currency, details, status)
		 VALUES
			(:booking_id, :customer_id, :artist_name, :artist_email, :artist_phone,
			 :category_slug, :experience, :city, :goal, :about, :portfolio_url,
			 :plan_name, :plan_price, :currency, :details, \'pending\')'
	);

	$stmt->execute( array(
		':booking_id'    => (string) $booking['booking_id'],
		':customer_id'   => $booking['customer_id'] ?: null,
		':artist_name'   => $name,
		':artist_email'  => $email,
		':artist_phone'  => $booking['customer_phone'] ?: null,
		':category_slug' => artist_registration_meta_value( $meta, 'artist_category' ),
		':experience'    => artist_registration_meta_value( $meta, 'experience' ),
		':city'          => artist_registration_meta_value( $meta, 'event_location' ),
		':goal'          => artist_registration_meta_value( $meta, 'artist_goal' ),
		':about'         => artist_registration_meta_value( $meta, 'about' ),
		':portfolio_url' => artist_registration_meta_value( $meta, 'portfolio' ),
		':plan_name'     => $booking['plan_name'] ?? null,
		':plan_price'    => (float) ( $booking['price'] ?? 0 ),
		':currency'      => (string) ( $booking['currency'] ?? 'INR' ),
		':details'       => json_encode( $meta ),
	) );

	$inserted = $stmt->rowCount();

	// INSERT IGNORE reports zero rows when the order already has a
	// registration, which is the normal result of a retry, not a failure.
	return $inserted > 0 ? (int) $pdo->lastInsertId() : false;
}

/**
 * One meta value as a trimmed string, or null when it is absent or empty.
 *
 * Kept private to this file by convention: it is only meaningful inside
 * artist_registration_create().
 *
 * @param array  $meta
 * @param string $key
 * @return string|null
 */
function artist_registration_meta_value( array $meta, $key ) {
	if ( ! isset( $meta[ $key ] ) || is_array( $meta[ $key ] ) ) {
		return null;
	}

	$value = trim( (string) $meta[ $key ] );

	return $value === '' ? null : $value;
}

/**
 * Load one registration by its id.
 *
 * @param PDO   $pdo
 * @param mixed $id
 * @return array|null
 */
function artist_registration_find( PDO $pdo, $id ) {
	$stmt = $pdo->prepare( 'SELECT * FROM artist_registrations WHERE id = :id LIMIT 1' );
	$stmt->execute( array( ':id' => (int) $id ) );

	$row = $stmt->fetch();

	return $row ?: null;
}

/**
 * Load one registration by booking id.
 *
 * @param PDO    $pdo
 * @param string $bookingId
 * @return array|null
 */
function artist_registration_by_booking( PDO $pdo, $bookingId ) {
	$stmt = $pdo->prepare( 'SELECT * FROM artist_registrations WHERE booking_id = :id LIMIT 1' );
	$stmt->execute( array( ':id' => (string) $bookingId ) );

	$row = $stmt->fetch();

	return $row ?: null;
}

/**
 * A customer's own registrations, newest first.
 *
 * @param PDO    $pdo
 * @param string $customerId
 * @return array
 */
function customer_artist_registrations( PDO $pdo, $customerId ) {
	if ( empty( $customerId ) ) {
		return array();
	}

	$stmt = $pdo->prepare(
		'SELECT id, booking_id, customer_id, artist_name, artist_email, artist_phone,
				artist_code, category_slug, experience, city, goal, about, portfolio_url,
				plan_name, plan_price, currency, status, review_note, reviewed_at,
				id_card_path, certificate_path, documents_generated_at, details,
				DATE_FORMAT(created_at, "%Y-%m-%d %H:%i") AS created_at
		 FROM artist_registrations
		 WHERE customer_id = :customer_id
		 ORDER BY created_at DESC, id DESC'
	);
	$stmt->execute( array( ':customer_id' => (string) $customerId ) );

	return $stmt->fetchAll();
}

/**
 * How many registrations are waiting for the studio.
 *
 * @param PDO $pdo
 * @return int
 */
function artist_registrations_pending_count( PDO $pdo ) {
	return (int) $pdo->query(
		"SELECT COUNT(*) FROM artist_registrations WHERE status = 'pending'"
	)->fetchColumn();
}
