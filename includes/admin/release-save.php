<?php
/**
 * Admin Release Save API
 *
 * POST (JSON)
 *   id       <release id>  required
 *   status   one of releases.status enum values, optional
 *   reviewer_note  text appended to release_history as a note, optional
 *   tracks   [ { track_no, title, isrc, duration } ]  full replacement, optional
 *   links    [ { platform, url, is_active } ]         full replacement, optional
 *
 * Tracks and links are sent as the complete desired list and are written in a
 * single transaction, so the edit UI can just post what it shows. Rows the
 * admin deleted simply are not sent; the rest are upserted on
 * (release_id, track_no) and (release_id, platform). Any status change is
 * recorded in release_history the same way the customer-facing flow records
 * submissions, so the customer's history stays in step with admin actions.
 *
 * Admin-only.
 */

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

$input = json_decode( file_get_contents( 'php://input' ), true );
if ( ! $input ) {
	$input = $_POST;
}

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf( $input );

$releaseId = (int) ( $input['id'] ?? 0 );
if ( $releaseId <= 0 ) {
	echo json_encode( [ 'success' => false, 'message' => 'Release id is required.' ] );
	exit;
}

$validStatuses = array( 'draft', 'pending', 'verification', 'onhold', 'rejected', 'approved', 'live', 'takedown' );
$newStatus     = isset( $input['status'] ) ? clean_text( $input['status'] ) : null;
$reviewerNote  = isset( $input['reviewer_note'] ) ? trim( $input['reviewer_note'] ) : '';

if ( $newStatus !== null && ! in_array( $newStatus, $validStatuses, true ) ) {
	echo json_encode( [ 'success' => false, 'message' => 'Invalid status.' ] );
	exit;
}

$tracks = $input['tracks'] ?? null;
$links  = $input['links'] ?? null;
if ( $tracks !== null && ! is_array( $tracks ) ) $tracks = null;
if ( $links !== null && ! is_array( $links ) ) $links = null;

if ( $newStatus === null && $tracks === null && $links === null && $reviewerNote === '' ) {
	echo json_encode( [ 'success' => false, 'message' => 'Nothing to update.' ] );
	exit;
}

// A platform link is only useful to the customer if it is a real http(s) URL.
$badLink = false;
if ( $links !== null ) {
	foreach ( $links as $link ) {
		$url = trim( (string) ( $link['url'] ?? '' ) );
		if ( $url === '' ) continue;
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) || ! preg_match( '#^https?://#i', $url ) ) {
			$badLink = true;
			break;
		}
	}
}
if ( $badLink ) {
	echo json_encode( [ 'success' => false, 'message' => 'Every live link must be a full http(s) URL.' ] );
	exit;
}

try {
	$pdo = db_connect();
	$pdo->beginTransaction();

	$rStmt = $pdo->prepare( 'SELECT status FROM releases WHERE id = :id FOR UPDATE' );
	$rStmt->execute( [ ':id' => $releaseId ] );
	$current = $rStmt->fetch();
	if ( ! $current ) {
		$pdo->rollBack();
		http_response_code( 404 );
		echo json_encode( [ 'success' => false, 'message' => 'Release not found.' ] );
		exit;
	}

	if ( $newStatus !== null && $newStatus !== $current['status'] ) {
		$pdo->prepare( 'UPDATE releases SET status = :status WHERE id = :id' )
			->execute( [ ':status' => $newStatus, ':id' => $releaseId ] );

		$hStmt = $pdo->prepare(
			'INSERT INTO release_history (release_id, action, message, reviewer_note)
             VALUES (:rid, :action, :message, :note)'
		);
		$hStmt->execute( [
			':rid'     => $releaseId,
			':action'  => $newStatus,
			':message' => 'Status updated to ' . $newStatus . ' by the BDC Music team.',
			':note'    => $reviewerNote !== '' ? $reviewerNote : null,
		] );
	} elseif ( $reviewerNote !== '' ) {
		$hStmt = $pdo->prepare(
			'INSERT INTO release_history (release_id, action, message, reviewer_note)
             VALUES (:rid, :action, :message, :note)'
		);
		$hStmt->execute( [
			':rid'     => $releaseId,
			':action'  => $current['status'],
			':message' => 'Updated by the BDC Music team.',
			':note'    => $reviewerNote,
		] );
	}

	if ( $tracks !== null ) {
		// The client posts the complete desired list, so the simplest correct
		// write is delete-then-reinsert. It runs inside the transaction, so a
		// failure part way through leaves the original rows intact.
		$pdo->prepare( 'DELETE FROM release_tracks WHERE release_id = :rid' )
			->execute( [ ':rid' => $releaseId ] );

		$ins = $pdo->prepare(
			'INSERT INTO release_tracks (release_id, track_no, title, isrc, duration)
             VALUES (:rid, :no, :title, :isrc, :duration)'
		);
		// Number the rows here rather than trusting track_no from the request:
		// uq_rt_release_no would turn a duplicate number into a failed write.
		$no = 0;
		foreach ( $tracks as $track ) {
			$title = clean_text( $track['title'] ?? '' );
			if ( $title === '' ) continue;
			$ins->execute( [
				':rid'      => $releaseId,
				':no'       => ++$no,
				':title'    => $title,
				':isrc'     => clean_text( $track['isrc'] ?? '' ) ?: null,
				':duration' => clean_text( $track['duration'] ?? '' ) ?: null,
			] );
		}
	}

	if ( $links !== null ) {
		$pdo->prepare( 'DELETE FROM release_platform_links WHERE release_id = :rid' )
			->execute( [ ':rid' => $releaseId ] );

		$ins = $pdo->prepare(
			'INSERT INTO release_platform_links (release_id, platform, url, is_active, sort_order)
             VALUES (:rid, :platform, :url, :active, :sort)'
		);
		$sort = 0;
		$seen = array();
		foreach ( $links as $link ) {
			$platform = clean_text( $link['platform'] ?? '' );
			$url      = trim( (string) ( $link['url'] ?? '' ) );
			// Only a named platform with a URL is worth storing.
			if ( $platform === '' || $url === '' ) continue;
			// uq_rpl_release_platform allows one row per platform, so a repeated
			// platform in the payload keeps the first entry instead of failing.
			$key = strtolower( $platform );
			if ( isset( $seen[ $key ] ) ) continue;
			$seen[ $key ] = true;
			$ins->execute( [
				':rid'      => $releaseId,
				':platform' => $platform,
				':url'      => $url,
				':active'   => ! empty( $link['is_active'] ) ? 1 : 0,
				':sort'     => $sort++,
			] );
		}
	}

	$pdo->commit();

	echo json_encode( [ 'success' => true, 'message' => 'Release updated.' ] );
  } catch ( Throwable $e ) {
	  if ( isset( $pdo ) && $pdo->inTransaction() ) {
		  $pdo->rollBack();
	  }
	  app_log( 'admin-release-save', 'save failed', $e );
	  http_response_code( 500 );
	  echo json_encode( [ 'success' => false, 'message' => 'The release could not be saved. Please try again.' ] );
  }
