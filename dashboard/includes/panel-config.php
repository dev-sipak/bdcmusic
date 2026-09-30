<?php
/**
 * Client config for the customer dashboard pages.
 *
 * Requires: $activeReleaseStatus (releases page only),
 *           $activeServiceSlug   (service page only),
 *           $purchasedServices   (from panel-guard.php).
 * Emits customerDashboardConfig, which customer-dashboard.js and
 * customer-service.js read, and customerReleasesConfig, which
 * customer-releases.js reads.
 */
$activeReleaseStatus = isset( $activeReleaseStatus ) ? $activeReleaseStatus : 'all';
$activeServiceSlug   = isset( $activeServiceSlug ) ? $activeServiceSlug : '';

// Needed for generate_csrf_token(); helpers.php is not otherwise guaranteed to
// be loaded before this config is emitted.
require_once __DIR__ . '/../../includes/helpers.php';
?>
<script>
var customerDashboardConfig = {
	basePath: <?php echo json_encode( $basePath ); ?>,
	csrfToken: <?php echo json_encode( generate_csrf_token() ); ?>,
	passwordMinLength: <?php echo json_encode( PASSWORD_MIN_LENGTH ); ?>,
	updateProfileUrl: <?php echo json_encode( $basePath . 'includes/update-profile' ); ?>,
	changePasswordUrl: <?php echo json_encode( $basePath . 'includes/change-password' ); ?>,
	uploadProfilePicUrl: <?php echo json_encode( $basePath . 'includes/upload-profile-pic' ); ?>,
	orderDetailUrl: <?php echo json_encode( $basePath . 'includes/customer/order-detail' ); ?>,
	services: <?php echo json_encode( array_values( $purchasedServices ) ); ?>,
	activeService: <?php echo json_encode( $activeServiceSlug ); ?>
};
</script>
<script>
var customerReleasesConfig = {
	basePath: <?php echo json_encode( $basePath ); ?>,
	csrfToken: <?php echo json_encode( generate_csrf_token() ); ?>,
	activeTab: <?php echo json_encode( $activeReleaseStatus ); ?>
};
</script>
