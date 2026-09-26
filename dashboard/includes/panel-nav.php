<?php
/**
 * Customer dashboard sidebar navigation.
 *
 * Requires: $panelBase (from panel-guard.php), $panelPage, $userName,
 *           $userEmail, $hasDistribution.
 * Renders one link per section. The active item is resolved server-side from
 * $panelPage. The Releases link is only shown for customers with an active
 * Digital Music Distribution booking.
 */

$panelNavItems = array(
    'profile' => array( 'label' => 'Profile',      'icon' => 'fa-user',              'url' => $panelBase . 'customer-dashboard' ),
    'orders'  => array( 'label' => 'My Orders',    'icon' => 'fa-box',               'url' => $panelBase . 'orders' ),
    'uploads' => array( 'label' => 'My Uploads',   'icon' => 'fa-cloud-arrow-up',    'url' => $panelBase . 'uploads' ),
    'history' => array( 'label' => 'Order History','icon' => 'fa-clock-rotate-left', 'url' => $panelBase . 'history' ),
    'details' => array( 'label' => 'Order Details','icon' => 'fa-file-invoice',       'url' => $panelBase . 'order-details' ),
);

if ( $hasDistribution ) {
    $panelNavItems['releases'] = array( 'label' => 'My Releases', 'icon' => 'fa-compact-disc', 'url' => $panelBase . 'releases' );
}
?>
<aside class="panel-sidebar">
    <div class="panel-user">
        <div class="panel-avatar">
            <i class="fa-solid fa-user"></i>
        </div>
        <div class="panel-user-info">
            <strong><?php echo htmlspecialchars( $userName ); ?></strong>
            <span><?php echo htmlspecialchars( $userEmail ); ?></span>
        </div>
    </div>
    <nav class="panel-nav">
        <?php foreach ( $panelNavItems as $panelNavKey => $panelNavItem ) : ?>
            <a class="panel-nav-btn<?php echo $panelPage === $panelNavKey ? ' active' : ''; ?>"
                href="<?php echo htmlspecialchars( $panelNavItem['url'] ); ?>">
                <i class="fa-solid <?php echo htmlspecialchars( $panelNavItem['icon'] ); ?>"></i>
                <?php echo htmlspecialchars( $panelNavItem['label'] ); ?>
            </a>
        <?php endforeach; ?>
        <a class="panel-nav-btn panel-nav-logout" id="logout-btn" href="#">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </nav>
</aside>
