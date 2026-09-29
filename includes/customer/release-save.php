<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'customer' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Method not allowed' ] );
    exit;
}

$input = json_decode( file_get_contents( 'php://input' ), true );
if ( ! $input ) { $input = $_POST; }

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf( $input );

$title       = clean_text( $input['title'] ?? '' );
$type        = clean_text( $input['type'] ?? 'single' );
$isrc        = clean_text( $input['isrc'] ?? '' );
$go_live     = clean_text( $input['go_live_date'] ?? '' );
$lyrics      = trim( $input['lyrics'] ?? '' );
$dolby       = intval( $input['dolby'] ?? 0 );
$apple       = intval( $input['apple_itunes'] ?? 0 );
$artwork     = clean_text( $input['artwork_path'] ?? '' );
$booking_id  = clean_text( $input['booking_id'] ?? '' );
$id          = intval( $input['id'] ?? 0 );
$artists     = $input['artists'] ?? [];

if ( empty( $title ) ) {
    echo json_encode( [ 'success' => false, 'message' => 'Title is required.' ] );
    exit;
}

$validTypes = [ 'single', 'ep', 'album' ];
if ( ! in_array( $type, $validTypes ) ) { $type = 'single'; }

try {
    // Releases belong to Digital Music Distribution, so the same entitlement the
    // page gate in dashboard/releases.php applies has to be enforced here too. The
    // true asks for an *unlocked* booking (SERVICE_UNLOCK_STATUSES: processing or
    // delivered). Without it a customer whose only distribution booking is still
    // pending or cancelled would be refused the page but could still reach this
    // API by calling it directly, which is the inconsistency this flag closes.
    // Without it, any signed-in customer could create or edit releases by
    // calling this endpoint directly.
    if ( ! customer_has_service_booking( $pdo, $_SESSION['user_id'], 'digital-distribution', true ) ) {
        http_response_code( 403 );
        echo json_encode( [ 'success' => false, 'message' => 'You have not purchased this service.' ] );
        exit;
    }

    if ( $id > 0 ) {
        $pdo->beginTransaction();

        // Ownership is checked with its own SELECT rather than inferred from the
        // UPDATE's rowCount(): MySQL reports 0 affected rows when the submitted
        // values are identical to the stored ones, which would otherwise be
        // indistinguishable from "no such release" and would reject a no-op save.
        $ownStmt = $pdo->prepare( 'SELECT id FROM releases WHERE id = :id AND customer_id = :cid LIMIT 1' );
        $ownStmt->execute( [ ':id' => $id, ':cid' => $_SESSION['user_id'] ] );

        if ( ! $ownStmt->fetch() ) {
            $pdo->rollBack();
            echo json_encode( [ 'success' => false, 'message' => 'Release not found or access denied.' ] );
            exit;
        }

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
            $astmt->execute( [ ':rid' => $id, ':role' => clean_text( $a['role'] ?? '' ), ':name' => clean_text( $a['name'] ?? '' ) ] );
        }
    }

    if ( $pdo->inTransaction() ) {
        $pdo->commit();
    }

    echo json_encode( [ 'success' => true, 'message' => $id > 0 && ! empty( $input['id'] ) ? 'Release updated.' : 'Release created.', 'id' => $id ] );
  } catch ( Throwable $e ) {
      if ( isset( $pdo ) && $pdo->inTransaction() ) {
          $pdo->rollBack();
      }
      app_log( 'customer-release-save', 'save failed', $e );
      http_response_code( 500 );
      echo json_encode( [ 'success' => false, 'message' => 'Your release could not be saved. Please try again.' ] );
  }
