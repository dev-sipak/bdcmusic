/*=========================================================
  CUSTOMER SERVICE SECTION
  Renders one purchased service: the order-time details the
  customer submitted, the files they uploaded, and the
  arrangements the admin has recorded for each order.
  Data comes from includes/customer/service-overview.php
  and is gated server-side on bookings.status.
  ==========================================================*/

document.addEventListener('DOMContentLoaded', function () {

    if (typeof customerDashboardConfig === 'undefined') return;

    var config    = customerDashboardConfig;
    var basePath  = config.basePath;
    var slug      = config.activeService || '';

    var loadingEl = document.getElementById('svc-loading');
    var bodyEl    = document.getElementById('svc-body');
    var emptyEl   = document.getElementById('svc-empty');
    var ordersEl  = document.getElementById('svc-orders');

    // Nothing to do on pages that are not the generic service page.
    if (!loadingEl || !bodyEl || !slug) return;

    function esc(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function money(value) {
        return '\u20B9' + Number(value || 0).toFixed(2);
    }

    /* Progress values are free text such as "In Production", so they need a
       slugified modifier class rather than statusBadgeHtml's raw lowercase. */
    function progressBadgeHtml(progress) {
        var slug = String(progress).toLowerCase().replace(/[^a-z0-9]+/g, '-');
        return '<span class="panel-status-badge status-' + esc(slug) + '">' + esc(progress) + '</span>';
    }

    /* ----- Order-time details the customer filled in ----- */
    function renderMetaFields(metaFields) {
        if (!metaFields || metaFields.length === 0) {
            return '';
        }
        return '<div class="detail-grid">' + metaFields.map(function (f) {
            return '<div class="detail-item">' +
                '<span class="panel-label">' + esc(f.label) + '</span>' +
                '<span class="panel-value">' + esc(f.value) + '</span>' +
            '</div>';
        }).join('') + '</div>';
    }

    /* ----- Files the customer uploaded against the order ----- */
    function renderFiles(files) {
        if (!files || files.length === 0) {
            return '';
        }
        var rows = files.map(function (f) {
            var sizeMB = (f.file_size / (1024 * 1024)).toFixed(1);
            var sizeKB = (f.file_size / 1024).toFixed(1);
            var size   = f.file_size > 1048576 ? sizeMB + ' MB' : sizeKB + ' KB';
            return '<a class="panel-download-btn" target="_blank" href="' +
                basePath + 'includes/download-file.php?file=' + encodeURIComponent(f.file_path) + '">' +
                '<i class="fa-solid fa-download"></i> ' + esc(f.original_name) +
            '</a>' +
            '<span class="panel-file-size">' + esc(size) + '</span>';
        }).join('');

        return '<div class="panel-subsection">' +
            '<span class="panel-label">Your Uploads</span>' +
            '<div class="panel-file-list">' + rows + '</div>' +
        '</div>';
    }

    /* ----- Arrangements recorded by admin in service_records ----- */
    function renderArrangement(record, labels) {
        if (!record) {
            return '<div class="panel-subsection">' +
                '<span class="panel-label">Arrangements</span>' +
                '<p class="panel-note">Our team has not added the arrangements for this order yet. ' +
                'They appear here as soon as the service is scheduled.</p>' +
            '</div>';
        }

        // Only the columns this service actually uses, in the order declared
        // in includes/service-fields.php.
        var columns = ['headline', 'sub_headline', 'location', 'starts_on', 'ends_on'];
        var pairs = columns.map(function (column) {
            var value = record[column];
            if (value === null || value === undefined || value === '') return '';
            var meta = labels[column] || { label: column, icon: 'fa-circle' };
            return '<div class="detail-item">' +
                '<span class="panel-label"><i class="fa-solid ' + esc(meta.icon) + '"></i> ' + esc(meta.label) + '</span>' +
                '<span class="panel-value">' + esc(value) + '</span>' +
            '</div>';
        }).filter(Boolean).join('');

        var html = '';
        if (record.progress) {
            html += '<div class="detail-item">' +
                '<span class="panel-label"><i class="fa-solid fa-spinner"></i> Progress</span>' +
                '<span>' + progressBadgeHtml(record.progress) + '</span>' +
            '</div>';
        }
        html += pairs;

        var notes = record.notes
            ? '<div class="panel-subsection"><span class="panel-label">Notes From Our Team</span>' +
              '<p class="panel-note">' + esc(record.notes) + '</p></div>'
            : '';

        return '<div class="panel-subsection">' +
            '<span class="panel-label">Arrangements</span>' +
            '<div class="detail-grid">' + html + '</div>' +
            notes +
        '</div>';
    }

    /* ----- One order card ----- */
    function renderOrder(order, service) {
        var head =
            '<div class="detail-header">' +
                '<h3>' + esc(order.booking_id) + '</h3>' +
                statusBadgeHtml(order.status) +
                '<span class="panel-order-amount">' + money(order.amount) + '</span>' +
            '</div>' +
            '<div class="detail-grid">' +
                '<div class="detail-item"><span class="panel-label">Order Date</span><span class="panel-value">' + esc(order.created_at) + '</span></div>' +
                '<div class="detail-item"><span class="panel-label">Payment</span><span class="panel-value">' + esc(order.payment_status) + '</span></div>' +
            '</div>';

        var message = order.message
            ? '<div class="panel-subsection"><span class="panel-label">Your Message</span><p class="panel-note">' + esc(order.message) + '</p></div>'
            : '';

        return '<div class="panel-profile-card svc-card">' +
            head +
            '<div class="panel-subsection">' +
                '<span class="panel-label">Order Details</span>' +
                (order.metaFields.length
                    ? '<div class="detail-grid">' + order.metaFields.map(function (f) {
                        return '<div class="detail-item">' +
                            '<span class="panel-label">' + esc(f.label) + '</span>' +
                            '<span class="panel-value">' + esc(f.value) + '</span>' +
                        '</div>';
                      }).join('') + '</div>'
                    : '<p class="panel-note">No additional details were submitted for this order.</p>') +
            '</div>' +
            message +
            renderArrangement(order.arrangement, service.arrangement) +
            renderFiles(order.files) +
        '</div>';
    }

    /* ----- Locked notice ----- */
    function renderLocked(service) {
        return '<div class="panel-profile-card svc-locked">' +
            '<div class="svc-locked-icon"><i class="fa-solid fa-lock"></i></div>' +
            '<h3>Awaiting Confirmation</h3>' +
            '<p class="panel-note">Your ' + esc(service.name) + ' order is received. This section opens up as soon as ' +
            'our team moves the order into progress, and you will see the schedule, links and arrangements here.</p>' +
            '<p class="panel-note">You can always see everything you submitted in <a href="' + basePath + 'dashboard/orders">My Orders</a>.</p>' +
        '</div>';
    }

    function loadService() {
        fetch(basePath + 'includes/customer/service-overview.php?service=' + encodeURIComponent(slug))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                loadingEl.classList.add('d-none');
                bodyEl.classList.remove('d-none');

                if (!data.success) {
                    emptyEl.textContent = data.message;
                    emptyEl.classList.remove('d-none');
                    return;
                }

                var service = data.service;
                document.getElementById('svc-title').textContent    = service.name;
                document.getElementById('svc-blurb').textContent     = service.blurb;

                if (!data.orders || data.orders.length === 0) {
                    emptyEl.classList.remove('d-none');
                    return;
                }

                var html = service.unlocked
                    ? data.orders.map(function (order) { return renderOrder(order, service); }).join('')
                    : renderLocked(service);

                // Even while locked, list the orders so the customer can see
                // what stage each one is at.
                html += '<div class="svc-order-list">' + data.orders.map(function (order) {
                    return '<div class="svc-order-line">' +
                        '<strong>' + esc(order.booking_id) + '</strong>' +
                        statusBadgeHtml(order.status) +
                        '<span class="panel-muted">' + esc(order.created_at) + '</span>' +
                    '</div>';
                }).join('') + '</div>';

                ordersEl.innerHTML = html;
            })
            .catch(function () {
                loadingEl.classList.add('d-none');
                bodyEl.classList.remove('d-none');
                emptyEl.textContent = 'Could not load your service details. Please refresh the page.';
                emptyEl.classList.remove('d-none');
            });
    }

    loadService();
});
