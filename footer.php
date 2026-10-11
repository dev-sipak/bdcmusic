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
					<li><a href="https://www.youtube.com/@bdcmusic37" target="_blank" rel="noopener">BDC Music</a></li>
					<li><a href="https://www.youtube.com/@bdcbhaktisagar" target="_blank" rel="noopener">BDC Bhakti Sagar</a></li>
					<li><a href="https://www.youtube.com/@bdccovesongs" target="_blank" rel="noopener">BDC Cove Songs</a></li>
					<li><a href="https://www.youtube.com/@bdcshortmovies" target="_blank" rel="noopener">BDC Short Movies</a></li>
					<li><a href="https://www.youtube.com/@bdcshayari" target="_blank" rel="noopener">BDC Shayari</a></li>
					<li><a href="https://www.youtube.com/@bdcvlogs" target="_blank" rel="noopener">BDC Vlogs</a></li>
					<li><a href="https://www.youtube.com/@bdcmusicclasses" target="_blank" rel="noopener">BDC Music Classes</a></li>
				</ul>
			</div>
			<div>
				<h4>Contact</h4>
				<ul>
					<li><i data-lucide="mail" aria-hidden="true"></i> <a href="mailto:info@bdcmusic.in">info@bdcmusic.in</a></li>
					<li><i data-lucide="phone" aria-hidden="true"></i> <a href="tel:+919599665531">+91 9599665531</a></li>
					<li><i data-lucide="phone" aria-hidden="true"></i> <a href="tel:+919911144662">+91 9911144662</a></li>
				</ul>
			</div>
			<div>
				<h4>Follow Us</h4>
				<div class="social">
					<a href="https://www.facebook.com/people/BDC-Music/100063503816431/" target="_blank" rel="noopener" aria-label="Facebook"><?php echo brand_icon('facebook'); ?></a>
					<a href="https://www.instagram.com/invites/contact/?i=27gm4vwyp1ra&utm_content=hzk6tz0" target="_blank" rel="noopener" aria-label="Instagram"><?php echo brand_icon('instagram'); ?></a>
					<a href="https://www.youtube.com/@bdcmusic37" target="_blank" rel="noopener" aria-label="YouTube"><?php echo brand_icon('youtube'); ?></a>
				</div>
			</div>
		</div>
		<div class="copyright">
			&copy; <?php echo date('Y'); ?> BDC Music
		</div>
	</div>
</footer>

<button class="back-to-top" type="button" aria-label="Back to top" aria-hidden="true" tabindex="-1">
	<i data-lucide="arrow-up" aria-hidden="true"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lucide@1.48.0/dist/umd/lucide.min.js"></script>
<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/app.js"></script>
