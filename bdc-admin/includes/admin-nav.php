<?php
/**
 * Admin sidebar navigation.
 *
 * Requires: $adminBase (from admin-guard.php), $adminPage.
 * Renders one link per admin section. The active item is resolved
 * server-side from $adminPage.
 *
 * Two links carry a badge: Orders counts orders no admin has opened yet, and
 * Verifications counts marketplace registrations still waiting to be checked.
 * Both are counted here rather than in the browser so the number is right in
 * the first paint, and assets/js/admin-nav-badges.js keeps them current from
 * then on.
 */

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/orders-read-state.php';
require_once __DIR__ . '/../../includes/artist-registrations.php';

$adminNavItems = array(
	'overview'      => array( 'label' => 'Dashboard', 'icon' => 'gauge',      'url' => $adminBase ),
	'orders'        => array( 'label' => 'Orders',    'icon' => 'box',        'url' => $adminBase . 'orders' ),
	'verifications' => array( 'label' => 'Verifications', 'icon' => 'badge-check', 'url' => $adminBase . 'artist-verifications' ),
	'releases'      => array( 'label' => 'Releases',  'icon' => 'disc',       'url' => $adminBase . 'releases' ),
	'customers'     => array( 'label' => 'Customers', 'icon' => 'users',      'url' => $adminBase . 'customers' ),
	'artists'       => array( 'label' => 'Artists',   'icon' => 'palette',    'url' => $adminBase . 'artists' ),
	'services'      => array( 'label' => 'Services',  'icon' => 'layers',     'url' => $adminBase . 'services' ),
	'enquiries'     => array( 'label' => 'Enquiries', 'icon' => 'mail',       'url' => $adminBase . 'enquiries' ),
);

// A badge that cannot be counted must not take the sidebar down with it: the
// dashboard would then be unreachable while the database is unhappy.
$adminNavBadges = array(
	'orders'        => 0,
	'verifications' => 0,
);

try {
	$adminNavPdo = db_connect();

	$adminNavBadges['orders']        = admin_unread_orders_count( $adminNavPdo );
	$adminNavBadges['verifications'] = artist_registrations_pending_count( $adminNavPdo );
} catch ( Throwable $e ) {
	app_log( 'admin-nav', 'badge counts unavailable', $e );
}
?>
<aside class="adm-sidebar" id="adm-sidebar">
	<div class="adm-brand">
		<div class="adm-logo">
			<i data-lucide="shield-half"></i>
		</div>
		<div class="adm-brand-text">
			<strong>BDC Admin</strong>
			<span>Management Panel</span>
		</div>
		<button class="adm-sidebar-close" type="button" id="adm-sidebar-close"
			aria-label="Close navigation menu">
			<i data-lucide="x" aria-hidden="true"></i>
		</button>
	</div>
	<nav class="adm-nav">
		<?php foreach ( $adminNavItems as $adminNavKey => $adminNavItem ) :
			$adminNavBadge = (int) ( $adminNavBadges[ $adminNavKey ] ?? 0 );
			?>
<a class="adm-nav-btn<?php echo $adminPage === $adminNavKey ? ' active' : ''; ?>" href="<?php echo htmlspecialchars( $adminNavItem['url'] ); ?>">
				<i data-lucide="<?php echo htmlspecialchars( $adminNavItem['icon'] ); ?>"></i>
				<span class="adm-nav-label"><?php echo htmlspecialchars( $adminNavItem['label'] ); ?></span>
				<?php if ( isset( $adminNavBadges[ $adminNavKey ] ) ) : ?>
					<span class="adm-nav-badge<?php echo $adminNavBadge > 0 ? '' : ' d-none'; ?>"
						data-admin-badge="<?php echo htmlspecialchars( $adminNavKey ); ?>"
						title="<?php echo $adminNavBadge; ?> item<?php echo $adminNavBadge === 1 ? '' : 's'; ?> waiting for review">
						<?php echo ($adminNavKey === 'orders' && $adminNavBadge > 0) ? 'New' : $adminNavBadge; ?>
					</span>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
		<a class="adm-nav-btn adm-nav-logout" id="adm-logout-btn" href="#">
			<i data-lucide="log-out"></i> <span class="adm-nav-label">Logout</span>
		</a>
	</nav>
</aside>