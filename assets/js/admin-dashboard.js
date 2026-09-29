document.addEventListener('DOMContentLoaded', function () {

  var overview = adminDashboardConfig.overview || { stats: {}, recent: [] };
  var basePath = adminDashboardConfig.basePath;

    function esc(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    initSidebarToggle('.adm-sidebar', '#admin-menu-toggle');

    /* ----- Overview Stats ----- */
    var recentOrdersTbody = document.getElementById('recent-orders-tbody');

    function updateOverviewStats() {
        // The counts arrive pre-aggregated from the server, and `recent` holds
        // only the ten rows the table shows, so neither is derived from a full
        // list of orders in the browser.
        var stats = overview.stats || {};
        var recent = overview.recent || [];

        document.getElementById('stat-total').textContent      = stats.total || 0;
        document.getElementById('stat-pending').textContent     = stats.pending || 0;
        document.getElementById('stat-processing').textContent  = stats.processing || 0;
        document.getElementById('stat-delivered').textContent   = stats.delivered || 0;
        document.getElementById('stat-customers').textContent   = stats.customers || 0;

        if (!recentOrdersTbody) return;

        recentOrdersTbody.innerHTML = recent.map(function (o) {
            return '<tr>' +
                '<td><strong>' + esc(o.id) + '</strong></td>' +
                '<td>' + esc(o.customer) + '</td>' +
                '<td>' + esc(o.phone || 'N/A') + '</td>' +
                '<td>' + esc(o.service) + '</td>' +
                '<td>' + money(o.amount) + '</td>' +
                '<td>' + statusBadgeHtml(o.status) + '</td>' +
            '</tr>';
        }).join('');
    }

    if (recentOrdersTbody) {
        updateOverviewStats();
    }


    /* ----- Orders Management (Paginated) ----- */
    var ordersPagination = { currentPage: 1, totalPages: 1 };
    var ordersFilters = { status: 'all', service: 'all', payment: 'all', search: '' };

    function loadOrders(page) {
        page = page || 1;
        var params = 'page=' + page + '&per_page=10';
        if (ordersFilters.status !== 'all') params += '&status=' + encodeURIComponent(ordersFilters.status);
        if (ordersFilters.service !== 'all') params += '&service=' + encodeURIComponent(ordersFilters.service);
        if (ordersFilters.payment !== 'all') params += '&payment=' + encodeURIComponent(ordersFilters.payment);
        if (ordersFilters.search) params += '&search=' + encodeURIComponent(ordersFilters.search);

        fetch(basePath + 'includes/admin/orders-list.php?' + params)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    ordersPagination = data.pagination;
                    renderAdminOrders(data.orders);
                }
            });
    }

    function renderAdminOrders(ordersList) {
        var tbody    = document.getElementById('admin-orders-tbody');
        var emptyMsg = document.getElementById('admin-orders-empty');
        var pagWrap  = document.getElementById('admin-orders-pagination');

        if (!ordersList || ordersList.length === 0) {
            tbody.innerHTML = '';
            emptyMsg.classList.remove('d-none');
            if (pagWrap) pagWrap.innerHTML = '';
            return;
        }

        emptyMsg.classList.add('d-none');
        tbody.innerHTML = ordersList.map(function (o) {
            var plan      = o.plan || {};
            var items     = o.items || [];
            var amounts   = o.amounts || {};
            var addonsNote = o.addonsCount > 0 ? ' +' + o.addonsCount + ' add-on' + (o.addonsCount === 1 ? '' : 's') : '';

            // Several packages on one order are listed together; the plan
            // snapshot only names the first of them.
            var planName = items.length > 1
                ? items.map(function (it) { return it.name; }).join(', ')
                : (plan.name || (items.length === 1 ? items[0].name : '—'));

            return '<tr>' +
                '<td><strong>' + esc(o.id) + '</strong></td>' +
                '<td>' + esc(o.customer) +
                    (o.customerType === 'guest' ? ' <span class="adm-table-note">Guest</span>' : '') + '</td>' +
                '<td>' + esc(o.phone || 'N/A') + '</td>' +
                '<td>' + esc(o.service) + '</td>' +
                '<td>' + esc(planName) +
                    (addonsNote ? '<span class="adm-table-note">' + esc(addonsNote) + '</span>' : '') + '</td>' +
                '<td>' + esc(o.date) + '</td>' +
                '<td>' + money(amounts.total !== undefined ? amounts.total : o.amount) + '</td>' +
                '<td>' + statusBadgeHtml(o.status) + '</td>' +
                '<td>' + paymentBadgeHtml(o.paymentStatus, o.paymentLabel) + '</td>' +
                '<td><button class="adm-view-btn" data-order-id="' + esc(o.id) + '"><i class="fa-solid fa-eye"></i></button></td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('.adm-view-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openOrderModal(this.getAttribute('data-order-id'));
            });
        });

        if (pagWrap) {
            pagWrap.innerHTML = renderPaginationHtml(ordersPagination, function (page) {
                loadOrders(page);
            });
        }
    }

    var orderStatusFilter = document.getElementById('admin-order-status');
    var orderServiceFilter = document.getElementById('admin-order-service');
    var orderPaymentFilter = document.getElementById('admin-order-payment');
    var orderSearchInput = document.getElementById('admin-order-search');

    if (orderStatusFilter) {
        orderStatusFilter.addEventListener('change', function () {
            ordersFilters.status = this.value;
            loadOrders(1);
        });
    }
    if (orderServiceFilter) {
        orderServiceFilter.addEventListener('change', function () {
            ordersFilters.service = this.value;
            loadOrders(1);
        });
    }
    if (orderPaymentFilter) {
        orderPaymentFilter.addEventListener('change', function () {
            ordersFilters.payment = this.value;
            loadOrders(1);
        });
    }

    if (orderSearchInput) {
        var orderSearchTimer;
        orderSearchInput.addEventListener('input', function () {
            var val = this.value;
            clearTimeout(orderSearchTimer);
            orderSearchTimer = setTimeout(function () {
                ordersFilters.search = val;
                loadOrders(1);
            }, 400);
        });
    }

    /* ----- Customers (Paginated) ----- */
    var customersPagination = { currentPage: 1, totalPages: 1 };
    var customersSearch = '';

    function loadCustomers(page) {
        page = page || 1;
        var params = 'page=' + page + '&per_page=10';
        if (customersSearch) params += '&search=' + encodeURIComponent(customersSearch);

        fetch(basePath + 'includes/admin/customers-list.php?' + params)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    customersPagination = data.pagination;
                    renderCustomers(data.customers);
                }
            });
    }

    function renderCustomers(customersList) {
        var tbody    = document.getElementById('admin-customers-tbody');
        var pagWrap  = document.getElementById('admin-customers-pagination');
        if (!tbody) return;

        if (!customersList || customersList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--muted);">No customers found.</td></tr>';
            if (pagWrap) pagWrap.innerHTML = '';
            return;
        }

        tbody.innerHTML = customersList.map(function (c) {
            return '<tr>' +
                '<td><strong>' + esc(c.name) + '</strong></td>' +
                '<td>' + esc(c.email) + '</td>' +
                '<td>' + esc(c.phone || 'N/A') + '</td>' +
                '<td>' + esc(c.total_orders) + '</td>' +
                '<td>\u20B9' + esc(c.total_spent) + '</td>' +
                '<td>' + esc(c.last_order || 'N/A') + '</td>' +
            '</tr>';
        }).join('');

        if (pagWrap) {
            pagWrap.innerHTML = renderPaginationHtml(customersPagination, function (page) {
                loadCustomers(page);
            });
        }
    }

    /* ----- Order Modal -----
       The order list is paginated, so the modal loads the full order from
       includes/admin/order-detail.php rather than from the in-page overview
       data. Status and arrangement are both persisted through
       includes/admin/order-update.php. */
    var modal     = document.getElementById('admin-order-modal');
    var backdrop  = document.getElementById('modal-backdrop');
    var closeBtn  = document.getElementById('modal-close');
    var updateBtn = document.getElementById('modal-update-btn');
    var arrSaveBtn = document.getElementById('modal-arrangement-save-btn');
    var currentOrderId = null;

    // The six generic columns the arrangement form writes, in the order the
    // service_records table declares them.
    var ARRANGEMENT_COLUMNS = ['headline', 'sub_headline', 'location', 'starts_on', 'ends_on'];
    var arrangementFieldIds = {
        headline:     'modal-headline',
        sub_headline: 'modal-sub-headline',
        location:     'modal-location',
        starts_on:    'modal-starts-on',
        ends_on:      'modal-ends-on'
    };
    var arrangementLabelIds = {
        headline:     'modal-headline-label',
        sub_headline: 'modal-sub-headline-label',
        location:     'modal-location-label',
        starts_on:    'modal-starts-label',
        ends_on:      'modal-ends-label'
    };

    function fillArrangementForm(order) {
        var section = document.getElementById('modal-arrangement-section');
        var labels  = order.arrangementFields || {};
        var options = order.progressOptions || [];
        var record  = order.arrangement || {};

        // Digital distribution orders are managed in the release tracker, so
        // the generic arrangement form does not apply to them.
        if (order.service_slug === 'digital-distribution') {
            section.classList.add('d-none');
            return;
        }
        section.classList.remove('d-none');

        var hint = document.getElementById('modal-arrangement-hint');
        if (hint) {
            hint.textContent = 'These details appear in the customer’s ' + order.service + ' section and in My Orders.';
        }

        ARRANGEMENT_COLUMNS.forEach(function (column) {
            var field  = document.getElementById(arrangementFieldIds[column]);
            var label  = document.getElementById(arrangementLabelIds[column]);
            var meta   = labels[column];
            // A service can hide a field it has no use for.
            if (field) field.classList.toggle('d-none', !meta);
            if (label && meta) label.textContent = meta.label;
            if (field) field.value = record[column] || '';
        });

        var progressSelect = document.getElementById('modal-progress');
        progressSelect.innerHTML = options.map(function (value) {
            return '<option value="' + esc(value) + '">' + esc(value) + '</option>';
        }).join('');
        if (options.indexOf(record.progress) !== -1) {
            progressSelect.value = record.progress;
        } else if (options.length) {
            progressSelect.value = options[0];
        }

        document.getElementById('modal-arrangement-notes').value = record.notes || '';
    }

    function readArrangementForm() {
        var record = {};
        ARRANGEMENT_COLUMNS.forEach(function (column) {
            var field = document.getElementById(arrangementFieldIds[column]);
            if (field && !field.classList.contains('d-none')) {
                record[column] = field.value.trim();
            }
        });
        record.progress = document.getElementById('modal-progress').value;
        record.notes    = document.getElementById('modal-arrangement-notes').value.trim();
        return record;
    }

    /**
     * Build one label/value pair for the detail grids in the order modal.
     * @param {string} label
     * @param {string} value
     * @returns {string}
     */
    function detailItem(label, value) {
        return '<div class="detail-item">' +
            '<span class="panel-label">' + esc(label) + '</span>' +
            '<span class="panel-value">' + esc(value) + '</span>' +
        '</div>';
    }

    /**
     * Render the package, money breakdown and add-on lines.
     * @param {object} order
     */
    function renderOrderPackage(order) {
        var plan    = order.plan || {};
        var items   = order.items || [];
        var amounts = order.amounts || {};
        var addons  = order.addons || [];

        var pairs = [];

        if (items.length > 1) {
            // Several packages on one order: one line each, because
            // bookings.plan_id only names the first and service_records holds
            // a single arrangement row per booking.
            pairs.push(detailItem('Packages', String(items.length)));
            items.forEach(function (it) {
                var label = it.groupLabel || it.group || 'Package';
                pairs.push(detailItem(label, it.name + ' \u2014 ' + money(it.lineTotal)));
            });
        } else if (plan.hasPlan) {
            pairs.push(detailItem('Package', plan.name || '—'));
            if (plan.groupLabel || plan.group) {
                pairs.push(detailItem('Package Type', plan.groupLabel || plan.group));
            }
        } else {
            // Pre-package order: no plan was ever recorded, so say that instead
            // of showing a placeholder as though it were something purchased.
            pairs.push(detailItem('Package', 'No package recorded (legacy order)'));
        }

        if (addons.length) {
            pairs.push(detailItem('Add-ons', String(addons.length)));
        }

        pairs.push(detailItem('Package Price', money(amounts.subtotal)));
        if (addons.length) {
            pairs.push(detailItem('Add-ons Total', money(amounts.addonsTotal)));
        }
        pairs.push(detailItem('Total Charged', money(amounts.total)));
        pairs.push(detailItem('Currency', amounts.currency || 'INR'));

        var planEl = document.getElementById('modal-plan-fields');
        if (planEl) planEl.innerHTML = pairs.join('');

        var addonsEl     = document.getElementById('modal-addons-fields');
        var addonsSection = document.getElementById('modal-addons-section');
        if (addonsEl && addonsSection) {
            if (addons.length) {
                addonsEl.innerHTML = addons.map(function (a) {
                    var qty = a.qty > 1 ? a.name + ' × ' + a.qty : a.name;
                    return detailItem(qty, money(a.lineTotal));
                }).join('');
                addonsSection.classList.remove('d-none');
            } else {
                addonsEl.innerHTML = '';
                addonsSection.classList.add('d-none');
            }
        }
    }

    /**
     * Render the payment summary and the per-attempt trail.
     * @param {object} order
     */
    function renderOrderPayment(order) {
        var payment = order.payment || {};

        var pairs = [
            detailItem('Status', payment.statusLabel || payment.status || '—'),
            detailItem('Provider', payment.provider || '—')
        ];

        if (payment.method) pairs.push(detailItem('Method', payment.method));
        if (payment.paidAt)  pairs.push(detailItem('Paid On', payment.paidAt));
        if (payment.razorpayOrderId) pairs.push(detailItem('Gateway Order ID', payment.razorpayOrderId));
        if (payment.paymentId)       pairs.push(detailItem('Gateway Payment ID', payment.paymentId));

        // The signature value is never sent to the browser; only whether one
        // was captured, which is what an admin needs to audit a payment.
        pairs.push(detailItem('Signature On File', payment.hasSignature ? 'Yes' : 'No'));

        if (payment.failureReason) {
            pairs.push(detailItem('Failure Reason', payment.failureReason));
        }

        var payEl = document.getElementById('modal-payment-fields');
        if (payEl) payEl.innerHTML = pairs.join('');

        var attempts = payment.attempts || [];
        var attEl     = document.getElementById('modal-payment-attempts');
        var attSection = document.getElementById('modal-payment-attempts-section');

        if (attEl && attSection) {
            if (attempts.length) {
                attEl.innerHTML = attempts.map(function (a) {
                    var ref = a.razorpayPaymentId || a.razorpayOrderId || ('Attempt #' + a.id);
                    var note = a.method || a.statusLabel;
                    if (a.failureReason) note += ' — ' + a.failureReason;
                    return '<div class="modal-file-item">' +
                        '<i class="fa-solid fa-receipt modal-file-icon"></i>' +
                        '<span class="modal-file-name">' + esc(ref) + '</span>' +
                        '<span class="modal-file-size">' + esc(money(a.amount) + ' · ' + note) + '</span>' +
                    '</div>';
                }).join('');
                attSection.classList.remove('d-none');
            } else {
                attEl.innerHTML = '';
                attSection.classList.add('d-none');
            }
        }
    }

    function openOrderModal(orderId) {
        currentOrderId = orderId;
        document.getElementById('modal-customer').textContent = '';
        document.getElementById('modal-status-select').value = 'pending';
        // Hide the async sections up front so a previous order's package and
        // payment details cannot linger while the next one loads.
        ['modal-plan-fields', 'modal-payment-fields'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.innerHTML = '';
        });
        ['modal-addons-section', 'modal-payment-attempts-section'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';

        fetch(basePath + 'includes/admin/order-detail.php?id=' + encodeURIComponent(orderId))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.success) {
                    alert(data && data.message ? data.message : 'Could not load this order.');
                    closeModal();
                    return;
                }

                var order = data.order;

                document.getElementById('modal-order-id').textContent  = 'Order ' + order.id;
                document.getElementById('modal-customer').textContent  = order.customer;
                document.getElementById('modal-email').textContent     = order.email;
                document.getElementById('modal-phone').textContent     = order.phone;
                document.getElementById('modal-service').textContent   = order.service;
                document.getElementById('modal-item').textContent      = order.service;
                document.getElementById('modal-date').textContent      = order.date;
                document.getElementById('modal-amount').textContent    = money(order.amount);
                document.getElementById('modal-status-select').value   = order.status;

                renderOrderPackage(order);
                renderOrderPayment(order);

                var metaEl     = document.getElementById('modal-meta-fields');
                var metaSection = document.getElementById('modal-meta-section');
                if (order.metaFields && order.metaFields.length) {
                    metaEl.innerHTML = order.metaFields.map(function (f) {
                        return '<div class="detail-item">' +
                            '<span class="panel-label">' + esc(f.label) + '</span>' +
                            '<span class="panel-value">' + esc(f.value) + '</span>' +
                        '</div>';
                    }).join('');
                    metaSection.classList.remove('d-none');
                } else {
                    metaEl.innerHTML = '';
                    metaSection.classList.add('d-none');
                }

                var msgEl     = document.getElementById('modal-message');
                var msgSection = document.getElementById('modal-message-section');
                if (order.message) {
                    msgEl.textContent = order.message;
                    msgSection.classList.remove('d-none');
                } else {
                    msgSection.classList.add('d-none');
                }

                var filesContainer = document.getElementById('modal-files-list');
                var filesSection   = document.getElementById('modal-files-section');
                if (order.files && order.files.length) {
                    filesSection.classList.remove('d-none');
                    filesContainer.innerHTML = order.files.map(function (f) {
                        var sizeKB = (f.file_size / 1024).toFixed(1);
                        var sizeMB = (f.file_size / (1024 * 1024)).toFixed(1);
                        var sizeStr = f.file_size > 1048576 ? sizeMB + ' MB' : sizeKB + ' KB';
                        var icon = 'fa-file';
                        if (f.mime_type && f.mime_type.indexOf('audio') !== -1) icon = 'fa-file-audio';
                        else if (f.mime_type && f.mime_type.indexOf('video') !== -1) icon = 'fa-file-video';
                        else if (f.mime_type && f.mime_type.indexOf('image') !== -1) icon = 'fa-file-image';
                        else if (f.mime_type && f.mime_type.indexOf('pdf') !== -1) icon = 'fa-file-pdf';
                        return '<div class="modal-file-item">' +
                            '<i class="fa-solid ' + icon + ' modal-file-icon"></i>' +
                            '<span class="modal-file-name">' + esc(f.original_name) + '</span>' +
                            '<span class="modal-file-size">' + sizeStr + '</span>' +
                            '<a href="' + basePath + 'includes/download-file.php?file=' + encodeURIComponent(f.file_path) + '" class="modal-file-download" target="_blank"><i class="fa-solid fa-download"></i></a>' +
                        '</div>';
                    }).join('');
                } else {
                    filesContainer.innerHTML = '';
                    filesSection.classList.add('d-none');
                }

                fillArrangementForm(order);
            })
            .catch(function () {
                alert('Could not load this order.');
                closeModal();
            });
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('open');
        document.body.style.overflow = '';
        currentOrderId = null;
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

    function saveOrder(payload, button, busyLabel, doneLabel) {
        var originalHtml = button.innerHTML;
        button.disabled = true;
        button.textContent = busyLabel;

        // The CSRF token is added here so every caller of saveOrder is covered.
        payload.csrf_token = adminDashboardConfig.csrfToken;

        return fetch(basePath + 'includes/admin/order-update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            button.disabled = false;
            button.innerHTML = originalHtml;
            alert(data && data.success ? (data.message || doneLabel) : (data && data.message ? data.message : 'Update failed.'));
            if (data && data.success) {
                closeModal();
                loadOrders(ordersPagination.currentPage);
                if (recentOrdersTbody) updateOverviewStats();
            }
        })
        .catch(function () {
            button.disabled = false;
            button.innerHTML = originalHtml;
            alert('Update failed. Please try again.');
        });
    }

    if (updateBtn) {
        updateBtn.addEventListener('click', function () {
            if (!currentOrderId) return;
            saveOrder(
                { id: currentOrderId, status: document.getElementById('modal-status-select').value },
                updateBtn,
                'Updating...',
                'Status updated.'
            );
        });
    }

    if (arrSaveBtn) {
        arrSaveBtn.addEventListener('click', function () {
            if (!currentOrderId) return;
            saveOrder(
                { id: currentOrderId, record: readArrangementForm() },
                arrSaveBtn,
                'Saving...',
                'Arrangement saved.'
            );
        });
    }

    /* ----- Logout ----- */
    var logoutBtn = document.getElementById('adm-logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout from admin?')) {
                window.location.href = basePath + 'includes/logout.php';
            }
        });
    }

    // Initial loads (only for the sections present on this page)
    if (document.getElementById('admin-orders-tbody')) {
        loadOrders(1);
    }
    if (document.getElementById('admin-customers-tbody')) {
        loadCustomers(1);
    }

});
