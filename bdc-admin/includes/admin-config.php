<?php
/**
 * Client config for the admin pages.
 *
 * Requires: $adminOverview (supplied by bdc-admin/index.php only; pages that
 * do not render the overview cards pass nothing and get empty stats). It holds
 * server-side counts plus the ten most recent orders, not the booking history.
 * Emits adminDashboardConfig, which admin-dashboard.js, admin-artists.js and
 * admin-enquiries.js all read for basePath, and adminReleasesConfig, which
 * admin-releases.js reads.
 *
 * Uploaded files are no longer inlined here: the order modal fetches them per
 * order from includes/admin/order-detail.php.
 */

// Needed for generate_csrf_token(); helpers.php is not otherwise part of the
// admin page include chain.
require_once __DIR__ . '/../../includes/helpers.php';
?>
<script>
  var adminDashboardConfig = {
      overview: <?php echo json_encode( isset( $adminOverview ) ? $adminOverview : array( 'stats' => array(), 'recent' => array() ) ); ?>,
      basePath: <?php echo json_encode( $basePath ); ?>,
      csrfToken: <?php echo json_encode( generate_csrf_token() ); ?>
  };
</script>

<?php if ( isset( $adminReleaseStatuses ) ) : ?>
<script>
var adminReleasesConfig = {
    basePath: <?php echo json_encode( $basePath ); ?>,
    csrfToken: <?php echo json_encode( generate_csrf_token() ); ?>,
    statuses: <?php echo json_encode( $adminReleaseStatuses ); ?>,
    types: <?php echo json_encode( $adminReleaseTypes ); ?>,
    activeTab: <?php echo json_encode( $adminActiveReleaseStatus ); ?>
};
</script>
<?php endif; ?>

