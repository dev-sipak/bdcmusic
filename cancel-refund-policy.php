<?php
/**
 * Cancellation and Refund Policy.
 *
 * Follows the same .legal-page markup as privacy-policy.php and
 * terms-and-conditions.php so all four policy pages share one set of styles.
 *
 * The status and payment values named in the policy are the ones the booking
 * tables actually carry (bookings.status, bookings.payment_status), so the
 * wording a customer reads matches what the admin panel shows them.
 */
$pageTitle = 'Cancellation & Refund Policy | BDC Music Studio';
$metaDescription = 'How cancellations, refunds and chargebacks work for bookings, studio sessions, classes, distribution and marketplace services at BDC Music Studio.';
$ogTitle = $pageTitle;
$ogDescription = $metaDescription;
include_once "header.php";
?>
<!-- CANCELLATION & REFUND POLICY -->
<section class="legal-page" id="legal">
	<div class="container">
		<div class="breadcrumb">
			<a href="<?php echo $siteUrl; ?>">Home</a>
			<span>/</span>
			<span>Cancellation &amp; Refund Policy</span>
		</div>

		<div class="legal-header">
			<span class="section-tag">CANCELLATION &amp; REFUND</span>
			<h1>Cancellation &amp; Refund Policy</h1>
		</div>

		<div class="legal-content">

			<p class="legal-intro">
				This policy explains how a booking can be cancelled, when a refund
				is due, how long it takes to arrive, and what happens to a booking
				once the work has already been delivered. It applies to every
				service booked through this website &mdash; studio sessions, online
				and offline classes, digital music distribution, promotion, IPRS
				registration and BDC Artist Marketplace memberships &mdash; and to
				the same services booked directly with our team.
			</p>

			<div class="legal-section">
				<h3><i data-lucide="info"></i> Scope and Agreement</h3>
				<p>
					Placing a booking and paying for it forms an agreement between
					you and BDC Music Studio to deliver the service you selected at
					the price you paid. This policy is part of that agreement, and
					it sits alongside our Terms and Conditions and Privacy Policy.
					Where this policy and the Terms conflict, the Terms govern.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="clock"></i> Requesting a Cancellation</h3>
				<p>
					Cancellations must be requested in writing so they can be
					recorded against your booking. Email
					<a href="mailto:info@bdcmusic.in">info@bdcmusic.in</a> with your
					booking or invoice number and the reason for cancelling, or
					speak to us on <a href="tel:+919599665531">+91 9599665531</a>.
					A cancellation is only accepted once you have received written
					confirmation from us; an unanswered message is not a
					cancellation.
				</p>
				<p>
					Because studio time, artists and instructors are reserved for
					you as soon as a booking is confirmed, notice periods apply and
					are set out in the table below.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="list"></i> Notice Periods and What Is Refunded</h3>
				<ul class="legal-list">
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>More than 72 hours before the session or start date.</strong>
						A full refund of everything you paid, with no deduction.</span>
					</li>
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>Between 24 and 72 hours before.</strong>
						A cancellation fee equal to 25% of the amount paid is
						retained, and the remaining 75% is refunded.</span>
					</li>
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>Less than 24 hours before, or no notice at all.</strong>
						The booking is treated as used and no refund is due, because
						the time and the artist or instructor cannot be resold.</span>
					</li>
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>After the service has been delivered.</strong>
						No refund is due. Delivered work &mdash; finished masters,
						distributed releases, completed classes, published
						registrations and live marketplace bookings &mdash; cannot be
						un-delivered.</span>
					</li>
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>If we cancel.</strong>
						If BDC Music Studio cancels your booking for any reason other
						than a breach of these terms by you, you receive a full
						refund of everything you paid, or a free rescheduled
						equivalent at your choice.</span>
					</li>
					<li>
						<span class="list-icon"><i data-lucide="check"></i></span>
						<span><strong>Duplicate or double payments.</strong>
						If you are charged twice for the same booking, tell us and the
						duplicate amount is refunded in full once the payment records
						are matched.</span>
					</li>
				</ul>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="indian-rupee"></i> How Refunds Are Issued</h3>
				<p>
					Refunds are returned by the same method you paid with. If you
					paid by card or UPI through our payment gateway, the amount goes
					back to the original payment instrument &mdash; there is no cash
					refund and no transfer to a different account or person, because
					we cannot verify the ownership of an account we have no record of
					paying. Please make sure the card or UPI ID you use is one you
					can receive money back on.
				</p>
				<p>
					A refund is treated as approved once our team has confirmed it in
					writing and raised it with the payment gateway. Approved refunds
					are typically credited within 5 to 7 working days, but the time
					the money takes to appear is set by your bank or card issuer and
					can be longer. We are not able to speed up a bank.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="gauge"></i> Rescheduling Instead of Cancelling</h3>
				<p>
					Where a cancellation is for a reason other than a breach by you,
					we would much rather reschedule than refund, and a rescheduled
					booking is treated as a new booking with a new start date. A
					reschedule is free of charge if it is requested more than 72 hours
					in advance and is agreed by us. It is subject to availability, so
					the date is only confirmed once we have replied in writing, and a
					booking cannot be rescheduled more than twice.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="triangle-alert"></i> Late Arrival and Missed Sessions</h3>
				<p>
					Sessions run for the length booked. If you arrive late, the
					session ends at the time it was booked to end and is not extended.
					If you miss a session entirely, the session is marked as used and
					the amount paid for it is not refundable, though you may
					reschedule it to another date subject to availability and our
					agreement. Repeated no-shows may lead us to ask for bookings to be
					made in advance or to decline a future booking.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="rotate-ccw"></i> Chargebacks and Disputes</h3>
				<p>
					Please raise a concern with us first. Contact us at
					<a href="mailto:info@bdcmusic.in">info@bdcmusic.in</a> with your
					booking number and we will investigate and respond, normally
					within 7 working days. A chargeback filed with your bank without
					contacting us first slows the process down considerably and can
					take months to resolve, because we are not part of that process
					and only learn about it late. We reserve the right to contest a
					chargeback where the service was delivered as described.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="coins"></i> Third-Party Fees</h3>
				<p>
					Some services carry costs that are set by a third party and passed
					on to you at cost, such as distribution fees charged by streaming
					platforms and store partners, or royalty or licence fees. Once such
					a fee has been paid to the third party it cannot be recovered by
					us and cannot be refunded to you. We will always tell you when a
					quoted price includes such a fee.
				</p>
			</div>

			<div class="legal-section">
				<h3><i data-lucide="file-text"></i> Changes to This Policy</h3>
				<p>
					We may update this policy from time to time. The version that
					applies to your booking is the one that was published when the
					booking was made, so a change never affects a booking you have
					already paid for. Any change is published on this page with an
					updated date.
				</p>
				<p>
					Questions about a cancellation or refund? Email
					<a href="mailto:info@bdcmusic.in">info@bdcmusic.in</a> or call
					<a href="tel:+919911144662">+91 9911144662</a> and we will explain
					where your booking stands.
				</p>
			</div>

		</div>
	</div>
</section>

<?php
include_once "footer.php";
?>
