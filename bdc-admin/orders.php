<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'orders';

$pageTitle       = 'Orders Management - BDC Music Studio';
$metaDescription = 'View, filter, and manage all customer orders across services from the BDC Music Studio admin panel.';
include_once __DIR__ . '/includes/admin-header.php';

// The order detail modal reads from these in-memory lists.
$adminOrders = array();
$adminFiles  = array();
$services    = array();
try {
    $pdo  = db_connect();
    $stmt = $pdo->query(
        'SELECT b.booking_id AS id, u.name AS customer, u.email AS email, u.mobile AS phone,
                s.name AS service, s.name AS item, b.status,
                DATE_FORMAT(b.created_at, "%Y-%m-%d") AS date,
                b.price AS amount
         FROM bookings b
         LEFT JOIN users u ON b.customer_id = u.id
         JOIN services s ON b.service_id = s.id
         ORDER BY b.created_at DESC'
    );
    $adminOrders = $stmt->fetchAll();

    $fStmt = $pdo->query( 'SELECT booking_id, original_name, file_path, mime_type, file_size FROM uploaded_files ORDER BY uploaded_at DESC' );
    $adminFiles = $fStmt->fetchAll();

    $svcStmt = $pdo->query( 'SELECT name AS service FROM services ORDER BY name' );
    $services = $svcStmt->fetchAll( PDO::FETCH_COLUMN );
} catch ( Exception $e ) {
    $adminOrders = array();
    $adminFiles  = array();
    $services    = array();
}

$adminScripts = array( 'admin-dashboard.js' );
?>

<section class="adm-page">
    <div class="container-fluid px-6">
        <div class="adm-layout">

            <?php include __DIR__ . '/includes/admin-nav.php'; ?>

            <main class="adm-main">

                <div class="adm-mobile-toggle">
                    <button type="button" class="btn adm-menu-btn" id="admin-menu-toggle">
                        <i class="fa-solid fa-bars"></i> Menu
                    </button>
                </div>

                <div class="adm-tab active" id="adm-tab-orders">
                    <div class="adm-tab-header">
                        <h2>Orders Management</h2>
                        <p>View, filter, and manage all customer orders across services.</p>
                    </div>

                    <div class="adm-filter-bar">
                        <select class="adm-filter-select" id="admin-order-status">
                            <option value="all">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="hold">Hold</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select class="adm-filter-select" id="admin-order-service">
                            <option value="all">All Services</option>
                            <?php foreach ( $services as $service ) : ?>
                                <option value="<?php echo htmlspecialchars( $service ); ?>">
                                    <?php echo htmlspecialchars( $service ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="adm-search-wrap">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" id="admin-order-search" placeholder="Search by ID, customer, or phone...">
                        </div>
                    </div>

                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="admin-orders-tbody"></tbody>
                        </table>
                    </div>
                    <p class="adm-empty d-none" id="admin-orders-empty">No orders found.</p>
                    <div id="admin-orders-pagination"></div>
                </div>

            </main>
        </div>
    </div>

    <?php include __DIR__ . '/includes/order-modal.php'; ?>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
