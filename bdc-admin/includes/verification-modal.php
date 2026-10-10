<!--
	Artist registration review panel.

	Opened from the verification queue. Shows everything the artist submitted at
	order time, the order behind it, and the three decisions available: verify
	(issues the ID Card and Certificate), reject (needs a reason, which is sent to
	the artist), and move back to pending.
-->
<div class="adm-modal" id="adm-verification-modal">
	<div class="adm-modal-backdrop" id="verification-modal-backdrop"></div>
	<div class="adm-modal-content" style="max-width:720px;">
		<div class="adm-modal-header">
			<h3 id="verification-modal-title">Registration Review</h3>
			<button type="button" class="modal-close-btn" id="verification-modal-close">
				<i data-lucide="x"></i>
			</button>
		</div>
		<div class="adm-modal-body">
			<div class="modal-detail-grid" id="verification-summary-grid"></div>
			<div style="margin-top:18px;">
				<label class="panel-label">What the artist submitted</label>
				<div class="modal-detail-grid" id="verification-fields-grid"></div>
			</div>
			<div style="margin-top:18px;" id="verification-files-section">
				<label class="panel-label">Uploaded with the registration</label>
				<div id="verification-files-list"></div>
			</div>
			<div style="margin-top:18px;" id="verification-documents-section">
				<label class="panel-label">Issued documents</label>
				<div id="verification-documents-list"></div>
			</div>
			<div style="margin-top:20px;">
				<label class="panel-label" for="verification-note">
					Review note <span id="verification-note-required" class="d-none" style="color:var(--primary);">* required to reject</span>
				</label>
				<textarea class="panel-input" id="verification-note" rows="3"
					placeholder="Shown to the artist. Required when rejecting."></textarea>
			</div>
			<div style="margin-top:20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;" id="verification-actions">
				<button type="button" class="btn" style="background:var(--secondary);color:var(--text);" id="verification-reopen-btn">
					<i data-lucide="rotate-ccw"></i> Move Back to Pending
				</button>
				<button type="button" class="btn" style="background:#fef2f2;color:#b91c1c;" id="verification-reject-btn">
					<i data-lucide="circle-x"></i> Reject
				</button>
				<button type="button" class="btn" id="verification-verify-btn">
					<i data-lucide="badge-check"></i> Verify &amp; Issue Documents
				</button>
			</div>
		</div>
	</div>
</div>
