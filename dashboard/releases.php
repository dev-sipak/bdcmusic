<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/panel-guard.php';

// The releases page is the Digital Music Distribution section, so it marks
// itself active with that service's nav key.
$panelPage = 'service:digital-distribution';

// The Releases section is only reachable for customers with an active
// Digital Music Distribution booking (the same gate the nav link uses).
if ( ! $hasDistribution ) {
	header( 'Location: ' . $panelBase . 'customer-dashboard' );
	exit;
}

$pageTitle       = 'My Releases - BDC Music Studio';
$metaDescription = 'Manage your digital music releases.';
$currentPage     = 'dashboard/releases';
include_once __DIR__ . '/../header.php';

// Status filter is a route segment (?status=...) rather than a JS tab switch.
// Every value of the releases.status enum is offered, otherwise a release
// sitting at an intermediate stage (verification, on hold, approved) would not
// be reachable from any tab.
$releaseStatusLabels = array(
	'all'          => 'All',
	'draft'        => 'Draft',
	'pending'      => 'Pending',
	'verification' => 'Verification',
	'onhold'       => 'On Hold',
	'approved'     => 'Approved',
	'live'         => 'Live',
	'rejected'     => 'Rejected',
	'takedown'     => 'Taken Down',
);
$activeReleaseStatus = isset( $_GET['status'] ) ? trim( strip_tags( (string) $_GET['status'] ) ) : 'all';
if ( ! array_key_exists( $activeReleaseStatus, $releaseStatusLabels ) ) {
	$activeReleaseStatus = 'all';
}

function release_status_url( $base, $status ) {
	return $status === 'all' ? $base . 'releases' : $base . 'releases?status=' . rawurlencode( $status );
}
?>

<section class="panel-page">
	<div class="container">
		<div class="panel-layout">

			<?php include __DIR__ . '/includes/panel-nav.php'; ?>

			<main class="panel-main">

				<div class="panel-mobile-toggle">
					<button type="button" class="btn panel-menu-btn" id="dash-menu-toggle">
						<i data-lucide="menu"></i> Menu
					</button>
				</div>

				<div class="panel-tab active" id="tab-releases">
					<div id="releases-list-panel">
						<div class="panel-tab-header">
							<h2>My Releases</h2>
							<p>Manage your digital music releases.</p>
						</div>
						<div class="panel-filter-bar">
							<?php
							foreach ( $releaseStatusLabels as $releaseStatusKey => $releaseStatusLabel ) :
								?>
								<a class="release-tab-btn<?php echo $activeReleaseStatus === $releaseStatusKey ? ' active' : ''; ?>"
									data-status="<?php echo htmlspecialchars( $releaseStatusKey ); ?>"
									href="<?php echo htmlspecialchars( release_status_url( $panelBase, $releaseStatusKey ) ); ?>">
									<?php echo htmlspecialchars( $releaseStatusLabel ); ?>
								</a>
							<?php endforeach; ?>
						</div>
						<div class="panel-table-wrap">
							<table class="panel-table">
								<thead>
								<tr>
									<th>Title</th>
									<th>Type</th>
									<th>Tracks</th>
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
							<p><button type="button" class="btn" id="back-to-releases" style="font-size:0.82rem;padding:4px 10px;"><i data-lucide="arrow-left"></i> Back</button></p>
						</div>
						<div class="panel-profile-card">
							<div class="detail-header">
								<h3 id="rel-detail-title"></h3>
								<span id="rel-detail-status"></span>
							</div>
							<div class="detail-grid">
								<div class="detail-item"><span class="panel-label">Type</span><span class="panel-value" id="rel-detail-type"></span></div>
								<div class="detail-item"><span class="panel-label">ISRC</span><span class="panel-value" id="rel-detail-isrc"></span></div>
								<div class="detail-item"><span class="panel-label">UPC</span><span class="panel-value" id="rel-detail-upc"></span></div>
								<div class="detail-item"><span class="panel-label">Go Live Date</span><span class="panel-value" id="rel-detail-golive"></span></div>
							</div>
							<div style="margin-top:16px;"><span class="panel-label">Artists</span><div id="rel-detail-artists" style="margin-top:6px;"></div></div>
						</div>

						<div class="panel-profile-card" style="margin-top:1.5rem;">
							<h3><i data-lucide="list-music"></i> Tracks</h3>
							<p class="panel-note" id="rel-tracks-note"></p>
							<div class="panel-table-wrap">
								<table class="panel-table">
									<thead>
										<tr>
											<th>#</th>
											<th>Track Title</th>
											<th>ISRC</th>
											<th>Duration</th>
										</tr>
									</thead>
									<tbody id="rel-tracks-tbody"></tbody>
								</table>
							</div>
						</div>

						<div class="panel-profile-card" style="margin-top:1.5rem;">
							<h3><i data-lucide="link"></i> Live Links</h3>
							<p class="panel-note">Platform links are added by the BDC Music team once your release is live.</p>
							<div class="panel-link-list" id="rel-links-list"></div>
						</div>

						<div class="panel-profile-card" style="margin-top:1.5rem;">
							<h3><i data-lucide="history"></i> History</h3>
							<div id="rel-detail-history" style="margin-top:10px;"></div>
						</div>

						<div class="panel-profile-card" style="margin-top:1.5rem;">
							<h3><i data-lucide="feather"></i> Lyrics</h3>
							<pre id="rel-detail-lyrics" style="white-space:pre-wrap;font-size:0.85rem;background:var(--secondary);padding:12px;border-radius:8px;margin-top:10px;max-height:200px;overflow-y:auto;"></pre>
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
								<i data-lucide="music" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
								<input type="text" id="rel-title" class="panel-input" style="padding-left:34px;" required>
							</div>
						</div>
						<div class="panel-detail-row">
							<label class="panel-label" for="rel-type">Type</label>
							<div style="position:relative;flex:1;">
								<i data-lucide="tag" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
								<select id="rel-type" class="panel-input" style="padding-left:34px;">
									<option value="single">Single</option>
									<option value="album">Album</option>
								</select>
							</div>
						</div>
						<div class="panel-detail-row">
							<label class="panel-label" for="rel-isrc">ISRC</label>
							<div style="position:relative;flex:1;">
								<i data-lucide="link" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
								<input type="text" id="rel-isrc" class="panel-input" style="padding-left:34px;" placeholder="Auto-assigned if blank">
							</div>
						</div>
						<div class="panel-detail-row">
							<label class="panel-label" for="rel-golive">Go Live Date</label>
							<div style="position:relative;flex:1;">
								<i data-lucide="calendar" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
								<input type="date" id="rel-golive" class="panel-input" style="padding-left:34px;">
							</div>
						</div>
						<div class="panel-detail-row">
							<label class="panel-label" for="rel-lyrics">Lyrics</label>
							<div style="position:relative;flex:1;">
								<i data-lucide="feather" style="position:absolute;left:10px;top:12px;color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
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
							<button type="button" class="btn" id="add-artist-row" style="font-size:0.82rem;padding:6px 12px;margin-top:8px;"><i data-lucide="plus"></i> Add Artist</button>
						</div>
						<button type="submit" class="btn panel-edit-btn" style="margin-top:16px;">Save Release</button>
					</form>
				</div>

			</main>
		</div>
	</div>
</section>

<?php include __DIR__ . '/includes/panel-config.php'; ?>

<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-releases.js"></script>

<?php include_once __DIR__ . '/../footer.php'; ?>
