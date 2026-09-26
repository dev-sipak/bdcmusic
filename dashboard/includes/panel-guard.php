<?php
/**
 * Customer authentication guard.
 *
 * Include this on every page inside dashboard/ BEFORE header.php so the
 * redirect happens before any markup is emitted.
 *
 * Exposes:
 *   $userName       - display name from the session
 *   $userEmail      - email from the session
 *   $hasDistribution - whether the customer qualifies for the Releases section
 *   $panelBase      - base URL for customer dashboard routes (ends with a slash)
 *   $panelPage      - key of the current section, used by panel-nav.php
 */

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'customer' ) {
    header( 'Location: ' . $basePath . 'login' );
    exit;
}

$userName  = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

$hasDistribution = false;
try {
    $pdo    = db_connect();
    $dStmt  = $pdo->prepare( 'SELECT COUNT(*) FROM bookings WHERE customer_id = :cid AND service_id = 4 AND status IN ("processing","delivered")' );
    $dStmt->execute( [ ':cid' => $_SESSION['user_id'] ] );
    $hasDistribution = (int) $dStmt->fetchColumn() > 0;
} catch ( Exception $e ) {
    $hasDistribution = false;
}

$panelBase = $basePath . 'dashboard/';

if ( ! isset( $panelPage ) ) {
    $panelPage = 'profile';
}
