<?php
/**
 * Customer authentication guard + service entitlement resolver.
 *
 * Include this on every page inside dashboard/ BEFORE header.php so the
 * redirect happens before any markup is emitted.
 *
 * Exposes:
 *   $userName         - display name from the session
 *   $userEmail        - email from the session
 *   $purchasedServices- ordered list of the services this customer has bought,
 *                        keyed by services.slug. Each entry carries an
 *                        `unlocked` flag that says whether the service has
 *                        reached the status required to show its full section.
 *   $panelBase        - base URL for customer dashboard routes (ends with a slash)
 *   $panelPage        - key of the current section, used by panel-nav.php
 */

require_once __DIR__ . '/../../includes/service-fields.php';
require_once __DIR__ . '/../../includes/helpers.php';

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'customer' ) {
    header( 'Location: ' . $basePath . 'login' );
    exit;
}

$userName  = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

// ─── Service entitlement ────────────────────────────────────────────
//
// A section exists for a service only when the customer has a booking for it,
// and it is only unlocked once at least one of those bookings reaches a
// "processing" or "delivered" status. Both the flag and the per-service
// booking list are resolved here so panel-nav.php, the section pages and the
// JSON endpoints all agree on what the customer is allowed to see.
//
// The unlock statuses come from SERVICE_UNLOCK_STATUSES in service-fields.php
// so there is a single definition of "unlocked" in the codebase.

$purchasedServices = array();

try {
    $pdo = db_connect();

    // A password change elsewhere must end this session too. The fingerprint
    // recorded at sign-in no longer matches the stored hash, so the session is
    // dropped and the customer has to sign in again.
    if ( ! session_password_is_current( $pdo, $_SESSION['user_id'] ) ) {
        app_session_destroy();
        header( 'Location: ' . $basePath . 'login' );
        exit;
    }

    $unlockPlaceholders = array();
    $unlockParams       = array();
    foreach ( array_values( SERVICE_UNLOCK_STATUSES ) as $i => $unlockStatus ) {
        $unlockPlaceholders[]              = ':unlock' . $i;
        $unlockParams[ ':unlock' . $i ]    = $unlockStatus;
    }

    $svcStmt = $pdo->prepare(
        'SELECT s.id, s.slug, s.name,
                MAX(b.status IN (' . implode( ',', $unlockPlaceholders ) . ')) AS unlocked,
                COUNT(b.id) AS order_count
         FROM bookings b
         JOIN services s ON b.service_id = s.id
         WHERE b.customer_id = :cid
         GROUP BY s.id, s.slug, s.name
         ORDER BY s.id'
    );
    $svcStmt->execute( $unlockParams + array( ':cid' => $_SESSION['user_id'] ) );

    foreach ( $svcStmt->fetchAll() as $row ) {
        $purchasedServices[ $row['slug'] ] = array(
            'id'         => (int) $row['id'],
            'slug'       => $row['slug'],
            'name'       => $row['name'],
            'nav'        => service_nav_label( $row['slug'] ),
            'icon'       => service_nav_icon( $row['slug'] ),
            'unlocked'   => (int) $row['unlocked'] === 1,
            'orderCount' => (int) $row['order_count'],
        );
    }
  } catch ( Throwable $e ) {
      app_log( 'panel-guard', 'could not resolve entitlements for ' . ( $_SESSION['user_id'] ?? 'unknown' ), $e );
      $purchasedServices = array();
  }

// The My Releases link is the Digital Music Distribution section, so the
// existing gate is now just a lookup into the resolved entitlement list.
$hasDistribution = ! empty( $purchasedServices['digital-distribution'] )
    && $purchasedServices['digital-distribution']['unlocked'];

$panelBase = $basePath . 'dashboard/';

if ( ! isset( $panelPage ) ) {
    $panelPage = 'profile';
}

/**
 * Whether the current customer has bought the given service slug.
 *
 * @param string $slug services.slug.
 * @return bool
 */
function customer_has_service( $slug ) {
    global $purchasedServices;
    return isset( $purchasedServices[ $slug ] );
}

/**
 * Whether the current customer's section for the given service is unlocked.
 *
 * @param string $slug services.slug.
 * @return bool
 */
function customer_service_unlocked( $slug ) {
    global $purchasedServices;
    return ! empty( $purchasedServices[ $slug ]['unlocked'] );
}
