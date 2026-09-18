<?php
/**
 * Policy checkbox component.
 * Usage: $policyUrl = $siteUrl; include __DIR__ . '/components/policy-checkbox.php';
 */
if ( ! isset( $policyUrl ) ) {
    global $siteUrl;
    $policyUrl = $siteUrl ?? '/';
}
?>
<label class="policy-check form-field--full">
    <input type="checkbox" name="terms" required>
    <span>I have read the <a href="<?php echo htmlspecialchars( $policyUrl ); ?>privacy-policy">privacy policy</a> and <a href="<?php echo htmlspecialchars( $policyUrl ); ?>terms-and-conditions">terms and conditions</a>.</span>
</label>
