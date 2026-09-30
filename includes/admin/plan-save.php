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
$serviceId   = (int) ( $input['service_id'] ?? 0 );
$groupKey    = clean_text( $input['group_key'] ?? '' );
$groupLabel  = clean_text( $input['group_label'] ?? '' );
$name        = clean_text( $input['name'] ?? '' );
$priceRaw    = $input['price'] ?? '0';
$priceNote   = clean_text( $input['price_note'] ?? '' );
$description = trim( (string) ( $input['description'] ?? '' ) );
$bestFor     = trim( (string) ( $input['best_for'] ?? '' ) );
$sortOrder   = (int) ( $input['sort_order'] ?? 0 );
$isDefault   = ! empty( $input['is_default'] ) ? 1 : 0;
$isActive    = ! empty( $input['is_active'] ) ? 1 : 0;
$isEnquiry   = ! empty( $input['is_enquiry'] ) ? 1 : 0;

// A plan can be published as a price list without being buyable. Absent from the
// payload means 1, so an older admin client that does not send the key keeps
// making new plans bookable rather than silently reference-only.
$isOrderable = array_key_exists( 'is_orderable', (array) $input ) ? ( ! empty( $input['is_orderable'] ) ? 1 : 0 ) : 1;
$isUpdate    = $id > 0;

if ( $name === '' ) {
	echo json_encode( [ 'success' => false, 'message' => 'A package name is required.' ] );
	exit;
}

if ( strlen( $name ) > 100 ) {
	echo json_encode( [ 'success' => false, 'message' => 'The package name is longer than 100 characters.' ] );
	exit;
}

// The group key is what the catalogue and the rate cards group by, so it is
// slugified rather than taken raw: a typo with a space in it would otherwise
// split one tier across two groups on the front end.
if ( $groupKey === '' ) {
	$groupKey = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $name ) );
	$groupKey = trim( $groupKey, '-' );
}

if ( $groupKey === '' ) {
	$groupKey = 'general';
}

if ( strlen( $groupKey ) > 60 ) {
	echo json_encode( [ 'success' => false, 'message' => 'The package group key is longer than 60 characters.' ] );
	exit;
}

if ( $groupLabel !== '' && strlen( $groupLabel ) > 100 ) {
	echo json_encode( [ 'success' => false, 'message' => 'The group label is longer than 100 characters.' ] );
	exit;
}

if ( $priceNote !== '' && strlen( $priceNote ) > 60 ) {
	echo json_encode( [ 'success' => false, 'message' => 'The price note is longer than 60 characters.' ] );
	exit;
}

if ( strlen( $description ) > 255 || strlen( $bestFor ) > 255 ) {
	echo json_encode( [ 'success' => false, 'message' => 'A description or "best for" line is longer than 255 characters.' ] );
	exit;
}

// An enquiry tier is quoted, never sold, so it has no price of its own and
// must not claim one.
if ( $isEnquiry ) {
	$priceRaw = 0;
} elseif ( ! is_numeric( $priceRaw ) ) {
	echo json_encode( [ 'success' => false, 'message' => 'The price must be a number.' ] );
	exit;
}

$price = round( (float) $priceRaw, 2 );

if ( $price < 0 || $price > 99999999.99 ) {
	echo json_encode( [ 'success' => false, 'message' => 'The price must be between 0 and 99,999,999.99.' ] );
	exit;
}

// The editor posts one feature per line; a caller that posts the array the
// column holds is accepted too, so the endpoint does not care which shape it
// gets.
$rawFeatures = $input['features'] ?? [];

if ( is_string( $rawFeatures ) ) {
	$rawFeatures = preg_split( '/\r\n|\r|\n/', $rawFeatures );
}

if ( ! is_array( $rawFeatures ) ) {
	$rawFeatures = [];
}

$features = [];

foreach ( $rawFeatures as $feature ) {
	$feature = trim( (string) $feature );

	if ( $feature === '' || count( $features ) >= 40 ) {
		continue;
	}

	if ( mb_strlen( $feature ) > 200 ) {
		echo json_encode( [ 'success' => false, 'message' => 'A feature line is longer than 200 characters.' ] );
		exit;
	}

	$features[] = $feature;
}

