<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	http_response_code( 405 );
	echo json_encode( [ 'success' => false, 'message' => 'Method not allowed' ] );
	exit;
}

$raw   = file_get_contents( 'php://input' );
$input = json_decode( $raw === false ? '' : $raw, true );

if ( ! is_array( $input ) ) {
	$input = $_POST;
}

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf( $input );

$id          = (int) ( $input['id'] ?? 0 );
$name        = clean_text( $input['name'] ?? '' );
$slug        = clean_text( $input['slug'] ?? '' );
$bookingMode = clean_text( $input['booking_mode'] ?? 'packages' );
$priceNote   = clean_text( $input['price_note'] ?? '' );
$isActive    = ! empty( $input['is_active'] ) ? 1 : 0;
$isUpdate    = $id > 0;

if ( $name === '' ) {
	echo json_encode( [ 'success' => false, 'message' => 'A service name is required.' ] );
	exit;
}

if ( strlen( $name ) > 100 ) {
	echo json_encode( [ 'success' => false, 'message' => 'The service name is longer than 100 characters.' ] );
	exit;
}

if ( $priceNote !== '' && strlen( $priceNote ) > 120 ) {
	echo json_encode( [ 'success' => false, 'message' => 'The price note is longer than 120 characters.' ] );
	exit;
}

if ( ! in_array( $bookingMode, [ 'packages', 'quote' ], true ) ) {
	echo json_encode( [ 'success' => false, 'message' => 'Booking mode must be "packages" or "quote".' ] );
	exit;
}

// The slug drives the service page URL and the checkout deep links, so it is
// slugified rather than taken raw and is only regenerated when the caller did
// not supply one.
if ( $slug === '' ) {
	$slug = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $name ) );
	$slug = trim( $slug, '-' );
}

if ( $slug === '' ) {
	echo json_encode( [ 'success' => false, 'message' => 'The service slug could not be generated from that name.' ] );
	exit;
}

if ( strlen( $slug ) > 50 || ! preg_match( '/^[a-z0-9-]+$/', $slug ) ) {
	echo json_encode( [ 'success' => false, 'message' => 'The slug may only contain lowercase letters, numbers, and hyphens.' ] );
	exit;
}

try {
	$dupe = $pdo->prepare( 'SELECT id FROM services WHERE slug = :slug AND id <> :id LIMIT 1' );
	$dupe->execute( [ ':slug' => $slug, ':id' => $id ] );

	if ( $dupe->fetch() ) {
		echo json_encode( [ 'success' => false, 'message' => 'Another service already uses that slug.' ] );
		exit;
	}

	if ( $isUpdate ) {
		$stmt = $pdo->prepare(
			'UPDATE services SET name = :name, slug = :slug, booking_mode = :mode,
                    price_note = :priceNote, is_active = :isActive
              WHERE id = :id'
		);
		$stmt->execute( [
			':name'      => $name,
			':slug'      => $slug,
			':mode'      => $bookingMode,
			':priceNote' => $priceNote !== '' ? $priceNote : null,
			':isActive'  => $isActive,
			':id'        => $id,
		] );

		if ( $stmt->rowCount() === 0 ) {
			$exists = $pdo->prepare( 'SELECT id FROM services WHERE id = :id LIMIT 1' );
			$exists->execute( [ ':id' => $id ] );

			if ( ! $exists->fetch() ) {
				echo json_encode( [ 'success' => false, 'message' => 'That service could not be found.' ] );
				exit;
			}
		}
	} else {
		$stmt = $pdo->prepare(
			'INSERT INTO services (name, slug, booking_mode, price_note, is_active)
             VALUES (:name, :slug, :mode, :priceNote, :isActive)'
		);
		$stmt->execute( [
			':name'      => $name,
			':slug'      => $slug,
			':mode'      => $bookingMode,
			':priceNote' => $priceNote !== '' ? $priceNote : null,
			':isActive'  => $isActive,
		] );

		$id = (int) $pdo->lastInsertId();
	}

	echo json_encode( [
		'success' => true,
		'message' => $isUpdate ? 'Service updated.' : 'Service created.',
		'id'      => $id,
	] );
} catch ( Throwable $e ) {
	app_log( 'service-save', 'save failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'The service could not be saved. Please try again.' ] );
}
