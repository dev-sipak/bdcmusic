<!-- Service Modal -->
<div class="adm-modal" id="adm-service-modal">
	<div class="adm-modal-backdrop" id="service-modal-backdrop"></div>
	<div class="adm-modal-content" style="max-width:640px;">
		<div class="adm-modal-header">
			<h3 id="service-modal-title">Add New Service</h3>
			<button type="button" class="modal-close-btn" id="service-modal-close">
				<i data-lucide="x"></i>
			</button>
		</div>
		<div class="adm-modal-body">
			<form id="service-form" novalidate>
				<input type="hidden" id="service-modal-id" value="0">
				<div class="modal-detail-grid">
					<div class="modal-detail-item" style="grid-column:1/-1;">
						<label class="panel-label">Service Name <span style="color:red;">*</span></label>
						<div style="position:relative;">
							<i data-lucide="layers" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
							<input type="text" id="service-name" class="panel-input" style="padding-left:42px;" maxlength="100" required>
						</div>
					</div>
					<div class="modal-detail-item">
						<label class="panel-label">Slug <span style="color:red;">*</span></label>
						<div style="position:relative;">
							<i data-lucide="link" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
							<input type="text" id="service-slug" class="panel-input" style="padding-left:42px;" maxlength="50" placeholder="audio-video">
						</div>
					</div>
					<div class="modal-detail-item">
						<label class="panel-label">Booking Type <span style="color:red;">*</span></label>
						<div style="position:relative;">
							<i data-lucide="shopping-cart" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
							<select id="service-booking-mode" class="adm-filter-select" style="padding-left:42px;">
								<option value="packages">Packages (book online)</option>
								<option value="quote">Quote only</option>
							</select>
						</div>
					</div>
					<div class="modal-detail-item" style="grid-column:1/-1;">
						<label class="panel-label">Price Note</label>
						<div style="position:relative;">
							<i data-lucide="indian-rupee" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
							<input type="text" id="service-price-note" class="panel-input" style="padding-left:42px;" maxlength="120" placeholder="Starting at Rs. 499">
						</div>
					</div>
					<div class="modal-detail-item">
						<label class="panel-label">Active</label>
						<label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
							<input type="checkbox" id="service-active" checked> Visible on frontend
						</label>
					</div>
				</div>

				<div style="margin-top:20px;text-align:right;">
					<button type="button" class="btn" style="background:var(--secondary);color:var(--text);margin-right:8px;" id="service-modal-close-btn">Cancel</button>
					<button type="submit" class="btn">Save Service</button>
				</div>
			</form>
		</div>
	</div>
</div>
