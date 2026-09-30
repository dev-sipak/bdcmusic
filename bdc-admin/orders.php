<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/admin-guard.php';

$adminPage = 'orders';

$pageTitle       = 'Orders Management - BDC Music Studio';
$metaDescription = 'View, filter, and manage all customer orders across services from the BDC Music Studio admin panel.';
include_once __DIR__ . '/includes/admin-header.php';

  // Only the service list is needed here: the Orders tab renders its rows from
  // includes/admin/orders-list.php, which paginates server-side. The overview
  // totals that used to be embedded in this page are now computed in SQL on
  // bdc-admin/index.php.
  $services = array();
  try {
	  $pdo = db_connect();
	  $svcStmt = $pdo->query( 'SELECT name AS service FROM services ORDER BY name' );
	  $services = $svcStmt->fetchAll( PDO::FETCH_COLUMN );
  } catch ( Throwable $e ) {
	  app_log( 'admin-orders', 'page data unavailable', $e );
	  $services = array();
  }


$adminScripts = array( 'admin-dashboard.js' );
?>

<section class="adm-page">
	<div class="container-fluid px-6">
		<div class="adm-layout">

			<?php include __DIR__ . '/includes/admin-nav.php'; ?>

			<?php include __DIR__ . '/includes/admin-mobile-bar.php'; ?>

			<main class="adm-main">

				<div class="adm-tab active" id="adm-tab-orders">
					<div class="adm-tab-header">
						<h2>Orders Management</h2>
						<p>View, filter, and manage all customer orders across services.</p>
					</div>

					<div class="adm-filter-bar">
						<select class="adm-filter-select" id="admin-order-status">
							<option value="all">All Statuses</option>
							<option value="pending">Pending</option>
							<option value="processing">Processing</option>
							<option value="hold">Hold</option>
							<option value="delivered">Delivered</option>
							<option value="cancelled">Cancelled</option>
						</select>
						<select class="adm-filter-select" id="admin-order-service">
							<option value="all">All Services</option>
							<?php foreach ( $services as $service ) : ?>
								<option value="<?php echo htmlspecialchars( $service ); ?>">
									<?php echo htmlspecialchars( $service ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<select class="adm-filter-select" id="admin-order-payment">
							<option value="all">All Payments</option>
							<option value="awaiting">Awaiting Payment</option>
							<option value="paid">Paid</option>
							<option value="refunded">Refunded</option>
							<option value="failed">Failed</option>
							<option value="cancelled">Cancelled</option>
						</select>
						<div class="adm-search-wrap">
							<i data-lucide="search"></i>
							<input type="text" id="admin-order-search" placeholder="Search by ID, customer, or phone...">
						</div>
					</div>

					<div class="adm-table-wrap">
						<table class="adm-table">
							<thead>
								<tr>
								<th>Order ID</th>
								<th>Customer</th>
								<th>Service</th>
								<th>Date &amp; Time</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Payment</th>
								<th>Action</th>
								</tr>
							</thead>
							<tbody id="admin-orders-tbody"></tbody>
						</table>
					</div>
					<p class="adm-empty d-none" id="admin-orders-empty">No orders found.</p>
					<div id="admin-orders-pagination"></div>
				</div>

			</main>
		</div>
	</div>

	<?php include __DIR__ . '/includes/order-modal.php'; ?>
</section>

<?php include __DIR__ . '/includes/admin-config.php'; ?>

<?php include_once __DIR__ . '/includes/admin-footer.php'; ?>
