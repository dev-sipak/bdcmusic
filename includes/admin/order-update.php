<?php
/**
 * Admin Order Update API
 *
 * POST (JSON or form encoded)
 *   id       <booking_id>  required
 *   status   one of the bookings.status enum values
 *   record   optional arrangement object:
 *              headline, sub_headline, progress, starts_on, ends_on,
 *              location, notes
 *
 * Both parts are optional, so the admin modal can save the status on its own or
 * save an arrangement on its own. `status` is only touched when present.
 * The arrangement is an upsert on service_records.booking_id (UNIQUE).
 *
 * Status change side effect: when a booking is delivered, the service_record
 * progress is forced to the service's terminal step (e.g. "Completed") so the
 * customer never sees an arrangement stuck at an earlier stage.
 *
 * Admin-only.
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/service-fields.php';
require_once __DIR__ . '/../../includes/notifications.php';

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

$bookingId = clean_text( $input['id'] ?? '' );
if ( $bookingId === '' ) {
	echo json_encode( [ 'success' => false, 'message' => 'Order id is required.' ] );
	exit;
}

$validStatuses = array( 'pending', 'processing', 'hold', 'delivered', 'cancelled' );

$newStatus   = isset( $input['status'] ) ? clean_text( $input['status'] ) : null;
$hasRecord   = isset( $input['record'] ) && is_array( $input['record'] );
$record      = $hasRecord ? $input['record'] : [];

if ( $newStatus !== null && ! in_array( $newStatus, $validStatuses, true ) ) {
	echo json_encode( [ 'success' => false, 'message' => 'Invalid status.' ] );
	exit;
}

if ( $newStatus === null && ! $hasRecord ) {
	echo json_encode( [ 'success' => false, 'message' => 'Nothing to update.' ] );
	exit;
}

try {
	$pdo->beginTransaction();

	// Lock the booking row for the duration so a concurrent status write cannot
	// land between our read of the service_id and the service_records upsert.
	$bStmt = $pdo->prepare(
		'SELECT service_id, status, customer_name, customer_email, customer_phone, service_name
           FROM bookings WHERE booking_id = :id FOR UPDATE'
	);
	$bStmt->execute( [ ':id' => $bookingId ] );
	$booking = $bStmt->fetch();
	if ( ! $booking ) {
		$pdo->rollBack();
		http_response_code( 404 );
		echo json_encode( [ 'success' => false, 'message' => 'Order not found.' ] );
		exit;
	}

	if ( $newStatus !== null ) {
		$pdo->prepare( 'UPDATE bookings SET status = :status WHERE booking_id = :id' )
			->execute( [ ':status' => $newStatus, ':id' => $bookingId ] );
	}

	// Digital distribution orders are managed in the release tracker, not here.
	$slugStmt = $pdo->prepare( 'SELECT slug FROM services WHERE id = :id' );
	$slugStmt->execute( [ ':id' => $booking['service_id'] ] );
	$slug = (string) $slugStmt->fetchColumn();
	$isDistribution = ( $slug === 'digital-distribution' );

	$statusChanged = ( $newStatus !== null && $newStatus !== $booking['status'] );
	$isDelivering  = ( $statusChanged && $newStatus === 'delivered' );
	$recordSaved   = false;
	$progressMoved = false;

	if ( ! $isDistribution && ( $hasRecord || $isDelivering ) ) {
		$options = service_progress_options( $slug );

		if ( $hasRecord ) {
			$headline     = clean_text( $record['headline'] ?? '' );
			$subHeadline  = clean_text( $record['sub_headline'] ?? '' );
			$location     = clean_text( $record['location'] ?? '' );
			$notes        = trim( $record['notes'] ?? '' );
			$progress     = clean_text( $record['progress'] ?? '' );
			$startsOn     = $record['starts_on'] ?? '';
			$endsOn       = $record['ends_on'] ?? '';

			if ( $progress === '' ) {
				$progress = $options[0] ?? 'Confirmed';
			} elseif ( ! empty( $options ) && ! in_array( $progress, $options, true ) ) {
				$pdo->rollBack();
				echo json_encode( [ 'success' => false, 'message' => 'Invalid progress value.' ] );
				exit;
			}

			$datePattern = '/^\d{4}-\d{2}-\d{2}$/';
			if ( $startsOn !== '' && $startsOn !== null && ! preg_match( $datePattern, $startsOn ) ) {
				$startsOn = '';
			}
			if ( $endsOn !== '' && $endsOn !== null && ! preg_match( $datePattern, $endsOn ) ) {
				$endsOn = '';
			}

			// A delivered order should not be left showing an in-progress stage.
			if ( $isDelivering && ! empty( $options ) ) {
				$progress = end( $options );
			}

			$emptyRow = ( $headline === '' && $subHeadline === '' && $location === ''
				&& $notes === '' && $startsOn === '' && $endsOn === '' );

			if ( $emptyRow ) {
				// Admin cleared every field: drop the row rather than store blanks.
				$pdo->prepare( 'DELETE FROM service_records WHERE booking_id = :id' )
					->execute( [ ':id' => $bookingId ] );
			} else {
				$pdo->prepare(
					'INSERT INTO service_records
                        (booking_id, service_id, headline, sub_headline, progress, starts_on, ends_on, location, notes)
                     VALUES
                        (:booking_id, :service_id, :headline, :sub_headline, :progress, :starts_on, :ends_on, :location, :notes)
                     ON DUPLICATE KEY UPDATE
                        service_id  = VALUES(service_id),
                        headline    = VALUES(headline),
                        sub_headline= VALUES(sub_headline),
                        progress    = VALUES(progress),
                        starts_on   = VALUES(starts_on),
                        ends_on     = VALUES(ends_on),
                        location    = VALUES(location),
                        notes       = VALUES(notes)'
				)->execute( [
					':booking_id'   => $bookingId,
					':service_id'   => $booking['service_id'],
					':headline'     => $headline,
					':sub_headline' => $subHeadline,
					':progress'     => $progress,
					':starts_on'    => $startsOn !== '' ? $startsOn : null,
					':ends_on'      => $endsOn !== '' ? $endsOn : null,
					':location'     => $location,
					':notes'        => $notes,
				] );
			}
			$recordSaved = true;
		} elseif ( ! empty( $options ) ) {
			// Status-only save on a delivered order: advance the arrangement that
			// already exists to its terminal step instead of leaving it mid-way.
			// The previous progress is read first because rowCount() reports 0
			// when the value is already the terminal one, which would silently
			// drop the "marked as complete" message even though the order was
			// saved.
			$prev = $pdo->prepare( 'SELECT progress FROM service_records WHERE booking_id = :id LIMIT 1' );
			$prev->execute( [ ':id' => $bookingId ] );
			$previousProgress = $prev->fetchColumn();

			if ( $previousProgress !== false ) {
				$terminal = end( $options );
				$upd = $pdo->prepare(
					'UPDATE service_records SET progress = :progress WHERE booking_id = :id'
				);
				$upd->execute( [ ':progress' => $terminal, ':id' => $bookingId ] );
				$progressMoved = ( $previousProgress !== $terminal );
			}
		}
	}

	$pdo->commit();

	// A status change is the one thing a customer watches for, so both sides are
	// emailed. The booking row read above still holds the previous status.
	if ( $statusChanged ) {
		$order = array(
			'booking_id'   => $bookingId,
			'name'         => (string) $booking['customer_name'],
			'email'        => (string) $booking['customer_email'],
			'phone'        => (string) $booking['customer_phone'],
			'service_name' => (string) $booking['service_name'],
			'plan_name'    => '',
			'total'        => null,
			'status'       => ucfirst( $newStatus ),
		);

		notify_order_status_changed( $order, $booking['status'], $newStatus );
		notify_admin_order_status_changed( $order, $booking['status'], $newStatus );
	}

	$messages = array();
	if ( $newStatus !== null ) {
		$messages[] = 'Status updated.';
	}
	if ( $recordSaved ) {
		$messages[] = 'Arrangement saved.';
	} elseif ( $progressMoved ) {
		$messages[] = 'Arrangement marked as complete.';
	}

	echo json_encode( [
		'success'  => true,
		'message'  => implode( ' ', $messages ),
	] );
  } catch ( Throwable $e ) {
	  if ( isset( $pdo ) && $pdo->inTransaction() ) {
		  $pdo->rollBack();
	  }
	  app_log( 'order-update', 'update failed', $e );
	  http_response_code( 500 );
	  echo json_encode( [ 'success' => false, 'message' => 'The order could not be updated. Please try again.' ] );
  }
