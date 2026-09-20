<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'admin' ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Unauthorized' ] );
    exit;
}

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed' ] );
    exit;
}

$input = json_decode( file_get_contents( 'php://input' ), true );
if ( ! $input ) {
    $input = $_POST;
}

$name       = sanitize_input( $input['name'] ?? '' );
$category_id = intval( $input['category_id'] ?? 0 );
$location   = sanitize_input( $input['location'] ?? '' );
$bio        = trim( $input['bio'] ?? '' );
$is_active  = intval( $input['is_active'] ?? 1 );
$image      = sanitize_input( $input['image'] ?? '' );
$id         = intval( $input['id'] ?? 0 );
$pricing    = $input['pricing'] ?? [];

if ( empty( $name ) || $category_id <= 0 ) {
    echo json_encode( [ 'success' => false, 'message' => 'Name and category are required.' ] );
    exit;
}

$slug = strtolower( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $name ) ) );
$slug = trim( $slug, '-' );

try {
    $pdo = db_connect();

    if ( $id > 0 ) {
        $stmt = $pdo->prepare( 'UPDATE artists SET name = :name, slug = :slug, category_id = :cat, location = :loc, bio = :bio, is_active = :active, image = :image WHERE id = :id' );
        $stmt->execute( [ ':name' => $name, ':slug' => $slug, ':cat' => $category_id, ':loc' => $location, ':bio' => $bio, ':active' => $is_active, ':image' => $image, ':id' => $id ] );

        $pdo->prepare( 'DELETE FROM artist_pricing WHERE artist_id = :aid' )->execute( [ ':aid' => $id ] );
    } else {
        $stmt = $pdo->prepare( 'INSERT INTO artists (name, slug, category_id, location, bio, is_active, image) VALUES (:name, :slug, :cat, :loc, :bio, :active, :image)' );
        $stmt->execute( [ ':name' => $name, ':slug' => $slug, ':cat' => $category_id, ':loc' => $location, ':bio' => $bio, ':active' => $is_active, ':image' => $image ] );
        $id = (int) $pdo->lastInsertId();
    }

    if ( ! empty( $pricing ) && is_array( $pricing ) ) {
        $pstmt = $pdo->prepare( 'INSERT INTO artist_pricing (artist_id, service_type, price, sort_order) VALUES (:aid, :svc, :price, :sort)' );
        foreach ( $pricing as $idx => $p ) {
            $pstmt->execute( [
                ':aid'   => $id,
                ':svc'   => sanitize_input( $p['service_type'] ?? '' ),
                ':price' => floatval( $p['price'] ?? 0 ),
                ':sort'  => $idx,
            ] );
        }
    }

    echo json_encode( [ 'success' => true, 'message' => $id > 0 && ! empty( $input['id'] ) ? 'Artist updated.' : 'Artist created.', 'id' => $id ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error: ' . $e->getMessage() ] );
}
