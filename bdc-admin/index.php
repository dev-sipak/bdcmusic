<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'overview';

$pageTitle       = 'Admin Dashboard - BDC Music Studio';
$metaDescription = 'Manage orders, services, and customers from the BDC Music Studio admin dashboard.';
include_once __DIR__ . '/includes/admin-header.php';

// The overview needs three things: how many orders sit in each status, how many
// distinct customers have ordered, and the ten most recent orders. Counting in
// SQL keeps the page from loading every booking row and embedding the whole
// history into the HTML, which is what it used to do.
$adminOverview = [
	'stats'  => [ 'total' => 0, 'pending' => 0, 'processing' => 0, 'delivered' => 0, 'customers' => 0 ],
	'recent' => [],
];

try {
	$pdo = db_connect();

	$statusRows = $pdo->query( 'SELECT status, COUNT(*) AS total FROM bookings GROUP BY status' )->fetchAll( PDO::FETCH_KEY_PAIR );

	foreach ( $statusRows as $status => $count ) {
		$adminOverview['stats']['total'] += (int) $count;

		if ( isset( $adminOverview['stats'][ $status ] ) ) {
			$adminOverview['stats'][ $status ] = (int) $count;
		}
	}

	// A booking with no linked user is a guest order, so the email comes off the
	// snapshot on the booking rather than the users table.
	$adminOverview['stats']['customers'] = (int) $pdo->query(
		'SELECT COUNT(DISTINCT COALESCE(NULLIF(b.customer_email, ""), u.email))
         FROM bookings b
         LEFT JOIN users u ON b.customer_id = u.id
         WHERE COALESCE(NULLIF(b.customer_email, ""), u.email) IS NOT NULL'
	)->fetchColumn();

	// Only the ten rows the recent-orders table actually renders.
	// Formatted in SQL rather than in the browser, so the value in this table is
	// the same string the orders list and both order modals show.
	$recent = $pdo->query(
		'SELECT b.booking_id AS id,
                COALESCE(NULLIF(b.customer_name, ""), u.name) AS customer,
                s.name AS service,
                b.plan_name AS item,
                DATE_FORMAT(b.created_at, "%Y-%m-%d %H:%i") AS date,
                b.status, b.price AS amount
         FROM bookings b
         LEFT JOIN users u ON b.customer_id = u.id
         JOIN services s ON b.service_id = s.id
         ORDER BY b.created_at DESC
         LIMIT 10'
	)->fetchAll();

	$adminOverview['recent'] = $recent;
} catch ( Throwable $e ) {
	app_log( 'admin-dashboard', 'page data unavailable', $e );
	$adminOverview = [
		'stats'  => [ 'total' => 0, 'pending' => 0, 'processing' => 0, 'delivered' => 0, 'customers' => 0 ],
		'recent' => [],
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
							<div class="stat-icon stat-icon-total">
								<i data-lucide="box"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-total">0</span>
								<span class="stat-label">Total Orders</span>
							</div>
						</div>
						<div class="adm-stat-card">
							<div class="stat-icon stat-icon-pending">
								<i data-lucide="clock"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-pending">0</span>
								<span class="stat-label">Pending Orders</span>
							</div>
						</div>
						<div class="adm-stat-card">
							<div class="stat-icon stat-icon-processing">
								<i data-lucide="loader-circle"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-processing">0</span>
								<span class="stat-label">Processing Orders</span>
							</div>
						</div>
						<div class="adm-stat-card">
							<div class="stat-icon stat-icon-delivered">
								<i data-lucide="circle-check"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-delivered">0</span>
								<span class="stat-label">Delivered Orders</span>
							</div>
						</div>
						<div class="adm-stat-card">
							<div class="stat-icon stat-icon-customers">
								<i data-lucide="users"></i>
							</div>
							<div class="stat-info">
								<span class="stat-number" id="stat-customers">0</span>
								<span class="stat-label">Customers</span>
							</div>
						</div>
					</div>

					<div class="adm-section">
						<h3>Recent Orders</h3>
						<div class="adm-table-wrap">
							<table class="adm-table">
								<thead>
									<tr>
										<th>Order ID</th>
										<th>Customer</th>
										<th>Service</th>
										<th>Item</th>
										<th>Date &amp; Time</th>
										<th>Amount</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody id="recent-orders-tbody"></tbody>
							</table>
						</div>
					</div>
				</div>

			</main>
		</div>
	</div>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
