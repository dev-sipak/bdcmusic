<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'admin' ) {
    header( 'Location: ' . $basePath . 'login' );
    exit;
}

$pageTitle       = 'Admin Dashboard - BDC Music Studio';
$metaDescription = 'Manage orders, services, and customers from the BDC Music Studio admin dashboard.';
$currentPage     = 'bdc-admin';
include_once __DIR__ . '/includes/admin-header.php';

$adminOrders = array();
$adminFiles  = array();
$services    = array();
try {
    $pdo  = db_connect();
    $stmt = $pdo->query(
        'SELECT b.booking_id AS id, u.name AS customer, u.email AS email,
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
?>

<section class="adm-page">
    <div class="container-fluid px-6">
        <div class="adm-layout">

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
                    <button class="adm-nav-btn active" data-tab="overview">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard
                    </button>
                    <button class="adm-nav-btn" data-tab="orders">
                        <i class="fa-solid fa-box"></i> Orders
                    </button>
                    <button class="adm-nav-btn" data-tab="customers">
                        <i class="fa-solid fa-users"></i> Customers
                    </button>
                    <button class="adm-nav-btn adm-nav-logout" id="adm-logout-btn">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </button>
                </nav>
            </aside>

            <main class="adm-main">

                <div class="adm-mobile-toggle">
                    <button type="button" class="btn adm-menu-btn" id="admin-menu-toggle">
                        <i class="fa-solid fa-bars"></i> Menu
                    </button>
                </div>

                <!-- OVERVIEW TAB -->
                <div class="adm-tab active" id="adm-tab-overview">
                    <div class="adm-tab-header">
                        <h2>Dashboard Overview</h2>
                        <p>Welcome back. Here is a summary of your store activity.</p>
                    </div>

                    <div class="adm-stats-grid">
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-total">
                                <i class="fa-solid fa-box"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-total">10</span>
                                <span class="stat-label">Total Orders</span>
                            </div>
                        </div>
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-pending">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-pending">2</span>
                                <span class="stat-label">Pending Orders</span>
                            </div>
                        </div>
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-processing">
                                <i class="fa-solid fa-spinner"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-processing">2</span>
                                <span class="stat-label">Processing Orders</span>
                            </div>
                        </div>
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-delivered">
                                <i class="fa-solid fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-delivered">3</span>
                                <span class="stat-label">Delivered Orders</span>
                            </div>
                        </div>
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-customers">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-customers">8</span>
                                <span class="stat-label">Customers</span>
                            </div>
                        </div>
                    </div>

                    <div class="adm-section">
                        <h3>Recent Orders</h3>
                        <div class="adm-table-wrap">
                            <table class="adm-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-orders-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ORDERS TAB -->
                <div class="adm-tab" id="adm-tab-orders">
                    <div class="adm-tab-header">
                        <h2>Orders Management</h2>
                        <p>View, filter, and manage all customer orders across services.</p>
                    </div>

                    <div class="adm-filter-bar">
                        <select class="adm-filter-select" id="admin-order-status">
                            <option value="all">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
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
                            <input type="text" id="admin-order-search" placeholder="Search by ID or customer...">
                        </div>
                    </div>

                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
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
                </div>

                <!-- CUSTOMERS TAB -->
                <div class="adm-tab" id="adm-tab-customers">
                    <div class="adm-tab-header">
                        <h2>Customers</h2>
                        <p>View all registered customers and their order history.</p>
                    </div>
                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                    <th>Last Order</th>
                                </tr>
                            </thead>
                            <tbody id="admin-customers-tbody"></tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="adm-modal" id="admin-order-modal">
        <div class="adm-modal-backdrop" id="modal-backdrop"></div>
        <div class="adm-modal-content">
            <div class="adm-modal-header">
                <h3 id="modal-order-id">Order Details</h3>
                <button type="button" class="modal-close-btn" id="modal-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="adm-modal-body">
                <div class="modal-detail-grid">
                    <div class="modal-detail-item">
                        <span class="panel-label">Customer</span>
                        <span class="panel-value" id="modal-customer"></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="panel-label">Email</span>
                        <span class="panel-value" id="modal-email"></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="panel-label">Service</span>
                        <span class="panel-value" id="modal-service"></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="panel-label">Item</span>
                        <span class="panel-value" id="modal-item"></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="panel-label">Date</span>
                        <span class="panel-value" id="modal-date"></span>
                    </div>
                    <div class="modal-detail-item">
                        <span class="panel-label">Amount</span>
                        <span class="panel-value" id="modal-amount"></span>
                    </div>
                </div>
                <div class="modal-files-section" id="modal-files-section">
                    <label class="panel-label">Uploaded Files</label>
                    <div class="modal-files-list" id="modal-files-list"></div>
                </div>
                <div class="modal-status-control">
                    <label class="panel-label">Update Status</label>
                    <select class="adm-filter-select" id="modal-status-select">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="button" class="btn modal-update-btn" id="modal-update-btn">
                        Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
var adminDashboardConfig = {
    orders: <?php echo json_encode( $adminOrders ); ?>,
    files: <?php echo json_encode( $adminFiles ); ?>,
    basePath: '<?php echo $basePath; ?>'
};
</script>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
