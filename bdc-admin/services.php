<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'services';

$pageTitle       = 'Services & Packages - BDC Music Studio';
$metaDescription = 'Manage the services the studio sells and every package and price shown on the site from the BDC Music Studio admin panel.';
include_once __DIR__ . '/includes/admin-header.php';

$adminScripts = array( 'admin-plans.js' );
?>

<section class="adm-page">
	<div class="container-fluid px-6">
		<div class="adm-layout">

			<?php include __DIR__ . '/includes/admin-nav.php'; ?>

			<?php include __DIR__ . '/includes/admin-mobile-bar.php'; ?>

			<main class="adm-main">

				<div class="adm-tab active" id="adm-tab-services">
					<div class="adm-tab-header">
						<h2>Services &amp; Packages</h2>
						<p>Every price the site shows comes from these two tables, so a new tier or a new rate is published here rather than in the page files.</p>
					</div>

					<div class="adm-filter-bar">
						<button type="button" class="btn" id="adm-add-service-btn">
							<i data-lucide="plus"></i> Add New Service
						</button>
					</div>

					<div class="adm-table-wrap">
						<table class="adm-table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Slug</th>
									<th>Booking</th>
									<th>Price Note</th>
									<th>Packages</th>
									<th>Status</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody id="adm-services-tbody"></tbody>
						</table>
					</div>
					<p class="adm-empty d-none" id="adm-services-empty">No services found.</p>

					<div class="adm-tab-header" style="margin-top:56px;">
						<h2>Packages</h2>
						<p>Each card, rate card, and checkout total on the site is rendered from this list. An enquiry package is quoted instead of sold.</p>
					</div>

					<div class="adm-filter-bar">
						<button type="button" class="btn" id="adm-add-plan-btn">
							<i data-lucide="plus"></i> Add New Package
						</button>
						<select class="adm-filter-select" id="adm-plan-service-filter">
							<option value="0">All Services</option>
						</select>
						<select class="adm-filter-select" id="adm-plan-group-filter">
							<option value="">All Groups</option>
						</select>
						<div class="adm-search-wrap">
							<i data-lucide="search"></i>
							<input type="text" id="adm-plan-search" placeholder="Search package, group, or service...">
						</div>
					</div>

					<div class="adm-table-wrap">
						<table class="adm-table">
							<thead>
								<tr>
									<th>Service</th>
									<th>Group</th>
									<th>Package</th>
									<th>Price</th>
									<th>Price Note</th>
									<th>Flags</th>
									<th>Status</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody id="adm-plans-tbody"></tbody>
						</table>
					</div>
					<p class="adm-empty d-none" id="adm-plans-empty">No packages found.</p>
					<div id="adm-plans-pagination"></div>
				</div>

			</main>
		</div>
	</div>

	<?php include __DIR__ . '/includes/service-modal.php'; ?>
	<?php include __DIR__ . '/includes/plan-modal.php'; ?>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
