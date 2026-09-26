<!-- Artist Modal -->
<div class="adm-modal" id="adm-artist-modal">
    <div class="adm-modal-backdrop" id="artist-modal-backdrop"></div>
    <div class="adm-modal-content" style="max-width:640px;">
        <div class="adm-modal-header">
            <h3 id="artist-modal-title">Add New Artist</h3>
            <button type="button" class="modal-close-btn" id="artist-modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="adm-modal-body">
            <form id="artist-form" novalidate>
                <input type="hidden" id="artist-modal-id" value="0">
                <div class="modal-detail-grid">
                    <div class="modal-detail-item" style="grid-column:1/-1;">
                        <label class="panel-label">Artist Name <span style="color:red;">*</span></label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-user" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="text" id="artist-name" class="panel-input" style="padding-left:42px;" required>
                        </div>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Category <span style="color:red;">*</span></label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-palette" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <select id="artist-category" class="adm-filter-select" style="padding-left:42px;" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Location</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-location-dot" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="text" id="artist-location" class="panel-input" style="padding-left:42px;">
                        </div>
                    </div>
                    <div class="modal-detail-item" style="grid-column:1/-1;">
                        <label class="panel-label">Bio</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-pen-fancy" style="position:absolute;left:14px;top:14px;color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <textarea id="artist-bio" class="panel-input" rows="3" style="padding-left:42px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-detail-item" style="grid-column:1/-1;">
                        <label class="panel-label">Image (WebP, 250x360)</label>
                        <div style="position:relative;">
                            <i class="fa-solid fa-cloud-arrow-up" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.9rem;pointer-events:none;z-index:1;"></i>
                            <input type="file" id="artist-image-input" accept="image/jpeg,image/png,image/webp" class="panel-input" style="padding-left:42px;">
                        </div>
                        <input type="hidden" id="artist-image-url">
                        <img id="artist-image-preview" src="" alt="" style="display:none;margin-top:8px;width:80px;height:115px;object-fit:cover;border-radius:8px;">
                    </div>
                    <div class="modal-detail-item">
                        <label class="panel-label">Active</label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="artist-active" checked> Visible on frontend
                        </label>
                    </div>
                </div>

                <div style="margin-top:18px;padding-top:18px;border-top:1px solid var(--border);">
                    <label class="panel-label" style="margin-bottom:10px;">Pricing</label>
                    <table class="adm-table" style="font-size:0.82rem;">
                        <thead>
                            <tr>
                                <th>Service Type</th>
                                <th>Price</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="artist-pricing-body"></tbody>
                    </table>
                    <button type="button" class="btn" id="add-pricing-row" style="margin-top:8px;font-size:0.82rem;padding:6px 12px;">
                        <i class="fa-solid fa-plus"></i> Add Pricing
                    </button>
                </div>

                <div style="margin-top:20px;text-align:right;">
                    <button type="button" class="btn" style="background:var(--secondary);color:var(--text);margin-right:8px;" id="artist-modal-close-btn">Cancel</button>
                    <button type="submit" class="btn">Save Artist</button>
                </div>
            </form>
        </div>
    </div>
</div>
