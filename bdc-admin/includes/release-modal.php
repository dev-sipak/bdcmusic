<!-- Release Editor Modal -->
<div class="adm-modal" id="admin-release-modal">
    <div class="adm-modal-backdrop" id="release-modal-backdrop"></div>
    <div class="adm-modal-content" style="max-width:760px;">
        <div class="adm-modal-header">
            <h3 id="release-modal-title">Release</h3>
            <button type="button" class="modal-close-btn" id="release-modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="adm-modal-body">
            <div class="modal-detail-grid">
                <div class="modal-detail-item">
                    <span class="panel-label">Customer</span>
                    <span class="panel-value" id="release-modal-customer"></span>
                </div>
                <div class="modal-detail-item">
                    <span class="panel-label">Type</span>
                    <span class="panel-value" id="release-modal-type"></span>
                </div>
                <div class="modal-detail-item">
                    <span class="panel-label">ISRC</span>
                    <span class="panel-value" id="release-modal-isrc"></span>
                </div>
                <div class="modal-detail-item">
                    <span class="panel-label">UPC</span>
                    <span class="panel-value" id="release-modal-upc"></span>
                </div>
            </div>

            <div class="modal-status-control">
                <label class="panel-label">Update Status</label>
                <select class="adm-filter-select" id="release-modal-status"></select>
                <button type="button" class="btn modal-update-btn" id="release-modal-save-status">
                    Update Status
                </button>
            </div>

            <div class="modal-files-section">
                <label class="panel-label" for="release-modal-note">
                    Note for the customer
                    <span class="panel-hint">Shown in the release history</span>
                </label>
                <textarea class="panel-input" id="release-modal-note" rows="2" placeholder="e.g. Waiting on the final master."></textarea>
            </div>

            <!--
                Track list. An album or EP carries one row per track; a single
                carries exactly one. Rows are renumbered on save.
            -->
            <div class="modal-files-section">
                <label class="panel-label">
                    Track List
                    <span class="panel-hint" id="release-tracks-hint"></span>
                </label>
                <div class="release-editor-rows" id="release-tracks-editor"></div>
                <button type="button" class="btn release-add-row-btn" id="release-add-track">
                    <i class="fa-solid fa-plus"></i> Add Track
                </button>
            </div>

            <!--
                Platform live links. Only rows with a name and a URL and
                "Show to customer" ticked are visible to the customer.
            -->
            <div class="modal-files-section">
                <label class="panel-label">
                    Platform Live Links
                    <span class="panel-hint">Only active links appear in the customer’s release view</span>
                </label>
                <div class="release-editor-rows" id="release-links-editor"></div>
                <button type="button" class="btn release-add-row-btn" id="release-add-link">
                    <i class="fa-solid fa-plus"></i> Add Platform
                </button>
                <div class="release-platform-presets" id="release-platform-presets"></div>
            </div>

            <div class="modal-status-control">
                <button type="button" class="btn modal-update-btn" id="release-modal-save-content">
                    <i class="fa-solid fa-floppy-disk"></i> Save Tracks &amp; Links
                </button>
            </div>
        </div>
    </div>
</div>
