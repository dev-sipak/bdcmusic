<?php
/**
 * Customer dashboard sidebar navigation.
 *
 * Requires: $panelBase (from panel-guard.php), $panelPage, $userName,
 *           $userEmail, $purchasedServices.
 *
 * Renders one link per section. The active item is resolved server-side from
 * $panelPage. One link is emitted per service the customer has actually
 * purchased, so a service they never bought never appears here.
 *
 * The Digital Music Distribution service keeps its existing dedicated page
 * (dashboard/releases.php) rather than sharing the generic service page, so it
 * gets its own URL here.
 *
 * A page marks itself active either with its own $panelPage value or, when it
 * stands in for one of the service sections, with a 'service:<slug>' key. The
 * releases page therefore sets $panelPage = 'service:digital-distribution'.
 */

$panelNavItems = array(
    'profile' => array( 'label' => 'Profile',    'icon' => 'fa-user',           'url' => $panelBase . 'customer-dashboard' ),
);

foreach ( $purchasedServices as $purchasedSlug => $purchasedService ) {
    $panelNavItems[ 'service:' . $purchasedSlug ] = array(
        'label' => $purchasedService['nav'],
        'icon'  => $purchasedService['icon'],
        // A locked service is still listed, because the customer did buy it,
        // but it is flagged so the sidebar can show a padlock and the section
        // itself can explain it is waiting on the team.
        'locked' => ! $purchasedService['unlocked'],
        'url'   => $purchasedSlug === 'digital-distribution'
            ? $panelBase . 'releases'
            : $panelBase . 'service?service=' . rawurlencode( $purchasedSlug ),
    );
}

$panelNavItems['orders'] = array( 'label' => 'My Orders', 'icon' => 'fa-box', 'url' => $panelBase . 'orders' );
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
            <a class="panel-nav-btn<?php echo $panelPage === $panelNavKey ? ' active' : ''; ?><?php
                echo ! empty( $panelNavItem['locked'] ) ? ' is-locked' : ''; ?>"
                href="<?php echo htmlspecialchars( $panelNavItem['url'] ); ?>">
                <i class="fa-solid <?php echo htmlspecialchars( $panelNavItem['icon'] ); ?>"></i>
                <?php echo htmlspecialchars( $panelNavItem['label'] ); ?>
                <?php if ( ! empty( $panelNavItem['locked'] ) ) : ?>
                    <i class="fa-solid fa-lock panel-nav-lock" title="Waiting for the team to confirm this service"></i>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
        <a class="panel-nav-btn panel-nav-logout" id="logout-btn" href="#">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </nav>
</aside>
