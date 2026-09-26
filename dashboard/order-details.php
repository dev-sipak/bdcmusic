<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/panel-guard.php';

$panelPage = 'details';

$pageTitle       = 'Order Details - BDC Music Studio';
$metaDescription = 'Select an order to view its full details.';
$currentPage     = 'dashboard/order-details';
include_once __DIR__ . '/../header.php';

// Optional ?id= preselects an order on first render.
$initialOrderId = isset( $_GET['id'] ) ? trim( strip_tags( (string) $_GET['id'] ) ) : '';
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

                <div class="panel-tab active" id="tab-details">
                    <div class="panel-tab-header">
                        <h2>Order Details</h2>
                        <p>Select an order to view its full details.</p>
                    </div>
                    <div class="panel-filter-bar">
                        <select class="panel-filter-select" id="details-order-select">
                            <option value="">-- Select an Order --</option>
                        </select>
                    </div>
                    <div class="panel-order-detail d-none" id="order-detail-view">
                        <div class="detail-header">
                            <h3 id="detail-order-id"></h3>
                            <span class="panel-status-badge" id="detail-order-status"></span>
                        </div>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="panel-label">Service</span>
                                <span class="panel-value" id="detail-service"></span>
                            </div>
                            <div class="detail-item">
                                <span class="panel-label">Date</span>
                                <span class="panel-value" id="detail-date"></span>
                            </div>
                            <div class="detail-item">
                                <span class="panel-label">Amount</span>
                                <span class="panel-value" id="detail-amount"></span>
                            </div>
                        </div>
                        <div class="detail-timeline" id="detail-timeline"></div>
                    </div>
                </div>

            </main>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/panel-config.php'; ?>

<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>

<?php include_once __DIR__ . '/../footer.php'; ?>
