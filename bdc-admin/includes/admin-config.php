<?php
/**
 * Client config for the admin pages.
 *
 * Requires: $adminOrders (each page supplies only what it needs).
 * Emits adminDashboardConfig, which admin-dashboard.js, admin-artists.js and
 * admin-enquiries.js all read for basePath, and adminReleasesConfig, which
 * admin-releases.js reads.
 *
 * Uploaded files are no longer inlined here: the order modal fetches them per
 * order from includes/admin/order-detail.php.
 */
?>
<script>
var adminDashboardConfig = {
    orders: <?php echo json_encode( isset( $adminOrders ) ? $adminOrders : array() ); ?>,
    basePath: <?php echo json_encode( $basePath ); ?>
};
</script>

<?php if ( isset( $adminReleaseStatuses ) ) : ?>
<script>
var adminReleasesConfig = {
    basePath: <?php echo json_encode( $basePath ); ?>,
    statuses: <?php echo json_encode( $adminReleaseStatuses ); ?>,
    types: <?php echo json_encode( $adminReleaseTypes ); ?>,
    activeTab: <?php echo json_encode( $adminActiveReleaseStatus ); ?>
};
</script>
<?php endif; ?>

