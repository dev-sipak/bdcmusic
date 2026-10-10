<?php
/**
 * Verify or reject an artist registration.
 *
 * POST { id, action: verify|reject|reopen, note }
 *
 * verify  Assigns the artist a code, issues their ID Card and Certificate, and
 *         emails them. The documents are written before the row is updated, so a
 *         failure leaves the registration pending rather than marking it verified
 *         with nothing to show.
 *
 * reject  Records the reason, withdraws any issued documents from the disk and
 *         emails the reason to the artist. A reason is required: a rejection the
 *         artist cannot act on is worse than no decision at all.
 *
 * reopen  Puts a rejected registration back in the queue, for when the artist has
 *         sent something extra. The documents are withdrawn, but the artist code
 *         is kept: the same artist re-verifying should not end up with a second
 *         ID, and re-verifying rewrites the same two files.
 *
 * Admin-only. CSRF-checked.
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/notifications.php';
require_once __DIR__ . '/../../includes/artist-registrations.php';
require_once __DIR__ . '/../../includes/artist-documents.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	http_response_code( 405 );
	echo json_encode( array( 'success' => false, 'message' => 'Method not allowed' ) );
	exit;
}

$raw   = file_get_contents( 'php://input' );
$input = json_decode( $raw === false ? '' : $raw, true );

if ( ! is_array( $input ) ) {
	$input = $_POST;
}

// Enforce CSRF: the session cookie alone must not be able to trigger this.
require_csrf( $input );

$id     = isset( $input['id'] ) ? (int) $input['id'] : 0;
$action = isset( $input['action'] ) ? clean_text( $input['action'] ) : '';
$note   = isset( $input['note'] ) ? trim( (string) $input['note'] ) : '';

if ( $id <= 0 ) {
	echo json_encode( array( 'success' => false, 'message' => 'Missing registration id.' ) );
	exit;
}

if ( ! in_array( $action, array( 'verify', 'reject', 'reopen' ), true ) ) {
	echo json_encode( array( 'success' => false, 'message' => 'Unknown action.' ) );
	exit;
}

// The note is stored as typed, but capped: it is shown to the artist, and a text
// column that silently truncates mid-word reads as a bug on their side.
if ( strlen( $note ) > 2000 ) {
	$note = substr( $note, 0, 2000 );
}

if ( $action === 'reject' && $note === '' ) {
	echo json_encode( array( 'success' => false, 'message' => 'Please record why the registration is not being accepted.' ) );
	exit;
}

$reviewerId = (string) ( $_SESSION['user_id'] ?? '' );

try {
	$registration = artist_registration_find( $pdo, $id );

	if ( ! $registration ) {
		http_response_code( 404 );
		echo json_encode( array( 'success' => false, 'message' => 'That registration could not be found.' ) );
		exit;
	}

	if ( $action === 'verify' ) {
		if ( $registration['status'] === 'verified' ) {
			echo json_encode( array( 'success' => false, 'message' => 'This registration has already been verified.' ) );
			exit;
		}

		// A rejected registration keeps the code it was given only if it was
		// rejected before a code existed; a verified one already holds its own.
		$code = $registration['artist_code'];

		if ( $code === null || $code === '' ) {
			// The unique key on artist_code is the real guarantee of uniqueness.
			// A generated code that collides means two admins issued the same
			// number, so try the next one rather than failing the whole action.
			$code = null;

			for ( $attempt = 0; $attempt < 20; $attempt++ ) {
				$candidate = artist_registration_code( $pdo, $attempt );
				$exists    = $pdo->prepare( 'SELECT 1 FROM artist_registrations WHERE artist_code = :code LIMIT 1' );
				$exists->execute( array( ':code' => $candidate ) );

				if ( ! $exists->fetch() ) {
					$code = $candidate;
					break;
				}
			}

			if ( $code === null ) {
				app_log( 'artist-registration-update', 'could not allocate an artist code for registration ' . $id );
				http_response_code( 500 );
				echo json_encode( array( 'success' => false, 'message' => 'An artist ID could not be allocated. Please try again.' ) );
				exit;
			}
		}

		// reviewed_at is stamped before rendering: the documents carry the issue
		// date, so it has to be known first.
		$verifiedAt = date( 'Y-m-d H:i:s' );

		$documentRow = array_merge( $registration, array(
			'artist_code' => $code,
			'reviewed_at' => $verifiedAt,
		) );

		$paths = artist_generate_documents( $documentRow );

		if ( ! $paths ) {
			// Nothing was written, so the registration stays pending and the studio
			// can try again once the disk problem is fixed.
			http_response_code( 500 );
			echo json_encode( array( 'success' => false, 'message' => 'The artist documents could not be generated. The registration has been left pending.' ) );
			exit;
		}

		$stmt = $pdo->prepare(
			'UPDATE artist_registrations
				SET status = \'verified\',
					artist_code = :code,
					review_note = :note,
					reviewed_by = :reviewed_by,
					reviewed_at = :reviewed_at,
					id_card_path = :id_card,
					certificate_path = :certificate,
					documents_generated_at = NOW()
			  WHERE id = :id'
		);
		$stmt->execute( array(
			':code'        => $code,
			':note'        => $note !== '' ? $note : null,
			':reviewed_by' => $reviewerId ?: null,
			':reviewed_at' => $verifiedAt,
			':id_card'     => $paths['id_card_path'],
			':certificate' => $paths['certificate_path'],
			':id'          => $id,
		) );

		// The artist is told after the row is committed, so the message can only
		// ever describe a registration that really is verified. A mail failure is
		// logged and swallowed: the documents are already in their dashboard.
		notify_artist_registration_verified( array(
			'artist_name'     => $registration['artist_name'],
			'artist_email'    => $registration['artist_email'],
			'artist_code'     => $code,
			'category_label'  => artist_registration_category_label( $registration['category_slug'] ),
			'plan_name'       => (string) $registration['plan_name'],
		) );

		echo json_encode( array(
			'success' => true,
			'message' => 'Registration verified. The artist ID Card and Certificate have been issued.',
			'code'    => $code,
		) );
		exit;
	}

	if ( $action === 'reject' ) {
		// Withdraw the documents first: a registration that is no longer verified
		// must not be one keystroke away from serving an ID Card again.
		artist_documents_discard( $registration );

		$stmt = $pdo->prepare(
			'UPDATE artist_registrations
				SET status = \'rejected\',
					review_note = :note,
					reviewed_by = :reviewed_by,
					reviewed_at = NOW(),
					id_card_path = NULL,
					certificate_path = NULL,
					documents_generated_at = NULL
			  WHERE id = :id'
		);
		$stmt->execute( array(
			':note'        => $note,
			':reviewed_by' => $reviewerId ?: null,
			':id'          => $id,
		) );

		notify_artist_registration_rejected( array(
			'artist_name'  => $registration['artist_name'],
			'artist_email' => $registration['artist_email'],
			'review_note'  => $note,
		) );

		echo json_encode( array(
			'success' => true,
			'message' => 'Registration rejected and the artist has been told why.',
		) );
		exit;
	}

	// reopen
	artist_documents_discard( $registration );

	$stmt = $pdo->prepare(
		'UPDATE artist_registrations
			SET status = \'pending\',
				review_note = NULL,
				reviewed_by = NULL,
				reviewed_at = NULL,
				id_card_path = NULL,
				certificate_path = NULL,
				documents_generated_at = NULL
		  WHERE id = :id'
	);
	$stmt->execute( array( ':id' => $id ) );

	echo json_encode( array(
		'success' => true,
		'message' => 'Registration moved back to the verification queue.',
	) );
} catch ( Throwable $e ) {
	app_log( 'artist-registration-update', 'update failed', $e );
	http_response_code( 500 );
	echo json_encode( array( 'success' => false, 'message' => 'The registration could not be updated. Please try again.' ) );
}
