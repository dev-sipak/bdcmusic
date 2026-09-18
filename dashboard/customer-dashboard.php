<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || (int) $_SESSION['user_role'] !== 1 ) {
    header( 'Location: ' . $basePath . 'login' );
    exit;
}

$userName  = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

$pageTitle       = 'Customer Dashboard - BDC Music Studio';
$metaDescription = 'Manage your orders, track services, update your profile, and view order history from your BDC customer dashboard.';
$currentPage     = 'dashboard/customer-dashboard';
include_once __DIR__ . '/../header.php';

$userOrders = array();
$userPhone  = '';
$userSince  = '';
try {
    $pdo    = db_connect();
    $stmt   = $pdo->prepare( 'SELECT booking_id AS id, service AS service, service AS item, status, DATE_FORMAT(created_at, "%Y-%m-%d") AS date, price AS amount FROM bookings WHERE customer_id = :cid ORDER BY created_at DESC' );
    $stmt->execute( [ ':cid' => $_SESSION['user_id'] ] );
    $userOrders = $stmt->fetchAll();

    $uStmt = $pdo->prepare( 'SELECT mobile, DATE_FORMAT(created_at, "%M %Y") AS member_since FROM users WHERE id = :uid LIMIT 1' );
    $uStmt->execute( [ ':uid' => $_SESSION['user_id'] ] );
    $uRow = $uStmt->fetch();
    if ( $uRow ) {
        $userPhone = $uRow['mobile'] ?? '';
        $userSince = $uRow['member_since'] ?? '';
    }
} catch ( Exception $e ) {
    $userOrders = array();
}
?>

<section class="panel-page">
    <div class="container">
        <div class="panel-layout">

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
                    <button class="panel-nav-btn active" data-tab="profile">
                        <i class="fa-solid fa-user"></i> Profile
                    </button>
                    <button class="panel-nav-btn" data-tab="orders">
                        <i class="fa-solid fa-box"></i> My Orders
                    </button>
                    <button class="panel-nav-btn" data-tab="uploads">
                        <i class="fa-solid fa-cloud-arrow-up"></i> My Uploads
                    </button>
                    <button class="panel-nav-btn" data-tab="history">
                        <i class="fa-solid fa-clock-rotate-left"></i> Order History
                    </button>
                    <button class="panel-nav-btn" data-tab="tracking">
                        <i class="fa-solid fa-truck"></i> Order Tracking
                    </button>
                    <button class="panel-nav-btn" data-tab="details">
                        <i class="fa-solid fa-file-invoice"></i> Order Details
                    </button>
                    <button class="panel-nav-btn panel-nav-logout" id="logout-btn">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </button>
                </nav>
            </aside>

            <main class="panel-main">

                <div class="panel-mobile-toggle">
                    <button type="button" class="btn panel-menu-btn" id="dash-menu-toggle">
                        <i class="fa-solid fa-bars"></i> Menu
                    </button>
                </div>

                <!-- PROFILE TAB -->
                <div class="panel-tab active" id="tab-profile">
                    <div class="panel-tab-header">
                        <h2>My Profile</h2>
                        <p>Manage your personal information and preferences.</p>
                    </div>
                    <div class="panel-profile-card">
                        <div class="panel-profile-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="panel-profile-details">
                            <div class="panel-detail-row">
                                <span class="panel-label">Full Name</span>
                                <span class="panel-value"><?php echo htmlspecialchars( $userName ); ?></span>
                            </div>
                            <div class="panel-detail-row">
                                <span class="panel-label">Email</span>
                                <span class="panel-value"><?php echo htmlspecialchars( $userEmail ); ?></span>
                            </div>
                            <div class="panel-detail-row">
                                <span class="panel-label">Phone</span>
                                <span class="panel-value"><?php echo htmlspecialchars( $userPhone ?: 'Not provided' ); ?></span>
                            </div>
                            <div class="panel-detail-row">
                                <span class="panel-label">Member Since</span>
                                <span class="panel-value"><?php echo htmlspecialchars( $userSince ?: 'N/A' ); ?></span>
                            </div>
                            <div class="panel-detail-row">
                                <span class="panel-label">Location</span>
                                <span class="panel-value">N/A</span>
                            </div>
                        </div>
                        <button class="btn panel-edit-btn" disabled>
                            <i class="fa-solid fa-pen"></i> Edit Profile
                        </button>
                    </div>
                </div>

                <!-- ORDERS TAB -->
                <div class="panel-tab" id="tab-orders">
                    <div class="panel-tab-header">
                        <h2>My Orders</h2>
                        <p>View and manage your active orders.</p>
                    </div>
                    <div class="panel-filter-bar">
                        <select class="panel-filter-select" id="order-status-filter">
                            <option value="all">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <select class="panel-filter-select" id="order-service-filter">
                            <option value="all">All Services</option>
                            <option value="BDC Artists Marketplace">BDC Artists Marketplace</option>
                            <option value="Audio & Video Services">Audio &amp; Video Services</option>
                            <option value="Digital Music Distribution">Digital Music Distribution</option>
                            <option value="Promotion Services">Promotion Services</option>
                            <option value="Online/Offline Classes">Online/Offline Classes</option>
                            <option value="IPRS Services">IPRS Services</option>
                        </select>
                    </div>
                    <div class="panel-table-wrap">
                        <table class="panel-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Service</th>
                                    <th>Item</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="orders-tbody"></tbody>
                        </table>
                    </div>
                    <p class="panel-empty d-none" id="orders-empty">No orders found matching your filters.</p>
                </div>

                <!-- UPLOADS TAB -->
                <div class="panel-tab" id="tab-uploads">
                    <div class="panel-tab-header">
                        <h2>My Uploads</h2>
                        <p>View and download files you have uploaded with your orders.</p>
                    </div>
                    <div class="panel-table-wrap">
                        <table class="panel-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Service</th>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="uploads-tbody"></tbody>
                        </table>
                    </div>
                    <p class="panel-empty d-none" id="uploads-empty">No uploaded files found.</p>
                </div>

                <!-- ORDER HISTORY TAB -->
                <div class="panel-tab" id="tab-history">
                    <div class="panel-tab-header">
                        <h2>Order History</h2>
                        <p>Browse your completed and past orders.</p>
                    </div>
                    <div class="panel-table-wrap">
                        <table class="panel-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Service</th>
                                    <th>Item</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="history-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- ORDER TRACKING TAB -->
                <div class="panel-tab" id="tab-tracking">
                    <div class="panel-tab-header">
                        <h2>Order Tracking</h2>
                        <p>Track the progress of your current orders in real time.</p>
                    </div>
                    <div class="tracking-list" id="tracking-list"></div>
                </div>

                <!-- ORDER DETAILS TAB -->
                <div class="panel-tab" id="tab-details">
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
                                <span class="panel-label">Item</span>
                                <span class="panel-value" id="detail-item"></span>
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

<script>
var customerDashboardConfig = {
    orders: <?php echo json_encode( $userOrders ); ?>,
    basePath: '<?php echo $basePath; ?>'
};
</script>
<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>

<?php include_once __DIR__ . '/../footer.php'; ?>
