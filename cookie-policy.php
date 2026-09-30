<?php
/**
 * Cookie Policy.
 *
 * Follows the same .legal-page markup as privacy-policy.php so it shares one set
 * of styles with the rest of the policy pages.
 *
 * This page deliberately describes two different states, because they are
 * genuinely different:
 *
 *   1. What the site sets today - a single strictly-necessary PHP session cookie.
 *   2. What it will set once Google Analytics (gtag) is switched on.
 *
 * Section 4 is written for the second state and says so plainly. It is not a
 * claim that analytics is running now, because it is not. The gtag snippet is
 * meant to be added in header.php and is marked there and in includes/config.php.
 */
$pageTitle = 'Cookie Policy | BDC Music Studio';
$metaDescription = 'Which cookies BDC Music Studio sets, what each one is for, how to control them in your browser, and how analytics cookies will be used.';
$ogTitle = $pageTitle;
$ogDescription = $metaDescription;
include_once "header.php";
?>
<!-- COOKIE POLICY -->
<section class="legal-page" id="legal">
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo $siteUrl; ?>">Home</a>
			<span>/</span>
			<span>Cookie Policy</span>
		</div>

		<div class="legal-header">
			<span class="section-tag">COOKIE POLICY</span>
			<h1>Cookie Policy</h1>
		</div>

		<div class="legal-content">

			<p class="legal-intro">
				This page explains what cookies and similar storage this website
				uses, what each one is for, and how to control it. It is written to
				be read without legal training, and it sits alongside our
				<a href="<?php echo $siteUrl; ?>privacy-policy">Privacy Policy</a>,
				which covers the personal data behind these cookies.
			</p>

			<div class="legal-section">
				<h3><i data-lucide="cookie"></i> What Cookies Are</h3>
				<p>
					A cookie is a small text file a website asks your browser to
					remember. Browsers store it and send it back on every later visit
					to that site, which is how a site recognises you between page
					loads. The same job is done by &ldquo;web storage&rdquo; &mdash;
					localStorage and sessionStorage &mdash; which is not a cookie but
					behaves similarly, and by pixels or tags embedded by third
					parties.
				</p>
				<p>
					Cookies cannot read your files, cannot reach other websites and
					cannot damage your computer. What they can do is follow you around
					the web, which is why the rules around them matter.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="cookie"></i> Cookies This Website Sets Today</h3>
				<p>
					At the moment this website sets <strong>one</strong> cookie, and
					it is strictly necessary:
				</p>
				<ul class="legal-list">
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>PHP session cookie (PHPSESSID)</strong> &mdash;
						strictly necessary. Keeps you signed in and holds the progress
						of a booking between pages, so you are not asked for your
						details again three steps later. It is set only when you sign
						in, start a booking or submit a form, it holds no tracking
						identifier, and it is deleted when you sign out or when the
						session expires.</span>
					</li>
				</ul>
				<p>
					We do not use advertising cookies, cross-site tracking cookies or
					third-party marketing cookies, and we do not sell or share data
					about your visit.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="chart-line"></i> Analytics Cookies We Will Use</h3>
				<p>
					We intend to add Google Analytics (via gtag.js) to understand which
					pages are useful and which are not, so we can improve the site.
					<strong>That is not switched on yet.</strong> When it is, the
					following will apply, and this section will be updated on the day
					it goes live:
				</p>
				<ul class="legal-list">
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>_ga</strong> &mdash; set by Google Analytics.
						Distinguishes one visitor from another across sessions, so
						visits can be counted rather than treated as one. Expires
						after two years.</span>
					</li>
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>_ga_&lt;container-id&gt;</strong> &mdash; set
						by Google Analytics. Keeps a single visitor's activity
						together within one site so their session is counted once.
						Expires after two years.</span>
					</li>
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>_gid</strong> &mdash; set by Google Analytics.
						Counts visits within a single day. Expires after 24 hours.</span>
					</li>
				</ul>
				<p>
					Analytics of this kind is used to measure page views, referrers and
					general location such as country, never to build a profile of you
					for advertising, and never to see anything you type or any page
					you visit behind a sign-in. Google may use the data as set out in
					its own <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener noreferrer">partner sites policy</a>.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="lock"></i> Cookies Required for the Site to Work</h3>
				<p>
					The session cookie above is not optional. Without it, signing in
					does not stick, and a booking cannot be carried from the package
					step through to payment. Because it carries no tracking
					identifier and is deleted when your session ends, it is treated as
					strictly necessary and does not require consent.
				</p>
				<p>
					If you block it in your browser, the rest of the site will still
					load and you can read every page, but signing in and booking will
					not work.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="sliders-horizontal"></i> How to Control Cookies</h3>
				<p>
					Every major browser lets you see and delete cookies and block the
					ones you would rather not have. In Chrome, Edge or Firefox open
					Settings, then Privacy and Security, then Cookies and site data. In
					Safari, use Preferences &rsaquo; Privacy. From there you can block
					cookies for this site, delete the ones already stored, or turn on
					&ldquo;Do Not Track&rdquo;.
				</p>
				<p>
					Two things worth knowing. First, clearing your cookies signs you
					out of BDC Music Studio, because the session lives in one. Second,
					blocking cookies in your browser is not the same as deleting them:
					some browsers still send the blocked cookies with the requests that
					are essential to a page, which is why the session cookie may
					continue to work even when you have asked for it to be blocked.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="pointer"></i> Do Not Track</h3>
				<p>
					Some browsers send a Do Not Track header asking sites not to track
					visitors. There is no agreed standard for how a site should
					respond, and we cannot guarantee that honouring it is possible.
					Disabling cookies for this site in your browser is the
					dependable way to stop analytics from being set.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="mail"></i> Questions</h3>
				<p>
					If anything here is unclear, or you want to know what is stored
					during your own visit, email
					<a href="mailto:info@bdcmusic.in">info@bdcmusic.in</a> and we will
					help, or explain the data side in more detail in our
					<a href="<?php echo $siteUrl; ?>privacy-policy">Privacy Policy</a>.
				</p>
			</div>

		</div>
	</div>
</section>

<?php
include_once "footer.php";
?>
