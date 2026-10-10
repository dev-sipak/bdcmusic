<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'overview';

$pageTitle       = 'Admin Dashboard - BDC Music';
$metaDescription = 'Manage orders, services, and customers from the BDC Music admin dashboard.';
include_once __DIR__ . '/includes/admin-header.php';

// The four headline figures for the studio: money taken, money returned, the
// size of the artist roster and the size of the release catalogue. Each is one
// aggregate in SQL, so the page never loads the booking rows it is counting.
//
// "Total Sales" is the sum of orders that actually completed a payment
// (payment_status = 'paid'), and "Total Refunded" is reported beside it rather
// than netted off it: a studio reading this page needs to see the refunds as a
// figure in their own right, not hidden inside the sales number.
$adminOverview = [
	'stats' => [
		'sales'    => '0.00',
		'refunded' => '0.00',
		'artists'  => 0,
		'releases' => 0,
	],
];

try {
	$pdo = db_connect();

	$money = $pdo->query(
		"SELECT
			COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.price END), 0) AS sales,
			COALESCE(SUM(CASE WHEN b.payment_status = 'refunded' THEN b.price END), 0) AS refunded
		 FROM bookings b"
	)->fetch();

	$adminOverview['stats']['sales']    = number_format( (float) $money['sales'], 2, '.', '' );
	$adminOverview['stats']['refunded'] = number_format( (float) $money['refunded'], 2, '.', '' );

	// The studio's artist roster and release catalogue, which the Artists and
	// Releases pages manage.
	$adminOverview['stats']['artists'] = (int) $pdo->query(
		'SELECT COUNT(*) FROM artists'
	)->fetchColumn();

	$adminOverview['stats']['releases'] = (int) $pdo->query(
		'SELECT COUNT(*) FROM releases'
	)->fetchColumn();
} catch ( Throwable $e ) {
	app_log( 'admin-dashboard', 'page data unavailable', $e );
	$adminOverview = [
		'stats' => [
			'sales'    => '0.00',
			'refunded' => '0.00',
			'artists'  => 0,
			'releases' => 0,
		],
	];
}

$adminScripts = array( 'admin-dashboard.js' );
?>

<section class="adm-page">
	<div class="container-fluid px-6">
		<div class="adm-layout">

			<?php include __DIR__ . '/includes/admin-nav.php'; ?>

			<?php include __DIR__ . '/includes/admin-mobile-bar.php'; ?>

			<main class="adm-main">

				<div class="adm-tab active" id="adm-tab-overview">
					<div class="adm-tab-header">
						<h2>Dashboard Overview</h2>
						<p>Welcome back. Here is a summary of your store activity.</p>
					</div>

					<div class="adm-stats-grid">
						<div class="adm-stat-card">
							<div class="stat-icon stat-icon-sales">
								<i data-lucide="indian-rupee"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-sales">₹0.00</span>
								<span class="stat-label">Total Sales</span>
							</div>
						</div>
						<div class="adm-stat-card">
							<div class="stat-icon stat-icon-refunded">
								<i data-lucide="undo-2"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-refunded">₹0.00</span>
								<span class="stat-label">Total Refunded</span>
							</div>
						</div>
						<div class="adm-stat-card">
							<div class="stat-icon stat-icon-artists">
								<i data-lucide="users"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-artists">0</span>
								<span class="stat-label">Total Artists Registered</span>
							</div>
						</div>
						<div class="adm-stat-card">
							<div class="stat-icon stat-icon-releases">
								<i data-lucide="disc"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-releases">0</span>
								<span class="stat-label">Total Music Released</span>
							</div>
						</div>
					</div>
				</div>

			</main>
		</div>
	</div>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
