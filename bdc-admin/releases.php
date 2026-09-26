<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'releases';

$pageTitle       = 'Release Management - BDC Music Studio';
$metaDescription = 'Review customer releases, manage track lists, and publish platform live links from the BDC Music Studio admin panel.';
include_once __DIR__ . '/includes/admin-header.php';

// Drives the status filter tabs and the status select in the editor.
$adminReleaseStatuses = array(
    'draft'        => 'Draft',
    'pending'      => 'Pending',
    'verification' => 'Verification',
    'onhold'       => 'On Hold',
    'rejected'     => 'Rejected',
    'approved'     => 'Approved',
    'live'         => 'Live',
    'takedown'     => 'Taken Down',
);
$adminReleaseTypes = array(
    'single' => 'Single',
    'ep'     => 'EP',
    'album'  => 'Album',
);

// Status filter is a route segment (?status=...) so the view is linkable.
$adminActiveReleaseStatus = isset( $_GET['status'] ) ? trim( strip_tags( (string) $_GET['status'] ) ) : 'all';
if ( $adminActiveReleaseStatus !== 'all' && ! array_key_exists( $adminActiveReleaseStatus, $adminReleaseStatuses ) ) {
    $adminActiveReleaseStatus = 'all';
}

$adminScripts = array( 'admin-releases.js' );
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

                <div class="adm-tab active" id="adm-tab-releases">
                    <div class="adm-tab-header">
                        <h2>Release Management</h2>
                        <p>Review submissions, maintain track lists, and publish the live platform links customers see.</p>
                    </div>

                    <div class="adm-filter-bar">
                        <select class="adm-filter-select" id="admin-release-status">
                            <option value="all">All Statuses</option>
                            <?php foreach ( $adminReleaseStatuses as $admRelKey => $admRelLabel ) : ?>
                                <option value="<?php echo htmlspecialchars( $admRelKey ); ?>"<?php echo $adminActiveReleaseStatus === $admRelKey ? ' selected' : ''; ?>>
                                    <?php echo htmlspecialchars( $admRelLabel ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="adm-search-wrap">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" id="admin-release-search" placeholder="Search by title, ISRC, UPC, customer...">
                        </div>
                    </div>

                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Tracks</th>
                                    <th>ISRC</th>
                                    <th>Go Live</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="admin-releases-tbody"></tbody>
                        </table>
                    </div>
                    <p class="adm-empty d-none" id="admin-releases-empty">No releases found.</p>
                    <div id="admin-releases-pagination"></div>
                </div>

            </main>
        </div>
    </div>

    <?php include __DIR__ . '/includes/release-modal.php'; ?>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
