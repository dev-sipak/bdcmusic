<!-- Enquiry Reply Modal -->
<div class="adm-modal" id="adm-reply-modal">
    <div class="adm-modal-backdrop" id="reply-modal-backdrop"></div>
    <div class="adm-modal-content" style="max-width:540px;">
        <div class="adm-modal-header">
            <h3>Reply to Enquiry</h3>
            <button type="button" class="modal-close-btn" id="reply-modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="adm-modal-body">
            <input type="hidden" id="reply-enquiry-id" value="0">
            <div class="modal-detail-grid">
                <div class="modal-detail-item">
                    <span class="panel-label">Enquiry From</span>
                    <span class="panel-value" id="reply-enquiry-name"></span>
                </div>
                <div class="modal-detail-item">
                    <span class="panel-label">Email</span>
                    <span class="panel-value" id="reply-enquiry-email"></span>
                </div>
                <div class="modal-detail-item" style="grid-column:1/-1;">
                    <span class="panel-label">Original Message</span>
                    <p id="reply-enquiry-message" style="background:var(--secondary);padding:10px 14px;border-radius:8px;font-size:0.85rem;color:var(--muted);margin:4px 0 0;"></p>
                </div>
                <div class="modal-detail-item" style="grid-column:1/-1;display:none;" id="reply-previous-wrap">
                    <span class="panel-label">Previous Reply</span>
                    <p id="reply-enquiry-previous" style="background:var(--secondary);padding:10px 14px;border-radius:8px;font-size:0.85rem;color:var(--muted);margin:4px 0 0;"></p>
                </div>
                <div class="modal-detail-item" style="grid-column:1/-1;">
                    <label class="panel-label">Your Reply <span style="color:red;">*</span></label>
                    <textarea id="reply-message" class="panel-input" rows="4" placeholder="Type your reply here..."></textarea>
                </div>
            </div>
            <div id="reply-error" style="display:none;color:#ef4444;font-size:0.85rem;margin-top:8px;"></div>
            <div id="reply-success" style="display:none;color:#10b981;font-size:0.85rem;margin-top:8px;"></div>
            <div style="margin-top:16px;text-align:right;">
                <button type="button" class="btn" style="background:var(--secondary);color:var(--text);margin-right:8px;" id="reply-modal-cancel">Cancel</button>
                <button type="button" class="btn" id="reply-send-btn">Send Reply</button>
            </div>
        </div>
    </div>
</div>
