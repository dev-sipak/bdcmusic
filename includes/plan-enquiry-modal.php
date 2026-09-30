<?php
/**
 * The "Enquire Now" modal for a tier that is quoted rather than sold.
 *
 * `service_plans.is_enquiry = 1` means the tier has no price the checkout could
 * charge, so `booking_resolve_selection()` refuses it and a plain "book" link
 * would only ever land the customer on an error. Those tiers get this control
 * instead: the plan id is carried on the button by booking_rate_card(), copied
 * into the form by assets/js/audio-video-services.js, and posted to
 * includes/plan-enquiry-submit.php.
 *
 * The markup reuses `.adm-modal`, which is already in the compiled stylesheet
 * (pages/admin is part of main.scss) and is opened by toggling `.open`, so this
 * needs no new CSS and no rebuild.
 *
 * Requires: includes/helpers.php (url(), csrf_field()).
 */

if ( ! function_exists( 'booking_esc' ) ) {
	require_once __DIR__ . '/booking-registry.php';
}
?>
<div class="adm-modal" id="plan-enquiry-modal" role="dialog" aria-modal="true" aria-labelledby="plan-enquiry-title">
	<div class="adm-modal-backdrop" data-enquiry-close></div>
	<div class="adm-modal-content">
		<div class="adm-modal-header">
			<h3 id="plan-enquiry-title">Enquire about this package</h3>
			<button type="button" class="modal-close-btn" data-enquiry-close aria-label="Close">
				<i data-lucide="x"></i>
			</button>
		</div>

		<div class="adm-modal-body">
			<p id="plan-enquiry-package" style="margin:0 0 4px;font-weight:600;"></p>
			<p style="margin:0 0 16px;color:var(--muted);font-size:0.92rem;">
				Tell us what you need and we will send a quote. There is nothing
				to pay today.
			</p>

			<form id="plan-enquiry-form" method="post" action="<?php echo booking_esc( url( 'includes/plan-enquiry-submit.php' ) ); ?>" novalidate>
				<?php echo csrf_field(); ?>
				<input type="hidden" name="plan_id" id="enquiry-plan-id" value="">

				<div class="modal-detail-grid">
					<div class="modal-detail-item">
						<label class="panel-label" for="enquiry-name">Full Name <span style="color:red;">*</span></label>
						<input class="panel-input" type="text" id="enquiry-name" name="name" autocomplete="name" required>
					</div>

					<div class="modal-detail-item">
						<label class="panel-label" for="enquiry-email">Email <span style="color:red;">*</span></label>
						<input class="panel-input" type="email" id="enquiry-email" name="email" autocomplete="email" required>
					</div>

					<div class="modal-detail-item">
						<label class="panel-label" for="enquiry-phone">Phone <span style="color:red;">*</span></label>
						<input class="panel-input" type="tel" id="enquiry-phone" name="phone" autocomplete="tel" required>
					</div>

					<div class="modal-detail-item">
						<label class="panel-label" for="enquiry-whatsapp">WhatsApp</label>
						<input class="panel-input" type="tel" id="enquiry-whatsapp" name="whatsapp" autocomplete="tel">
					</div>

					<div class="modal-detail-item">
						<label class="panel-label" for="enquiry-deadline">Deadline</label>
						<input class="panel-input" type="text" id="enquiry-deadline" name="deadline" placeholder="e.g. 3 weeks">
					</div>

					<div class="modal-detail-item">
						<label class="panel-label" for="enquiry-budget">Budget</label>
						<input class="panel-input" type="text" id="enquiry-budget" name="budget" placeholder="e.g. Rs.50,000">
					</div>

					<div class="modal-detail-item" style="grid-column:1/-1;">
						<label class="panel-label" for="enquiry-message">What do you need?</label>
						<textarea class="panel-input" id="enquiry-message" name="message" rows="3" placeholder="Scope, deadline, references — anything that helps us price it."></textarea>
					</div>
				</div>

				<div id="plan-enquiry-error" class="notice error" style="display:none;margin-top:16px;"></div>

				<div style="margin-top:20px;text-align:right;">
					<button type="button" class="btn btn-secondary" data-enquiry-close style="margin-right:8px;">Cancel</button>
					<button type="submit" class="btn">Send enquiry</button>
				</div>
			</form>
		</div>
	</div>
</div>
