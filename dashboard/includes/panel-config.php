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
?>
<script>
var customerDashboardConfig = {
    basePath: <?php echo json_encode( $basePath ); ?>,
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
    activeTab: <?php echo json_encode( $activeReleaseStatus ); ?>
};
</script>
