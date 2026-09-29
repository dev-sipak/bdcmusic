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

$name        = clean_text( $input['name'] ?? '' );
$category_id = intval( $input['category_id'] ?? 0 );
$location    = clean_text( $input['location'] ?? '' );
$bio         = trim( (string) ( $input['bio'] ?? '' ) );
$is_active   = intval( $input['is_active'] ?? 1 );
$image       = clean_text( $input['image'] ?? '' );
$id          = intval( $input['id'] ?? 0 );
$pricing     = $input['pricing'] ?? [];
$isUpdate    = $id > 0;

if ( empty( $name ) || $category_id <= 0 ) {
    echo json_encode( [ 'success' => false, 'message' => 'Name and category are required.' ] );
    exit;
}

// The pricing rows are replaced wholesale below, so they are validated up front.
// Validating inside the transaction would mean rolling back a half-applied save
// just to report a typo.
$cleanPricing = array();

if ( $pricing !== array() && ! is_array( $pricing ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Pricing data is not in the expected format.' ] );
    exit;
}

foreach ( $pricing as $idx => $p ) {
    if ( ! is_array( $p ) ) {
        continue;
    }

    $serviceType = clean_text( $p['service_type'] ?? '' );
    $priceRaw    = $p['price'] ?? '';

    if ( $serviceType === '' ) {
        continue; // An empty row is a blank form field, not an error.
    }

    if ( strlen( $serviceType ) > 100 ) {
        echo json_encode( [ 'success' => false, 'message' => 'A service type is longer than 100 characters.' ] );
        exit;
    }

    if ( ! is_numeric( $priceRaw ) ) {
        echo json_encode( [ 'success' => false, 'message' => 'Pricing for "' . $serviceType . '" must be a number.' ] );
        exit;
    }

    $price = round( (float) $priceRaw, 2 );

    if ( $price < 0 ) {
        echo json_encode( [ 'success' => false, 'message' => 'Pricing for "' . $serviceType . '" cannot be negative.' ] );
        exit;
    }

    // decimal(10,2) tops out just under 100 million.
    if ( $price > 99999999.99 ) {
        echo json_encode( [ 'success' => false, 'message' => 'Pricing for "' . $serviceType . '" is too large.' ] );
        exit;
    }

    $cleanPricing[] = array(
        'service_type' => $serviceType,
        'price'        => $price,
        'sort_order'   => (int) $idx,
    );
}

$slug = strtolower( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $name ) ) );
$slug = trim( $slug, '-' );

if ( $slug === '' ) {
    $slug = 'artist-' . substr( md5( $name . microtime( true ) ), 0, 8 );
}

try {
    // The artist row and its pricing rows are one unit. Without a transaction a
    // failure part way through the pricing insert would leave the artist updated
    // with its price list deleted or half written.
    $pdo->beginTransaction();

    if ( $isUpdate ) {
        // Confirmed with a SELECT rather than rowCount(), because an update that
        // changes nothing still reports zero affected rows, and because we need
        // to know the artist really exists before replacing its pricing.
        $exists = $pdo->prepare( 'SELECT id FROM artists WHERE id = :id LIMIT 1' );
        $exists->execute( [ ':id' => $id ] );

        if ( ! $exists->fetch() ) {
            $pdo->rollBack();
            echo json_encode( [ 'success' => false, 'message' => 'That artist could not be found.' ] );
            exit;
        }

        $stmt = $pdo->prepare( 'UPDATE artists SET name = :name, slug = :slug, category_id = :cat, location = :loc, bio = :bio, is_active = :active, image = :image WHERE id = :id' );
        $stmt->execute( [ ':name' => $name, ':slug' => $slug, ':cat' => $category_id, ':loc' => $location, ':bio' => $bio, ':active' => $is_active, ':image' => $image, ':id' => $id ] );

        $pdo->prepare( 'DELETE FROM artist_pricing WHERE artist_id = :aid' )->execute( [ ':aid' => $id ] );
    } else {
        $stmt = $pdo->prepare( 'INSERT INTO artists (name, slug, category_id, location, bio, is_active, image) VALUES (:name, :slug, :cat, :loc, :bio, :active, :image)' );
        $stmt->execute( [ ':name' => $name, ':slug' => $slug, ':cat' => $category_id, ':loc' => $location, ':bio' => $bio, ':active' => $is_active, ':image' => $image ] );
        $id = (int) $pdo->lastInsertId();
    }

    if ( ! empty( $cleanPricing ) ) {
        $pstmt = $pdo->prepare( 'INSERT INTO artist_pricing (artist_id, service_type, price, sort_order) VALUES (:aid, :svc, :price, :sort)' );
        foreach ( $cleanPricing as $row ) {
            $pstmt->execute( [
                ':aid'   => $id,
                ':svc'   => $row['service_type'],
                ':price' => $row['price'],
                ':sort'  => $row['sort_order'],
            ] );
        }
    }

    $pdo->commit();

    echo json_encode( [ 'success' => true, 'message' => $isUpdate ? 'Artist updated.' : 'Artist created.', 'id' => $id ] );
} catch ( Throwable $e ) {
    if ( isset( $pdo ) && $pdo->inTransaction() ) {
        $pdo->rollBack();
    }

    // The driver's message names tables and columns, so it goes to the log
    // only; the browser gets a generic line.
    app_log( 'artist-save', 'save failed', $e );
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'The artist could not be saved. Please try again.' ] );
}

