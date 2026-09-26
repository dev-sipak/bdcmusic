<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'overview';

$pageTitle       = 'Admin Dashboard - BDC Music Studio';
$metaDescription = 'Manage orders, services, and customers from the BDC Music Studio admin dashboard.';
include_once __DIR__ . '/includes/admin-header.php';

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
} catch ( Exception $e ) {
    $adminOrders = array();
    $adminFiles  = array();
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
                                <span class="stat-number" id="stat-total">0</span>
                                <span class="stat-label">Total Orders</span>
                            </div>
                        </div>
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-pending">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-pending">0</span>
                                <span class="stat-label">Pending Orders</span>
                            </div>
                        </div>
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-processing">
                                <i class="fa-solid fa-spinner"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-processing">0</span>
                                <span class="stat-label">Processing Orders</span>
                            </div>
                        </div>
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-delivered">
                                <i class="fa-solid fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-delivered">0</span>
                                <span class="stat-label">Delivered Orders</span>
                            </div>
                        </div>
                        <div class="adm-stat-card">
                            <div class="stat-icon stat-icon-customers">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-number" id="stat-customers">0</span>
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
                                        <th>Phone</th>
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

            </main>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
