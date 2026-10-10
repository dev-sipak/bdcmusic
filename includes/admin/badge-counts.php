<?php
/**
 * Admin sidebar badge counts.
 *
 * GET
 *
 * Returns the two numbers the sidebar badges show: orders no admin has opened
 * yet (bookings.admin_read_at IS NULL) and marketplace registrations still
 * pending verification. assets/js/admin-nav-badges.js polls this so a new order
 * or a new registration shows up without a page reload.
 *
 * Admin-only.
 */

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/orders-read-state.php';
require_once __DIR__ . '/../../includes/artist-registrations.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

try {
	echo json_encode( [
		'success' => true,
		'counts'  => [
			'orders'        => admin_unread_orders_count( $pdo ),
			'verifications' => artist_registrations_pending_count( $pdo ),
		],
	] );
} catch ( Throwable $e ) {
	app_log( 'admin-badge-counts', 'request failed', $e );
	http_response_code( 500 );
	echo json_encode( [ 'success' => false, 'message' => 'The counts could not be loaded.' ] );
}