try {
	// The service has to exist before anything is written, so the check is
	// made first rather than relying on the foreign key to reject the insert
	// after the transaction has already been opened.
	$existsService = $pdo->prepare( 'SELECT id FROM services WHERE id = :id LIMIT 1' );
	$existsService->execute( [ ':id' => $serviceId ] );

	if ( ! $existsService->fetch() ) {
		echo json_encode( [ 'success' => false, 'message' => 'Choose the service this package belongs to.' ] );
		exit;
	}

	$featuresJson = json_encode( $features, JSON_UNESCAPED_UNICODE );

	$pdo->beginTransaction();

	if ( $isUpdate ) {
		// Confirmed with a SELECT rather than rowCount(), because an update that
		// changes nothing still reports zero affected rows.
		$exists = $pdo->prepare( 'SELECT id FROM service_plans WHERE id = :id LIMIT 1' );
		$exists->execute( [ ':id' => $id ] );

		if ( ! $exists->fetch() ) {
			$pdo->rollBack();
			echo json_encode( [ 'success' => false, 'message' => 'That package could not be found.' ] );
			exit;
		}

		$stmt = $pdo->prepare(
			'UPDATE service_plans
                SET service_id = :serviceId, group_key = :groupKey, group_label = :groupLabel,
                    name = :name, price = :price, price_note = :priceNote,
                    description = :description, best_for = :bestFor, features = :features,
                    is_default = :isDefault, is_active = :isActive, is_enquiry = :isEnquiry,
                    is_orderable = :isOrderable,
                    sort_order = :sortOrder
              WHERE id = :id'
		);
		$stmt->execute( [
			':serviceId'   => $serviceId,
			':groupKey'    => $groupKey,
			':groupLabel'  => $groupLabel !== '' ? $groupLabel : null,
			':name'        => $name,
			':price'       => $price,
			':priceNote'   => $priceNote !== '' ? $priceNote : null,
			':description' => $description !== '' ? $description : null,
			':bestFor'     => $bestFor !== '' ? $bestFor : null,
			':features'    => $featuresJson,
			':isDefault'   => $isDefault,
			':isActive'    => $isActive,
			':isEnquiry'   => $isEnquiry,
			':isOrderable' => $isOrderable,
			':sortOrder'   => $sortOrder,
			':id'          => $id,
		] );
	} else {
		$stmt = $pdo->prepare(
			'INSERT INTO service_plans
                (service_id, group_key, group_label, name, price, price_note, description,
                 best_for, features, is_default, is_active, is_enquiry, is_orderable, sort_order)
             VALUES
                (:serviceId, :groupKey, :groupLabel, :name, :price, :priceNote, :description,
                 :bestFor, :features, :isDefault, :isActive, :isEnquiry, :isOrderable, :sortOrder)'
		);
		$stmt->execute( [
			':serviceId'   => $serviceId,
			':groupKey'    => $groupKey,
			':groupLabel'  => $groupLabel !== '' ? $groupLabel : null,
			':name'        => $name,
			':price'       => $price,
			':priceNote'   => $priceNote !== '' ? $priceNote : null,
			':description' => $description !== '' ? $description : null,
			':bestFor'     => $bestFor !== '' ? $bestFor : null,
			':features'    => $featuresJson,
			':isDefault'   => $isDefault,
			':isActive'    => $isActive,
			':isEnquiry'   => $isEnquiry,
			':isOrderable' => $isOrderable,
			':sortOrder'   => $sortOrder,
		] );

		$id = (int) $pdo->lastInsertId();
	}

	// "Most popular" is read as the service's opening package, so only one
	// package of a service may hold it or the choice would silently fall back
	// to whichever row sorted first.
	if ( $isDefault ) {
		$clear = $pdo->prepare( 'UPDATE service_plans SET is_default = 0 WHERE service_id = :serviceId AND id <> :id' );
		$clear->execute( [ ':serviceId' => $serviceId, ':id' => $id ] );
	}

	$pdo->commit();

	echo json_encode( [
		'success' => true,
		'message' => $isUpdate ? 'Package updated.' : 'Package created.',
		'id'      => $id,
	] );
} catch ( Throwable $e ) {
	if ( isset( $pdo ) && $pdo->inTransaction() ) {
		$pdo->rollBack();
	}

	// The driver's message names tables and columns, so it goes to the log
	// only; the browser gets a generic line.
	app_log( 'plan-save', 'save failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'The package could not be saved. Please try again.' ] );
}
