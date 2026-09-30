<!-- Order Detail Modal -->
<div class="adm-modal" id="admin-order-modal">
	<div class="adm-modal-backdrop" id="modal-backdrop"></div>
	<div class="adm-modal-content">
		<div class="adm-modal-header">
			<h3 id="modal-order-id">Order Details</h3>
			<button type="button" class="modal-close-btn" id="modal-close">
				<i data-lucide="x"></i>
			</button>
		</div>
		<div class="adm-modal-body">
			<div class="modal-detail-grid">
				<div class="modal-detail-item">
					<span class="panel-label">Customer</span>
					<span class="panel-value" id="modal-customer"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Email</span>
					<span class="panel-value" id="modal-email"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Phone</span>
					<span class="panel-value" id="modal-phone"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Service</span>
					<span class="panel-value" id="modal-service"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Item</span>
					<span class="panel-value" id="modal-item"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Order Date &amp; Time</span>
					<span class="panel-value" id="modal-date"></span>
				</div>
				<div class="modal-detail-item">
					<span class="panel-label">Amount</span>
					<span class="panel-value" id="modal-amount"></span>
				</div>
			</div>

			<!--
				What was actually bought. The package lines are the snapshot
				taken when the order was placed, so they always match the amount
				charged even after catalogue prices change.
			-->
			<div class="modal-files-section" id="modal-plan-section">
				<label class="panel-label">Package Ordered</label>
				<div class="detail-grid" id="modal-plan-fields"></div>
			</div>

			<div class="modal-files-section" id="modal-payment-section">
				<label class="panel-label">Payment</label>
				<div class="detail-grid" id="modal-payment-fields"></div>
			</div>

			<div class="modal-files-section d-none" id="modal-payment-attempts-section">
				<label class="panel-label">Payment Attempts</label>
				<div class="modal-files-list" id="modal-payment-attempts"></div>
			</div>

			<div class="modal-files-section" id="modal-meta-section">
				<label class="panel-label">Details Submitted by Customer</label>
				<div class="detail-grid" id="modal-meta-fields"></div>
			</div>

			<div class="modal-files-section d-none" id="modal-message-section">
				<label class="panel-label">Customer Message</label>
				<p class="panel-note" id="modal-message"></p>
			</div>

			<div class="modal-files-section" id="modal-files-section">
				<label class="panel-label">Uploaded Files</label>
				<div class="modal-files-list" id="modal-files-list"></div>
			</div>

			<div class="modal-status-control">
				<label class="panel-label">Update Status</label>
				<select class="adm-filter-select" id="modal-status-select">
					<option value="pending">Pending</option>
					<option value="processing">Processing</option>
					<option value="hold">Hold</option>
					<option value="delivered">Delivered</option>
					<option value="cancelled">Cancelled</option>
				</select>
				<button type="button" class="btn modal-update-btn" id="modal-update-btn">
					Update Status
				</button>
			</div>

			<!--
				Arrangement form. Field labels are swapped in by
				admin-dashboard.js from the service's definition in
				includes/service-fields.php, so "Assigned Artist" shows for
				Artists Marketplace and "Class Link / Venue" for Classes.
				The digital-distribution service is excluded at runtime: its
				orders are managed in the release tracker instead.
			-->
			<div class="modal-files-section d-none" id="modal-arrangement-section">
				<label class="panel-label">
					Arrangement Shown to Customer
					<span class="panel-hint" id="modal-arrangement-hint"></span>
				</label>
				<div class="modal-arrangement-grid">
					<div class="modal-field">
						<label class="panel-label" id="modal-headline-label" for="modal-headline">Detail 1</label>
						<input type="text" class="panel-input" id="modal-headline" autocomplete="off">
					</div>
					<div class="modal-field">
						<label class="panel-label" id="modal-sub-headline-label" for="modal-sub-headline">Detail 2</label>
						<input type="text" class="panel-input" id="modal-sub-headline" autocomplete="off">
					</div>
					<div class="modal-field">
						<label class="panel-label" id="modal-location-label" for="modal-location">Location / Link</label>
						<input type="text" class="panel-input" id="modal-location" autocomplete="off">
					</div>
					<div class="modal-field">
						<label class="panel-label" for="modal-progress">Progress</label>
						<select class="adm-filter-select" id="modal-progress"></select>
					</div>
					<div class="modal-field">
						<label class="panel-label" id="modal-starts-label" for="modal-starts-on">Start Date</label>
						<input type="date" class="panel-input" id="modal-starts-on">
					</div>
					<div class="modal-field">
						<label class="panel-label" id="modal-ends-label" for="modal-ends-on">End Date</label>
						<input type="date" class="panel-input" id="modal-ends-on">
					</div>
					<div class="modal-field modal-field-wide">
						<label class="panel-label" for="modal-arrangement-notes">Notes</label>
						<textarea class="panel-input" id="modal-arrangement-notes" rows="3"></textarea>
					</div>
				</div>
				<button type="button" class="btn modal-update-btn" id="modal-arrangement-save-btn">
					Save Arrangement
				</button>
			</div>
		</div>
	</div>
</div>
