<!-- Package Modal -->
<div class="adm-modal" id="adm-plan-modal">
    <div class="adm-modal-backdrop" id="plan-modal-backdrop"></div>
    <div class="adm-modal-content" style="max-width:720px;">
        <div class="adm-modal-header">
            <h3 id="plan-modal-title">Add New Package</h3>
            <button type="button" class="modal-close-btn" id="plan-modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="adm-modal-body">
            <form id="plan-form" novalidate>
                <input type="hidden" id="plan-modal-id" value="0">
                <div class="modal-detail-grid">
                    <div class="modal-detail-item">
                        <label class="panel-label">Service <span style="color:red;">*</span></label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-layer-group" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <select id="plan-service" class="adm-filter-select" style="padding-left:42px;" required>
                                <option value="">Select Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Package Name <span style="color:red;">*</span></label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-tag" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="text" id="plan-name" class="panel-input" style="padding-left:42px;" maxlength="100" required>
                        </div>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Group Label</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-folder" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="text" id="plan-group-label" class="panel-input" style="padding-left:42px;" maxlength="100" placeholder="Recording" list="plan-group-label-list">
                        </div>
                        <datalist id="plan-group-label-list"></datalist>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Group Key</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-key" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="text" id="plan-group-key" class="panel-input" style="padding-left:42px;" maxlength="60" placeholder="recording">
                        </div>
                        <p style="margin:6px 0 0;font-size:0.75rem;color:var(--muted);">Leave blank to generate one from the name. Packages sharing a key are shown as one group.</p>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Price (Rs.) <span style="color:red;">*</span></label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-indian-rupee-sign" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="number" id="plan-price" class="panel-input" style="padding-left:42px;" step="0.01" min="0" max="99999999.99" value="0">
                        </div>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Price Note</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-comment" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="text" id="plan-price-note" class="panel-input" style="padding-left:42px;" maxlength="60" placeholder="/ project">
                        </div>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Sort Order</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-arrow-down-wide-short" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="number" id="plan-sort-order" class="panel-input" style="padding-left:42px;" step="1" value="0">
                        </div>
                    </div>
                    <div class="modal-detail-item" style="grid-column:1/-1;">
                        <label class="panel-label">Description</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-align-left" style="position:absolute;left:14px;top:14px;color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <textarea id="plan-description" class="panel-input" rows="2" style="padding-left:42px;" maxlength="255"></textarea>
                        </div>
                    </div>
                    <div class="modal-detail-item" style="grid-column:1/-1;">
                        <label class="panel-label">Best For</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-bullseye" style="position:absolute;left:14px;top:14px;color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <textarea id="plan-best-for" class="panel-input" rows="2" style="padding-left:42px;" maxlength="255"></textarea>
                        </div>
                    </div>
                    <div class="modal-detail-item" style="grid-column:1/-1;">
                        <label class="panel-label">Features (one per line)</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-list-check" style="position:absolute;left:14px;top:14px;color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <textarea id="plan-features" class="panel-input" rows="5" style="padding-left:42px;" placeholder="Up to 5 songs&#10;2 revisions&#10;72 hour delivery"></textarea>
                        </div>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Default Package</label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="plan-default"> Opens this one first
                        </label>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Enquiry Only</label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="plan-enquiry"> Quote instead of selling
                        </label>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Bookable</label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="plan-orderable" checked> Customer can add this to an order
                        </label>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Active</label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="plan-active" checked> Visible on frontend
                        </label>
                    </div>
                </div>

                <div style="margin-top:20px;text-align:right;">
                    <button type="button" class="btn" style="background:var(--secondary);color:var(--text);margin-right:8px;" id="plan-modal-close-btn">Cancel</button>
                    <button type="submit" class="btn">Save Package</button>
                </div>
            </form>
        </div>
    </div>
</div>
