<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/panel-guard.php';

$panelPage = 'orders';

$pageTitle       = 'My Orders - BDC Music Studio';
$metaDescription = 'View and manage your active BDC Music Studio orders.';
$currentPage     = 'dashboard/orders';
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

                <div class="panel-tab active" id="tab-orders">
                    <div class="panel-tab-header">
                        <h2>My Orders</h2>
                        <p>View and manage your active orders. Use the view icon on a row for the full order details and arrangements.</p>
                    </div>
                    <div class="panel-filter-bar">
                        <select class="panel-filter-select" id="order-status-filter">
                            <option value="all">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="hold">Hold</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select class="panel-filter-select" id="order-service-filter">
                            <option value="all">All Services</option>
                            <?php foreach ( $purchasedServices as $purchasedService ) : ?>
                                <option value="<?php echo htmlspecialchars( $purchasedService['name'] ); ?>">
                                    <?php echo htmlspecialchars( $purchasedService['name'] ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="panel-table-wrap">
                        <table class="panel-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="orders-tbody"></tbody>
                        </table>
                    </div>
                    <p class="panel-empty d-none" id="orders-empty">No orders found matching your filters.</p>
                    <div id="orders-pagination"></div>
                </div>

            </main>
        </div>
    </div>

    <?php include __DIR__ . '/includes/order-detail-modal.php'; ?>
</section>

<?php include __DIR__ . '/includes/panel-config.php'; ?>

<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>

<?php include_once __DIR__ . '/../footer.php'; ?>
