<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'artists';

$pageTitle       = 'Artists Management - BDC Music Studio';
$metaDescription = 'Manage artists, categories, and pricing for the marketplace from the BDC Music Studio admin panel.';
include_once __DIR__ . '/includes/admin-header.php';

$adminScripts = array( 'admin-artists.js' );
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

                <div class="adm-tab active" id="adm-tab-artists">
                    <div class="adm-tab-header">
                        <h2>Artists Management</h2>
                        <p>Manage artists, categories, and pricing for the marketplace.</p>
                    </div>
                    <div class="adm-filter-bar">
                        <button type="button" class="btn" id="adm-add-artist-btn">
                            <i class="fa-solid fa-plus"></i> Add New Artist
                        </button>
                    </div>
                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="adm-artists-tbody"></tbody>
                        </table>
                    </div>
                    <p class="adm-empty d-none" id="adm-artists-empty">No artists found.</p>
                    <div id="adm-artists-pagination"></div>
                </div>

            </main>
        </div>
    </div>

    <?php include __DIR__ . '/includes/artist-modal.php'; ?>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
