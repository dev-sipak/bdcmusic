<?php
/**
 * Breadcrumb component.
 * Usage: include __DIR__ . '/components/breadcrumb.php'; (with $breadcrumbItems defined)
 * $breadcrumbItems = [ ['label' => 'Home', 'url' => '/'], ['label' => 'Services', 'url' => '/all-services'], 'Audio & Video Services' ];
 * String items = current page (no link), array items = linked.
 */
if ( ! isset( $breadcrumbItems ) || ! is_array( $breadcrumbItems ) ) {
    return;
}
?>
<div class="breadcrumb">
    <?php foreach ( $breadcrumbItems as $index => $item ) : ?>
        <?php if ( is_string( $item ) ) : ?>
            <span><?php echo htmlspecialchars( $item ); ?></span>
        <?php elseif ( is_array( $item ) ) : ?>
            <a href="<?php echo htmlspecialchars( $item['url'] ); ?>"><?php echo htmlspecialchars( $item['label'] ); ?></a>
        <?php endif; ?>
        <?php if ( $index < count( $breadcrumbItems ) - 1 ) : ?>
            <span>/</span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
