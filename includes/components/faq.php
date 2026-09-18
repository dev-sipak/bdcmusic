<?php
/**
 * FAQ section component.
 * Usage: $faqItems = [ ['q' => 'Question?', 'a' => 'Answer.', 'open' => true], ... ];
 *        include __DIR__ . '/components/faq.php';
 */
if ( ! isset( $faqItems ) || ! is_array( $faqItems ) ) {
    return;
}
?>
<div class="faq-wrapper">
    <?php foreach ( $faqItems as $index => $faq ) : ?>
        <details class="faq-item reveal"<?php echo ( $index === 0 && ! empty( $faq['open'] ) ) ? ' open' : ''; ?>>
            <summary class="faq-question">
                <?php echo htmlspecialchars( $faq['q'] ); ?>
                <i class="fa-solid fa-plus"></i>
            </summary>
            <div class="faq-answer">
                <p><?php echo htmlspecialchars( $faq['a'] ); ?></p>
            </div>
        </details>
    <?php endforeach; ?>
</div>
