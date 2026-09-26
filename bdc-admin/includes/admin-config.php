<?php
/**
 * Client config for the admin pages.
 *
 * Requires: $adminOrders and $adminFiles (each page supplies only what it needs).
 * Emits adminDashboardConfig, which admin-dashboard.js, admin-artists.js and
 * admin-enquiries.js all read for basePath.
 */
?>
<script>
var adminDashboardConfig = {
    orders: <?php echo json_encode( isset( $adminOrders ) ? $adminOrders : array() ); ?>,
    files: <?php echo json_encode( isset( $adminFiles ) ? $adminFiles : array() ); ?>,
    basePath: <?php echo json_encode( $basePath ); ?>
};
</script>
