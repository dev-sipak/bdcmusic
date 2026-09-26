<?php
/**
 * Client config for the customer dashboard pages.
 *
 * Requires: $userUploads (uploads page only), $activeReleaseStatus
 *           (releases page only). Emits customerDashboardConfig, which
 *           customer-dashboard.js reads, and customerReleasesConfig, which
 *           customer-releases.js reads.
 */
$activeReleaseStatus = isset( $activeReleaseStatus ) ? $activeReleaseStatus : 'all';
?>
<script>
var customerDashboardConfig = {
    uploads: <?php echo json_encode( isset( $userUploads ) ? $userUploads : array() ); ?>,
    basePath: <?php echo json_encode( $basePath ); ?>,
    updateProfileUrl: <?php echo json_encode( $basePath . 'includes/update-profile' ); ?>,
    changePasswordUrl: <?php echo json_encode( $basePath . 'includes/change-password' ); ?>,
    uploadProfilePicUrl: <?php echo json_encode( $basePath . 'includes/upload-profile-pic' ); ?>,
    hasDistribution: <?php echo ! empty( $hasDistribution ) ? 'true' : 'false'; ?>
};
</script>
<script>
var customerReleasesConfig = {
    basePath: <?php echo json_encode( $basePath ); ?>,
    activeTab: <?php echo json_encode( $activeReleaseStatus ); ?>,
    initialOrderId: <?php echo json_encode( isset( $initialOrderId ) ? $initialOrderId : '' ); ?>
};
</script>
