<?php
/**
 * Admin mobile chrome: the top bar and the drawer backdrop.
 *
 * The sidebar is a fixed left rail on desktop and an off-canvas drawer on a
 * phone. Rather than repeat the trigger in all seven admin pages, they include
 * this once and let initSidebarToggle() in assets/js/shared.js wire up the
 * burger, the backdrop, the X, the Escape key and the body scroll lock.
 *
 * The burger doubles as the close control, so it carries the markup for both the
 * bars and the X and swaps them on aria-expanded. The backdrop is not given the
 * `hidden` attribute on purpose: CSS drives it with visibility, which both
 * removes it from the tab order and animates, where `hidden` would only ever
 * mean display:none and could never be shown.
 */
?>
<div class="adm-topbar">
	<button class="adm-burger" type="button" id="admin-menu-toggle"
		aria-label="Open navigation menu"
		aria-expanded="false"
		aria-controls="adm-sidebar">
		<i class="adm-burger-bars" data-lucide="menu" aria-hidden="true"></i>
		<i class="adm-burger-close" data-lucide="x" aria-hidden="true"></i>
	</button>
	<div class="adm-topbar-brand">
		<div class="adm-logo">
			<i data-lucide="shield-half" aria-hidden="true"></i>
		</div>
		<span>BDC Admin</span>
	</div>
</div>

<div class="adm-backdrop" id="adm-sidebar-backdrop" aria-hidden="true"></div>

