<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/brand-icons.php';
?>
<footer class="footer" id="contact">
	<div class="container">
		<div class="footer-grid">
			<div>
				<h4>Quick Links</h4>
				<ul>
					<li><a href="<?php echo $siteUrl; ?>all-services">Our Services</a></li>
					<li><a href="<?php echo $siteUrl; ?>privacy-policy">Privacy Policy</a></li>
					<li><a href="<?php echo $siteUrl; ?>cookie-policy">Cookie Policy</a></li>
					<li><a href="<?php echo $siteUrl; ?>terms-and-conditions">Terms and Conditions</a></li>
					<li><a href="<?php echo $siteUrl; ?>cancel-refund-policy">Cancellation &amp; Refund</a></li>
				</ul>
			</div>
			<div>
				<h4>Explore More</h4>
				<ul>
					<li><a href="https://www.youtube.com/@bdcmusic37" target="_blank">BDC Music</a></li>
					<li><a href="https://www.youtube.com/@bdcbhaktisagar" target="_blank">BDC Bhakti Sagar</a></li>
					<li><a href="https://www.youtube.com/@bdccovesongs" target="_blank">BDC Cove Songs</a></li>
					<li><a href="https://www.youtube.com/@bdcshortmovies" target="_blank">BDC Short Movies</a></li>
					<li><a href="https://www.youtube.com/@bdcshayari" target="_blank">BDC Shayari</a></li>
					<li><a href="https://www.youtube.com/@bdcvlogs" target="_blank">BDC Vlogs</a></li>
					<li><a href="https://www.youtube.com/@bdcmusicclass" target="_blank">BDC Music Classes</a></li>
				</ul>
			</div>
			<div>
				<h4>Contact</h4>
				<p><a href="mailto:info@bdcmusic.in">info@bdcmusic.in</a></p>
				<p><a href="tel:+919599665531">+91 9599665531</a></p>
				<p><a href="tel:+919911144662">+91 9911144662</a></p>
			</div>
			<div>
				<h4>Follow Us</h4>
				<div class="social">
					<a href="https://www.facebook.com/people/BDC-Music/100063503816431/" target="_blank" aria-label="Facebook">
						<?php echo brand_icon( 'facebook' ); ?>
					</a>
					<a href="https://www.instagram.com/invites/contact/?i=27gm4vwyp1ra&utm_content=hzk6tz0" target="_blank" aria-label="Instagram">
						<?php echo brand_icon( 'instagram' ); ?>
					</a>
					<a href="https://www.youtube.com/@bdcmusic37" target="_blank" aria-label="YouTube">
						<?php echo brand_icon( 'youtube' ); ?>
					</a>
					<a href="https://x.com/BDCMusic1?s=08" target="_blank" aria-label="X">
						<?php echo brand_icon( 'x' ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="copyright">&copy; 2026 BDC Music Studio</div>
	</div>
</footer>

<!-- Back to top. Hidden until the visitor scrolls, and kept out of the tab order
	 while it is off screen so a keyboard user never lands on an invisible button.
	 app.js drives .is-visible and keeps aria-hidden in step. -->
<button class="back-to-top" type="button" aria-label="Back to top" aria-hidden="true" tabindex="-1">
	<i data-lucide="arrow-up" aria-hidden="true"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<!-- Lucide has to be on the page before shared.js, which renders every
	 data-lucide placeholder into an inline SVG and keeps watching the DOM for
	 new ones. The build is pinned so an upstream release cannot change an icon
	 out from under a page. -->
<script src="https://cdn.jsdelivr.net/npm/lucide@1.48.0/dist/umd/lucide.min.js"></script>
<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/app.js"></script>
</body>

</html>
