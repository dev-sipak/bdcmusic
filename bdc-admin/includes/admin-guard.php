<?php
/**
 * Admin authentication guard.
 *
 * Include this on every page inside bdc-admin/ BEFORE admin-header.php so the
 * redirect happens before any markup is emitted.
 *
 * Exposes:
 *   $adminBase - base URL for admin routes (always ends with a slash)
 *   $adminPage - key of the current section, used by admin-nav.php
 */

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'admin' ) {
    header( 'Location: ' . $basePath . 'login' );
    exit;
}

require_once __DIR__ . '/../../includes/helpers.php';

// A password change elsewhere must end this session too: the fingerprint
// recorded at sign-in no longer matches the stored hash once it has been
// changed, so the admin session is dropped rather than left usable.
try {
    require_once __DIR__ . '/../../includes/database.php';

    if ( ! session_password_is_current( db_connect(), $_SESSION['user_id'] ) ) {
        app_session_destroy();
        header( 'Location: ' . $basePath . 'login' );
        exit;
    }
} catch ( PDOException $e ) {
    // A database problem must not silently grant or revoke admin access, and it
    // must not lock the administrator out during an outage. The role check
    // above has already passed, so the session stands.
    app_log( 'admin-guard', 'could not verify session for ' . $_SESSION['user_id'], $e );
}

$adminBase = $basePath . 'bdc-admin/';

if ( ! isset( $adminPage ) ) {
    $adminPage = 'overview';
}
