<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'customers';

$pageTitle       = 'Customers - BDC Music Studio';
$metaDescription = 'View all registered customers and their order history from the BDC Music Studio admin panel.';
include_once __DIR__ . '/includes/admin-header.php';

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

                <div class="adm-tab active" id="adm-tab-customers">
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
                                    <th>Phone</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                    <th>Last Order</th>
                                </tr>
                            </thead>
                            <tbody id="admin-customers-tbody"></tbody>
                        </table>
                    </div>
                    <div id="admin-customers-pagination"></div>
                </div>

            </main>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
