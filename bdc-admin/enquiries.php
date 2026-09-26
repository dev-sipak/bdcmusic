<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'enquiries';

$pageTitle       = 'Artist Enquiries - BDC Music Studio';
$metaDescription = 'View and manage all artist enquiries from the BDC Music Studio admin panel.';
include_once __DIR__ . '/includes/admin-header.php';

$adminScripts = array( 'admin-enquiries.js' );
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

                <div class="adm-tab active" id="adm-tab-enquiries">
                    <div class="adm-tab-header">
                        <h2>Artist Enquiries</h2>
                        <p>View and manage all artist enquiries from the frontend.</p>
                    </div>
                    <div class="adm-filter-bar">
                        <select class="adm-filter-select" id="enquiry-status-filter">
                            <option value="all">All Statuses</option>
                            <option value="new">New</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                        </select>
                    </div>
                    <div class="adm-table-wrap">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="adm-enquiries-tbody"></tbody>
                        </table>
                    </div>
                    <p class="adm-empty d-none" id="adm-enquiries-empty">No enquiries found.</p>
                    <div id="adm-enquiries-pagination"></div>
                </div>

            </main>
        </div>
    </div>

    <?php include __DIR__ . '/includes/reply-modal.php'; ?>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
