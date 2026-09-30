<?php
/**
 * Admin sidebar navigation.
 *
 * Requires: $adminBase (from admin-guard.php), $adminPage.
 * Renders one link per admin section. The active item is resolved
 * server-side from $adminPage.
 */

$adminNavItems = array(
	'overview'  => array( 'label' => 'Dashboard', 'icon' => 'gauge',      'url' => $adminBase ),
	'orders'    => array( 'label' => 'Orders',    'icon' => 'box',             'url' => $adminBase . 'orders' ),
	'releases'  => array( 'label' => 'Releases',  'icon' => 'disc',    'url' => $adminBase . 'releases' ),
	'customers' => array( 'label' => 'Customers', 'icon' => 'users',           'url' => $adminBase . 'customers' ),
	'artists'   => array( 'label' => 'Artists',   'icon' => 'palette',         'url' => $adminBase . 'artists' ),
	'services'  => array( 'label' => 'Services',  'icon' => 'layers',     'url' => $adminBase . 'services' ),
	'enquiries' => array( 'label' => 'Enquiries', 'icon' => 'mail',        'url' => $adminBase . 'enquiries' ),
);
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
		<?php foreach ( $adminNavItems as $adminNavKey => $adminNavItem ) : ?>
			<a class="adm-nav-btn<?php echo $adminPage === $adminNavKey ? ' active' : ''; ?>"
				href="<?php echo htmlspecialchars( $adminNavItem['url'] ); ?>">
				<i data-lucide="<?php echo htmlspecialchars( $adminNavItem['icon'] ); ?>"></i>
				<?php echo htmlspecialchars( $adminNavItem['label'] ); ?>
			</a>
		<?php endforeach; ?>
		<a class="adm-nav-btn adm-nav-logout" id="adm-logout-btn" href="#">
			<i data-lucide="log-out"></i> Logout
		</a>
	</nav>
</aside>
