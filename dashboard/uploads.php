<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/panel-guard.php';

$panelPage = 'uploads';

$pageTitle       = 'My Uploads - BDC Music Studio';
$metaDescription = 'View and download the files you have uploaded with your BDC Music Studio orders.';
$currentPage     = 'dashboard/uploads';
include_once __DIR__ . '/../header.php';

$userUploads = array();
try {
    $pdo   = db_connect();
    $fStmt = $pdo->prepare( 'SELECT uf.booking_id, uf.original_name, uf.file_path, uf.mime_type, uf.file_size FROM uploaded_files uf JOIN bookings b ON uf.booking_id = b.booking_id WHERE b.customer_id = :cid ORDER BY uf.uploaded_at DESC' );
    $fStmt->execute( [ ':cid' => $_SESSION['user_id'] ] );
    $userUploads = $fStmt->fetchAll();
} catch ( Exception $e ) {
    $userUploads = array();
}
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

                <div class="panel-tab active" id="tab-uploads">
                    <div class="panel-tab-header">
                        <h2>My Uploads</h2>
                        <p>View and download files you have uploaded with your orders.</p>
                    </div>
                    <div class="panel-table-wrap">
                        <table class="panel-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
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

            </main>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/panel-config.php'; ?>

<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>

<?php include_once __DIR__ . '/../footer.php'; ?>
