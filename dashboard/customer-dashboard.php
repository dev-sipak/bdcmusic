<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if ( ! isset( $_SESSION['user_id'] ) || ! isset( $_SESSION['user_role'] ) || $_SESSION['user_role'] !== 'customer' ) {
    header( 'Location: ' . $basePath . 'login' );
    exit;
}

$userName  = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

$pageTitle       = 'Customer Dashboard - BDC Music Studio';
$metaDescription = 'Manage your orders, track services, update your profile, and view order history from your BDC customer dashboard.';
$currentPage     = 'dashboard/customer-dashboard';
include_once __DIR__ . '/../header.php';

$userUploads = array();
$userPhone   = '';
$userSince   = '';
$userPic     = '';
$hasDistribution = false;
try {
    $pdo    = db_connect();

    $fStmt = $pdo->prepare( 'SELECT uf.booking_id, uf.original_name, uf.file_path, uf.mime_type, uf.file_size FROM uploaded_files uf JOIN bookings b ON uf.booking_id = b.booking_id WHERE b.customer_id = :cid ORDER BY uf.uploaded_at DESC' );
    $fStmt->execute( [ ':cid' => $_SESSION['user_id'] ] );
    $userUploads = $fStmt->fetchAll();

    $uStmt = $pdo->prepare( 'SELECT mobile, profile_picture, DATE_FORMAT(created_at, "%M %Y") AS member_since FROM users WHERE id = :uid LIMIT 1' );
    $uStmt->execute( [ ':uid' => $_SESSION['user_id'] ] );
    $uRow = $uStmt->fetch();
    if ( $uRow ) {
        $userPhone = $uRow['mobile'] ?? '';
        $userSince = $uRow['member_since'] ?? '';
        $userPic   = $uRow['profile_picture'] ?? '';
    }

    $dStmt = $pdo->prepare( 'SELECT COUNT(*) FROM bookings WHERE customer_id = :cid AND service_id = 4 AND status IN ("processing","delivered")' );
    $dStmt->execute( [ ':cid' => $_SESSION['user_id'] ] );
    $hasDistribution = (int) $dStmt->fetchColumn() > 0;
} catch ( Exception $e ) {
    $userUploads = array();
}
$userPicUrl = $userPic ? ( $basePath . $userPic ) : '';
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

                    <button class="panel-nav-btn" data-tab="details">
                        <i class="fa-solid fa-file-invoice"></i> Order Details
                    </button>
                    <?php if ( $hasDistribution ) : ?>
                    <button class="panel-nav-btn" data-tab="releases">
                        <i class="fa-solid fa-compact-disc"></i> My Releases
                    </button>
                    <?php endif; ?>
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

                    <div class="auth-notice notice success d-none" id="profile-success">
                        <i class="fa-solid fa-check-circle"></i>
                        <span id="profile-success-msg"></span>
                    </div>
                    <div class="auth-notice notice error d-none" id="profile-error">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <span id="profile-error-msg"></span>
                    </div>

                    <div class="panel-profile-card">
                        <div class="panel-avatar-wrap">
                            <?php if ( $userPicUrl ) : ?>
                                <img src="<?php echo htmlspecialchars( $userPicUrl ); ?>" alt="Profile" class="panel-avatar-img" id="profile-avatar-img">
                            <?php else : ?>
                                <div class="panel-avatar-img panel-avatar-default" id="profile-avatar-img">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <label class="panel-avatar-upload" id="avatar-upload-label" title="Change profile picture">
                                <i class="fa-solid fa-camera"></i>
                                <input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/webp" hidden>
                            </label>
                        </div>
                        <div class="panel-avatar-status d-none" id="avatar-status"></div>
                        <form id="profile-form" class="panel-profile-details" novalidate>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="profile-name">Full Name</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-user" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="text" id="profile-name" class="panel-input" style="padding-left:34px;" value="<?php echo htmlspecialchars( $userName ); ?>" required>
                                </div>
                            </div>
                            <div class="panel-detail-row">
                                <label class="panel-label">Email</label>
                                <span class="panel-value"><?php echo htmlspecialchars( $userEmail ); ?></span>
                            </div>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="profile-phone">Phone</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-phone" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="tel" id="profile-phone" class="panel-input" style="padding-left:34px;" value="<?php echo htmlspecialchars( $userPhone ); ?>" placeholder="Enter phone number" pattern="[0-9]{10,15}">
                                </div>
                            </div>
                            <div class="panel-detail-row">
                                <span class="panel-label">Member Since</span>
                                <span class="panel-value"><?php echo htmlspecialchars( $userSince ?: 'N/A' ); ?></span>
                            </div>
                            <button type="submit" class="btn panel-edit-btn" id="profile-save-btn">
                                Save Changes
                            </button>
                        </form>
                    </div>

                    <div class="panel-profile-card" style="margin-top:1.5rem;">
                        <h3 style="margin-bottom:1rem;"><i class="fa-solid fa-lock"></i> Change Password</h3>
                        <div class="auth-notice notice success d-none" id="pass-success">
                            <i class="fa-solid fa-check-circle"></i>
                            <span id="pass-success-msg"></span>
                        </div>
                        <div class="auth-notice notice error d-none" id="pass-error">
                            <i class="fa-solid fa-exclamation-circle"></i>
                            <span id="pass-error-msg"></span>
                        </div>
                        <form id="password-form" class="panel-profile-details" novalidate>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="current-password">Current Password</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-lock" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="password" id="current-password" class="panel-input" style="padding-left:34px;" placeholder="Enter current password" required>
                                </div>
                            </div>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="new-password">New Password</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-lock" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="password" id="new-password" class="panel-input" style="padding-left:34px;" placeholder="Min 6 characters" required minlength="6">
                                </div>
                            </div>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="confirm-password">Confirm New Password</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-lock" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="password" id="confirm-password" class="panel-input" style="padding-left:34px;" placeholder="Re-enter new password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn panel-edit-btn" id="pass-save-btn">
                                Update Password
                            </button>
                        </form>
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
                            <option value="Hold">Hold</option>
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
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="orders-tbody"></tbody>
                        </table>
                    </div>
                    <p class="panel-empty d-none" id="orders-empty">No orders found matching your filters.</p>
                    <div id="orders-pagination"></div>
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
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="history-tbody">                            </tbody>
                        </table>
                    </div>
                    <div id="history-pagination"></div>
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

                <!-- RELEASES TAB -->
                <?php if ( $hasDistribution ) : ?>
                <div class="panel-tab" id="tab-releases">
                    <div id="releases-list-panel">
                        <div class="panel-tab-header">
                            <h2>My Releases</h2>
                            <p>Manage your digital music releases.</p>
                        </div>
                        <div class="panel-filter-bar">
                            <button type="button" class="release-tab-btn active" data-status="all">All</button>
                            <button type="button" class="release-tab-btn" data-status="draft">Draft</button>
                            <button type="button" class="release-tab-btn" data-status="pending">Pending</button>
                            <button type="button" class="release-tab-btn" data-status="live">Live</button>
                            <button type="button" class="release-tab-btn" data-status="rejected">Rejected</button>
                        </div>
                        <div class="panel-table-wrap">
                            <table class="panel-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>ISRC</th>
                                        <th>Go Live</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="releases-tbody"></tbody>
                            </table>
                        </div>
                        <p class="panel-empty d-none" id="releases-empty">No releases found.</p>
                        <div id="releases-pagination"></div>
                    </div>

                    <div class="panel-tab d-none" id="release-detail-panel">
                        <div class="panel-tab-header">
                            <h2>Release Details</h2>
                            <p><button type="button" class="btn" id="back-to-releases" style="font-size:0.82rem;padding:4px 10px;"><i class="fa-solid fa-arrow-left"></i> Back</button></p>
                        </div>
                        <div class="panel-profile-card">
                            <div class="detail-grid">
                                <div class="detail-item"><span class="panel-label">Title</span><span class="panel-value" id="rel-detail-title"></span></div>
                                <div class="detail-item"><span class="panel-label">Type</span><span class="panel-value" id="rel-detail-type"></span></div>
                                <div class="detail-item"><span class="panel-label">ISRC</span><span class="panel-value" id="rel-detail-isrc"></span></div>
                                <div class="detail-item"><span class="panel-label">UPC</span><span class="panel-value" id="rel-detail-upc"></span></div>
                                <div class="detail-item"><span class="panel-label">Go Live Date</span><span class="panel-value" id="rel-detail-golive"></span></div>
                                <div class="detail-item"><span class="panel-label">Status</span><span id="rel-detail-status"></span></div>
                            </div>
                            <div style="margin-top:16px;"><span class="panel-label">Artists</span><div id="rel-detail-artists" style="margin-top:6px;"></div></div>
                            <div style="margin-top:16px;"><span class="panel-label">Lyrics</span><pre id="rel-detail-lyrics" style="white-space:pre-wrap;font-size:0.85rem;background:var(--secondary);padding:12px;border-radius:8px;margin-top:6px;max-height:200px;overflow-y:auto;"></pre></div>
                            <div style="margin-top:16px;"><span class="panel-label">History</span><div id="rel-detail-history" style="margin-top:6px;"></div></div>
                        </div>
                    </div>
                </div>

                <!-- CREATE RELEASE FORM -->
                <div class="panel-tab" id="tab-create-release" style="display:none;">
                    <div class="panel-tab-header">
                        <h2>Create New Release</h2>
                        <p>Submit a new music release for distribution.</p>
                    </div>
                    <form id="create-release-form" class="panel-profile-details" novalidate>
                        <div class="panel-detail-row">
                            <label class="panel-label" for="rel-title">Title <span style="color:red;">*</span></label>
                            <div style="position:relative;flex:1;">
                                <i class="fa-solid fa-music" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                <input type="text" id="rel-title" class="panel-input" style="padding-left:34px;" required>
                            </div>
                        </div>
                        <div class="panel-detail-row">
                            <label class="panel-label" for="rel-type">Type</label>
                            <div style="position:relative;flex:1;">
                                <i class="fa-solid fa-tag" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                <select id="rel-type" class="panel-input" style="padding-left:34px;">
                                    <option value="single">Single</option>
                                    <option value="ep">EP</option>
                                    <option value="album">Album</option>
                                </select>
                            </div>
                        </div>
                        <div class="panel-detail-row">
                            <label class="panel-label" for="rel-isrc">ISRC</label>
                            <div style="position:relative;flex:1;">
                                <i class="fa-solid fa-link" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                <input type="text" id="rel-isrc" class="panel-input" style="padding-left:34px;" placeholder="Auto-assigned if blank">
                            </div>
                        </div>
                        <div class="panel-detail-row">
                            <label class="panel-label" for="rel-golive">Go Live Date</label>
                            <div style="position:relative;flex:1;">
                                <i class="fa-solid fa-calendar" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                <input type="date" id="rel-golive" class="panel-input" style="padding-left:34px;">
                            </div>
                        </div>
                        <div class="panel-detail-row">
                            <label class="panel-label" for="rel-lyrics">Lyrics</label>
                            <div style="position:relative;flex:1;">
                                <i class="fa-solid fa-pen-fancy" style="position:absolute;left:10px;top:12px;color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                <textarea id="rel-lyrics" class="panel-input" rows="4" style="padding-left:34px;"></textarea>
                            </div>
                        </div>
                        <div class="panel-detail-row" style="flex-direction:row;gap:20px;">
                            <label class="policy-check"><input type="checkbox" id="rel-dolby"> Dolby Atmos</label>
                            <label class="policy-check"><input type="checkbox" id="rel-apple"> Apple iTunes</label>
                        </div>
                        <div class="panel-detail-row">
                            <label class="panel-label">Artist Credits</label>
                            <div id="release-artists-container"></div>
                            <button type="button" class="btn" id="add-artist-row" style="font-size:0.82rem;padding:6px 12px;margin-top:8px;"><i class="fa-solid fa-plus"></i> Add Artist</button>
                        </div>
                        <button type="submit" class="btn panel-edit-btn" style="margin-top:16px;">Save Release</button>
                    </form>
                </div>
                <?php endif; ?>

            </main>
        </div>
    </div>
</section>

<script>
var customerDashboardConfig = {
    uploads: <?php echo json_encode( $userUploads ); ?>,
    basePath: '<?php echo $basePath; ?>',
    updateProfileUrl: '<?php echo $basePath; ?>includes/update-profile',
    changePasswordUrl: '<?php echo $basePath; ?>includes/change-password',
    uploadProfilePicUrl: '<?php echo $basePath; ?>includes/upload-profile-pic',
    userPicUrl: '<?php echo htmlspecialchars( $userPicUrl ); ?>',
    hasDistribution: <?php echo $hasDistribution ? 'true' : 'false'; ?>
};
</script>
<script>
var customerReleasesConfig = {
    basePath: '<?php echo $basePath; ?>'
};
</script>
<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>
<?php if ( $hasDistribution ) : ?>
<script src="<?php echo $assetPath; ?>js/customer-releases.js"></script>
<?php endif; ?>

<?php include_once __DIR__ . '/../footer.php'; ?>
