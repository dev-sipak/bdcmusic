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

$adminBase = $basePath . 'bdc-admin/';

if ( ! isset( $adminPage ) ) {
    $adminPage = 'overview';
}
