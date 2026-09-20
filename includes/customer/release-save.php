<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'customer' ) {
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
if ( ! $input ) { $input = $_POST; }

$title       = sanitize_input( $input['title'] ?? '' );
$type        = sanitize_input( $input['type'] ?? 'single' );
$isrc        = sanitize_input( $input['isrc'] ?? '' );
$go_live     = sanitize_input( $input['go_live_date'] ?? '' );
$lyrics      = trim( $input['lyrics'] ?? '' );
$dolby       = intval( $input['dolby'] ?? 0 );
$apple       = intval( $input['apple_itunes'] ?? 0 );
$artwork     = sanitize_input( $input['artwork_path'] ?? '' );
$booking_id  = sanitize_input( $input['booking_id'] ?? '' );
$id          = intval( $input['id'] ?? 0 );
$artists     = $input['artists'] ?? [];

if ( empty( $title ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Title is required.' ] );
    exit;
}

$validTypes = [ 'single', 'ep', 'album' ];
if ( ! in_array( $type, $validTypes ) ) { $type = 'single'; }

try {
    $pdo = db_connect();

    if ( $id > 0 ) {
        $stmt = $pdo->prepare( 'UPDATE releases SET title = :title, type = :type, isrc = :isrc, go_live_date = :golive, lyrics = :lyrics, dolby = :dolby, apple_itunes = :apple, artwork_path = :artwork WHERE id = :id AND customer_id = :cid' );
        $stmt->execute( [ ':title' => $title, ':type' => $type, ':isrc' => $isrc, ':golive' => $go_live ?: null, ':lyrics' => $lyrics, ':dolby' => $dolby, ':apple' => $apple, ':artwork' => $artwork, ':id' => $id, ':cid' => $_SESSION['user_id'] ] );
        $pdo->prepare( 'DELETE FROM release_artists WHERE release_id = :rid' )->execute( [ ':rid' => $id ] );
        $pdo->prepare( 'DELETE FROM release_history WHERE release_id = :rid' )->execute( [ ':rid' => $id ] );
        $hstmt = $pdo->prepare( 'INSERT INTO release_history (release_id, action, message) VALUES (:rid, :action, :msg)' );
        $hstmt->execute( [ ':rid' => $id, ':action' => 'Release Updated', ':msg' => 'Release details updated by customer.' ] );
    } else {
        $stmt = $pdo->prepare( 'INSERT INTO releases (customer_id, booking_id, title, type, isrc, go_live_date, lyrics, dolby, apple_itunes, artwork_path) VALUES (:cid, :bid, :title, :type, :isrc, :golive, :lyrics, :dolby, :apple, :artwork)' );
        $stmt->execute( [ ':cid' => $_SESSION['user_id'], ':bid' => $booking_id ?: null, ':title' => $title, ':type' => $type, ':isrc' => $isrc, ':golive' => $go_live ?: null, ':lyrics' => $lyrics, ':dolby' => $dolby, ':apple' => $apple, ':artwork' => $artwork ] );
        $id = (int) $pdo->lastInsertId();
        $hstmt = $pdo->prepare( 'INSERT INTO release_history (release_id, action, message) VALUES (:rid, :action, :msg)' );
        $hstmt->execute( [ ':rid' => $id, ':action' => 'Initial Submission', ':msg' => 'Release created by customer.' ] );
    }

    if ( ! empty( $artists ) && is_array( $artists ) ) {
        $astmt = $pdo->prepare( 'INSERT INTO release_artists (release_id, role, name) VALUES (:rid, :role, :name)' );
        foreach ( $artists as $a ) {
            $astmt->execute( [ ':rid' => $id, ':role' => sanitize_input( $a['role'] ?? '' ), ':name' => sanitize_input( $a['name'] ?? '' ) ] );
        }
    }

    echo json_encode( [ 'success' => true, 'message' => $id > 0 && ! empty( $input['id'] ) ? 'Release updated.' : 'Release created.', 'id' => $id ] );
} catch ( Exception $e ) {
    echo json_encode( [ 'success' => false, 'message' => 'Database error.' ] );
}
