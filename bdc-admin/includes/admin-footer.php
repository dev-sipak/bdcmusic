<?php
/**
 * Admin footer.
 *
 * Requires: $adminScripts - array of JS filenames inside assets/js/ to load
 * for the current page. shared.js is always loaded first.
 */
$adminScripts = isset( $adminScripts ) && is_array( $adminScripts ) ? $adminScripts : array();
?>
<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<?php foreach ( $adminScripts as $adminScript ) : ?>
    <script src="<?php echo $assetPath; ?>js/<?php echo htmlspecialchars( $adminScript ); ?>"></script>
<?php endforeach; ?>
</body>

</html>
