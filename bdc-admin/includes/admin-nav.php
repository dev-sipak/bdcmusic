<?php
/**
 * Admin sidebar navigation.
 *
 * Requires: $adminBase (from admin-guard.php), $adminPage.
 * Renders one link per admin section. The active item is resolved
 * server-side from $adminPage.
 */

$adminNavItems = array(
    'overview'  => array( 'label' => 'Dashboard', 'icon' => 'fa-gauge-high',      'url' => $adminBase ),
    'orders'    => array( 'label' => 'Orders',    'icon' => 'fa-box',             'url' => $adminBase . 'orders' ),
    'customers' => array( 'label' => 'Customers', 'icon' => 'fa-users',           'url' => $adminBase . 'customers' ),
    'artists'   => array( 'label' => 'Artists',   'icon' => 'fa-palette',         'url' => $adminBase . 'artists' ),
    'enquiries' => array( 'label' => 'Enquiries', 'icon' => 'fa-envelope',        'url' => $adminBase . 'enquiries' ),
);
?>
<aside class="adm-sidebar">
    <div class="adm-brand">
        <div class="adm-logo">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="adm-brand-text">
            <strong>BDC Admin</strong>
            <span>Management Panel</span>
        </div>
    </div>
    <nav class="adm-nav">
        <?php foreach ( $adminNavItems as $adminNavKey => $adminNavItem ) : ?>
            <a class="adm-nav-btn<?php echo $adminPage === $adminNavKey ? ' active' : ''; ?>"
                href="<?php echo htmlspecialchars( $adminNavItem['url'] ); ?>">
                <i class="fa-solid <?php echo htmlspecialchars( $adminNavItem['icon'] ); ?>"></i>
                <?php echo htmlspecialchars( $adminNavItem['label'] ); ?>
            </a>
        <?php endforeach; ?>
        <a class="adm-nav-btn adm-nav-logout" id="adm-logout-btn" href="#">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </nav>
</aside>
