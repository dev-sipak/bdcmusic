<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'verifications';

$pageTitle       = 'Artist Verifications - BDC Music';
$metaDescription = 'Review BDC Artists Marketplace registrations, verify them, and issue artist ID cards and certificates.';
include_once __DIR__ . '/includes/admin-header.php';

// The queue rows come from includes/admin/artist-registrations-list.php, which
// paginates and filters in SQL, so this page needs no data of its own beyond the
// counts behind the status tabs.
$adminScripts = array( 'admin-verifications.js' );
?>
<section class="adm-page">
	<div class="container-fluid px-6">
		<div class="adm-layout">
			<?php include __DIR__ . '/includes/admin-nav.php'; ?>
			<?php include __DIR__ . '/includes/admin-mobile-bar.php'; ?>
			<main class="adm-main">
				<div class="adm-tab active" id="adm-tab-verifications">
					<div class="adm-tab-header">
						<h2>Artist Verifications</h2>
						<p>Marketplace package purchases waiting to be checked. Verifying an artist issues their ID Card and Certificate, which appear in their dashboard.</p>
					</div>
					<div class="adm-filter-bar">
						<select class="adm-filter-select" id="adm-registration-status">
							<option value="pending">Pending Verification</option>
							<option value="verified">Verified</option>
							<option value="rejected">Rejected</option>
							<option value="all">All Statuses</option>
						</select>
						<div class="adm-search-wrap">
							<i data-lucide="search"></i>
							<input type="text" id="adm-registration-search" placeholder="Search by artist, email, order ID or artist ID...">
						</div>
					</div>
					<div class="adm-table-wrap">
						<table class="adm-table">
							<thead>
								<tr>
									<th>Artist</th>
									<th>Category</th>
									<th>Package</th>
									<th>Order</th>
									<th>Submitted</th>
									<th>Artist ID</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id="adm-verifications-tbody"></tbody>
						</table>
					</div>
					<p class="adm-empty d-none" id="adm-verifications-empty">No registrations found.</p>
					<div id="adm-verifications-pagination"></div>
				</div>
			</main>
		</div>
	</div>
	<?php include __DIR__ . '/includes/verification-modal.php'; ?>
</section>
<?php include __DIR__ . '/includes/admin-config.php'; ?>
<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
