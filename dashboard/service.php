<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/panel-guard.php';

// Which service section is being viewed. The sidebar links here with
// ?service=<services.slug>.
$activeServiceSlug = isset( $_GET['service'] ) ? trim( strip_tags( (string) $_GET['service'] ) ) : '';

// Digital Music Distribution keeps its own page because it is release-centric
// rather than order-centric. Send the customer there instead of rendering an
// order list they do not need.
if ( $activeServiceSlug === 'digital-distribution' ) {
    header( 'Location: ' . $panelBase . 'releases' );
    exit;
}

// A service that is not part of the catalogue, or that this customer never
// bought, has no section. Send them back to their profile rather than
// rendering an empty page.
if ( service_definition( $activeServiceSlug ) === null || ! customer_has_service( $activeServiceSlug ) ) {
    header( 'Location: ' . $panelBase . 'customer-dashboard' );
    exit;
}

$panelPage = 'service:' . $activeServiceSlug;

$pageTitle       = service_nav_label( $activeServiceSlug ) . ' - BDC Music Studio';
$metaDescription = service_blurb( $activeServiceSlug );
$currentPage     = 'dashboard/service';
include_once __DIR__ . '/../header.php';
?>

<section class="panel-page">
    <div class="container">
        <div class="panel-layout">

            <?php include __DIR__ . '/includes/panel-nav.php'; ?>

            <main class="panel-main">

                <div class="panel-mobile-toggle">
                    <button type="button" class="btn panel-menu-btn" id="dash-menu-toggle">
                        <i class="fa-solid fa-bars"></i> Menu
                    </button>
                </div>

                <div class="panel-tab active" id="tab-service">
                    <div class="panel-tab-header">
                        <h2 id="svc-title"><?php echo htmlspecialchars( service_nav_label( $activeServiceSlug ) ); ?></h2>
                        <p id="svc-blurb"><?php echo htmlspecialchars( service_blurb( $activeServiceSlug ) ); ?></p>
                    </div>

                    <div class="panel-loading" id="svc-loading">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading your service details...
                    </div>

                    <div class="d-none" id="svc-body">
                        <p class="panel-empty d-none" id="svc-empty">Nothing here yet.</p>

                        <div id="svc-orders"></div>
                    </div>
                </div>

            </main>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/panel-config.php'; ?>

<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-service.js"></script>

<?php include_once __DIR__ . '/../footer.php'; ?>
