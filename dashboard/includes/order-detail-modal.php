<!--
  Order Detail Modal

  Opened by the view icon in the My Orders table. Shows everything the
  customer submitted when placing the order plus any arrangements the admin
  has recorded for it. Populated by customer-dashboard.js from
  includes/customer/order-detail.php. Uses the existing adm-modal styles so no
  new CSS is needed for the customer dashboard.
-->
<div class="adm-modal" id="panel-order-modal">
	<div class="adm-modal-backdrop" id="panel-modal-backdrop"></div>
	<div class="adm-modal-content" style="max-width:680px;">
		<div class="adm-modal-header">
			<h3 id="pm-order-id">Order Details</h3>
			<button type="button" class="modal-close-btn" id="pm-close">
				<i data-lucide="x"></i>
			</button>
		</div>
		<div class="adm-modal-body">
			<div class="modal-detail-grid">
				<div class="modal-detail-item">
					<span class="panel-label">Service</span>
					<span class="panel-value" id="pm-service"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Order Date &amp; Time</span>
					<span class="panel-value" id="pm-date"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Amount</span>
					<span class="panel-value" id="pm-amount"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Payment</span>
					<span class="panel-value" id="pm-payment"></span>
				</div>
			</div>

			<div class="modal-files-section" id="pm-status-section">
				<label class="panel-label">Order Status</label>
				<div id="pm-status"></div>
			</div>

			<!--
				What was ordered and charged. These are the package prices
				snapshotted when the order was placed, so they always match the
				amount shown above even if our prices change later.
			-->
			<div class="modal-files-section" id="pm-plan-section">
				<label class="panel-label">What You Ordered</label>
				<div class="detail-grid" id="pm-plan-fields"></div>
			</div>

			<div class="modal-files-section" id="pm-order-fields-section">
				<label class="panel-label">Details You Submitted</label>
				<div class="detail-grid" id="pm-order-fields"></div>
			</div>

			<div class="modal-files-section d-none" id="pm-message-section">
				<label class="panel-label">Your Message</label>
				<p class="panel-note" id="pm-message"></p>
			</div>

			<div class="modal-files-section" id="pm-arrangement-section">
				<label class="panel-label">Arrangements</label>
				<div class="detail-grid" id="pm-arrangement"></div>
				<div id="pm-progress"></div>
				<p class="panel-note d-none" id="pm-arrangement-empty">
					Our team has not added the arrangements for this order yet.
				</p>
				<p class="panel-note d-none" id="pm-locked-note"></p>
			</div>

			<div class="modal-files-section" id="pm-files-section">
				<label class="panel-label">Your Uploads</label>
				<div class="modal-files-list" id="pm-files-list"></div>
			</div>
		</div>
	</div>
</div>
