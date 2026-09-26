<!-- Order Detail Modal -->
<div class="adm-modal" id="admin-order-modal">
    <div class="adm-modal-backdrop" id="modal-backdrop"></div>
    <div class="adm-modal-content">
        <div class="adm-modal-header">
            <h3 id="modal-order-id">Order Details</h3>
            <button type="button" class="modal-close-btn" id="modal-close">
                <i class="fa-solid fa-xmark"></i>
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
                    <span class="panel-label">Date</span>
                    <span class="panel-value" id="modal-date"></span>
                </div>
                <div class="modal-detail-item">
                    <span class="panel-label">Amount</span>
                    <span class="panel-value" id="modal-amount"></span>
                </div>
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
        </div>
    </div>
</div>
