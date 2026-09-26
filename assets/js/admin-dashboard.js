document.addEventListener('DOMContentLoaded', function () {

    var orders = adminDashboardConfig.orders;
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
        var total      = orders.length;
        var pending    = orders.filter(function (o) { return o.status === 'pending'; }).length;
        var processing = orders.filter(function (o) { return o.status === 'processing'; }).length;
        var delivered  = orders.filter(function (o) { return o.status === 'delivered'; }).length;
        var customers  = {};
        orders.forEach(function (o) { customers[o.email] = true; });

        document.getElementById('stat-total').textContent      = total;
        document.getElementById('stat-pending').textContent     = pending;
        document.getElementById('stat-processing').textContent  = processing;
        document.getElementById('stat-delivered').textContent   = delivered;
        document.getElementById('stat-customers').textContent   = Object.keys(customers).length;

        if (!recentOrdersTbody) return;

        var recent = orders.slice(0, 10);
        recentOrdersTbody.innerHTML = recent.map(function (o) {
            return '<tr>' +
                '<td><strong>' + o.id + '</strong></td>' +
                '<td>' + o.customer + '</td>' +
                '<td>' + (o.phone || 'N/A') + '</td>' +
                '<td>' + o.service + '</td>' +
                '<td>₹' + o.amount + '</td>' +
                '<td>' + statusBadgeHtml(o.status) + '</td>' +
            '</tr>';
        }).join('');
    }

    if (recentOrdersTbody) {
        updateOverviewStats();
    }


    /* ----- Orders Management (Paginated) ----- */
    var ordersPagination = { currentPage: 1, totalPages: 1 };
    var ordersFilters = { status: 'all', service: 'all', search: '' };

    function loadOrders(page) {
        page = page || 1;
        var params = 'page=' + page + '&per_page=10';
        if (ordersFilters.status !== 'all') params += '&status=' + encodeURIComponent(ordersFilters.status);
        if (ordersFilters.service !== 'all') params += '&service=' + encodeURIComponent(ordersFilters.service);
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
            return '<tr>' +
                '<td><strong>' + o.id + '</strong></td>' +
                '<td>' + o.customer + '</td>' +
                '<td>' + (o.phone || 'N/A') + '</td>' +
                '<td>' + o.service + '</td>' +
                '<td>' + o.date + '</td>' +
                '<td>\u20B9' + o.amount + '</td>' +
                '<td>' + statusBadgeHtml(o.status) + '</td>' +
                '<td><button class="adm-view-btn" data-order-id="' + o.id + '"><i class="fa-solid fa-eye"></i></button></td>' +
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
                '<td><strong>' + c.name + '</strong></td>' +
                '<td>' + c.email + '</td>' +
                '<td>' + (c.phone || 'N/A') + '</td>' +
                '<td>' + c.total_orders + '</td>' +
                '<td>\u20B9' + c.total_spent + '</td>' +
                '<td>' + (c.last_order || 'N/A') + '</td>' +
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

    function openOrderModal(orderId) {
        currentOrderId = orderId;
        document.getElementById('modal-customer').textContent = '';
        document.getElementById('modal-status-select').value = 'pending';
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
                document.getElementById('modal-amount').textContent    = '\u20B9' + order.amount;
                document.getElementById('modal-status-select').value   = order.status;

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
