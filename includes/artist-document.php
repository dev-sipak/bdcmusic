<?php
/**
 * Artist document viewer.
 *
 * GET ?registration=<id>&doc=id-card|certificate&mode=view|download
 *
 * Serves an artist's ID Card or Certificate. The files live in data/documents/,
 * which the project .htaccess denies web access to, so this endpoint is the only
 * way to reach them and it checks that the person asking is entitled to:
 *
 *   - an admin, or
 *   - the customer the registration belongs to, or
 *   - the guest who placed the order, while that order is still the one this
 *     browser confirmed (same rule as includes/download-file.php, so a guest can
 *     read their documents before signing in).
 *
 * A registration that has not been verified has no documents to serve. A
 * registration whose file has gone missing is regenerated on the way out rather
 * than returning a broken document.
 */

if ( session_status() === PHP_SESSION_NONE ) {
	require_once __DIR__ . '/session.php';
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/artist-registrations.php';
require_once __DIR__ . '/artist-documents.php';

$registrationId = isset( $_GET['registration'] ) ? (int) $_GET['registration'] : 0;
$docKind        = isset( $_GET['doc'] ) ? clean_text( $_GET['doc'] ) : '';
$mode           = isset( $_GET['mode'] ) ? clean_text( $_GET['mode'] ) : 'view';

if ( $registrationId <= 0 || ! array_key_exists( $docKind, artist_document_kinds() ) ) {
	http_response_code( 400 );
	exit( 'Unknown document.' );
}

if ( $mode !== 'download' ) {
	$mode = 'view';
}

try {
	$pdo = db_connect();

	$stmt = $pdo->prepare(
		'SELECT r.id, r.customer_id, r.booking_id, r.artist_name, r.artist_code, r.status,
				r.category_slug, r.experience, r.city, r.plan_name, r.reviewed_at,
				r.id_card_path, r.certificate_path, r.documents_generated_at,
				b.customer_id AS booking_customer_id, b.customer_type
		 FROM artist_registrations r
		 LEFT JOIN bookings b ON b.booking_id = r.booking_id
		 WHERE r.id = :id
		 LIMIT 1'
	);
	$stmt->execute( array( ':id' => $registrationId ) );
	$registration = $stmt->fetch();

	if ( ! $registration ) {
		http_response_code( 404 );
		exit( 'Document not found.' );
	}
} catch ( Throwable $e ) {
	app_log( 'artist-document', 'registration lookup failed', $e );
	http_response_code( 500 );
	exit( 'The document could not be loaded.' );
}

$userId   = isset( $_SESSION['user_id'] ) ? (string) $_SESSION['user_id'] : '';
$userRole = isset( $_SESSION['user_role'] ) ? (string) $_SESSION['user_role'] : '';
$allowed  = false;

if ( $userRole === 'admin' ) {
	$allowed = true;
} elseif ( $userId !== '' && (string) $registration['customer_id'] === $userId ) {
	$allowed = true;
} elseif (
	$registration['customer_type'] === 'guest'
	&& ! empty( $_SESSION['booking_confirmed'] )
	&& hash_equals( (string) $_SESSION['booking_confirmed'], (string) $registration['booking_id'] )
) {
	$allowed = true;
}

if ( ! $allowed ) {
	http_response_code( 403 );
	exit( 'Access denied.' );
}

// Documents only exist once the studio has verified the registration. A rejected
// or still-pending one has nothing to hand over.
if ( $registration['status'] !== 'verified' ) {
	http_response_code( 403 );
	exit( 'These documents are available once your registration has been verified.' );
}

$column    = artist_document_kinds()[ $docKind ];
$relative  = artist_documents_ensure( $registration )[ $column ] ?? '';
$absolute  = $relative !== '' ? artist_document_absolute_path( $relative ) : '';

if ( $absolute === '' || ! is_file( $absolute ) ) {
	http_response_code( 404 );
	exit( 'The document file is missing. Please contact the studio.' );
}

$fileName = basename( $absolute );

header( 'Content-Type: text/html; charset=utf-8' );
header( 'Content-Length: ' . filesize( $absolute ) );

if ( $mode === 'download' ) {
	header( 'Content-Disposition: attachment; filename="' . $fileName . '"' );
}

header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );

readfile( $absolute );
exit;
