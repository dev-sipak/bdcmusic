document.addEventListener('DOMContentLoaded', function () {

    var orders = adminDashboardConfig.orders;
    var allFiles = adminDashboardConfig.files || [];
    var basePath = adminDashboardConfig.basePath;

    initTabNav('.adm-nav-btn[data-tab]', '.adm-tab', 'adm-tab-', '.adm-sidebar', '#admin-menu-toggle');

    /* ----- Overview Stats ----- */
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

        var recentTbody = document.getElementById('recent-orders-tbody');
        var recent = orders.slice(0, 10);
        recentTbody.innerHTML = recent.map(function (o) {
            return '<tr>' +
                '<td><strong>' + o.id + '</strong></td>' +
                '<td>' + o.customer + '</td>' +
                '<td>' + (o.phone || 'N/A') + '</td>' +
                '<td>' + o.service + '</td>' +
                '<td>\u20B9' + o.amount + '</td>' +
                '<td>' + statusBadgeHtml(o.status) + '</td>' +
            '</tr>';
        }).join('');
    }

    updateOverviewStats();

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

    document.getElementById('admin-order-status').addEventListener('change', function () {
        ordersFilters.status = this.value;
        loadOrders(1);
    });
    document.getElementById('admin-order-service').addEventListener('change', function () {
        ordersFilters.service = this.value;
        loadOrders(1);
    });

    var orderSearchTimer;
    document.getElementById('admin-order-search').addEventListener('input', function () {
        var val = this.value;
        clearTimeout(orderSearchTimer);
        orderSearchTimer = setTimeout(function () {
            ordersFilters.search = val;
            loadOrders(1);
        }, 400);
    });

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

    /* ----- Modal ----- */
    var modal     = document.getElementById('admin-order-modal');
    var backdrop  = document.getElementById('modal-backdrop');
    var closeBtn  = document.getElementById('modal-close');
    var currentOrderId = null;

    function openOrderModal(orderId) {
        // Fetch the single order from the full orders list (overview data)
        var order = orders.find(function (o) { return o.id === orderId; });
        if (!order) return;

        currentOrderId = orderId;
        document.getElementById('modal-order-id').textContent  = 'Order ' + order.id;
        document.getElementById('modal-customer').textContent  = order.customer;
        document.getElementById('modal-email').textContent     = order.email;
        document.getElementById('modal-phone').textContent     = order.phone || 'N/A';
        document.getElementById('modal-service').textContent   = order.service;
        document.getElementById('modal-item').textContent      = order.item;
        document.getElementById('modal-date').textContent      = order.date;
        document.getElementById('modal-amount').textContent    = '\u20B9' + order.amount;
        document.getElementById('modal-status-select').value   = order.status;

        var filesContainer = document.getElementById('modal-files-list');
        var filesSection   = document.getElementById('modal-files-section');
        if (filesContainer && filesSection) {
            var orderFiles = allFiles.filter(function (f) { return f.booking_id === order.id; });
            if (orderFiles.length === 0) {
                filesSection.classList.add('d-none');
            } else {
                filesSection.classList.remove('d-none');
                filesContainer.innerHTML = orderFiles.map(function (f) {
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
                        '<span class="modal-file-name">' + f.original_name + '</span>' +
                        '<span class="modal-file-size">' + sizeStr + '</span>' +
                        '<a href="' + basePath + 'includes/download-file.php?file=' + encodeURIComponent(f.file_path) + '" class="modal-file-download" target="_blank"><i class="fa-solid fa-download"></i></a>' +
                    '</div>';
                }).join('');
            }
        }

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        currentOrderId = null;
    }

    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    document.getElementById('modal-update-btn').addEventListener('click', function () {
        if (!currentOrderId) return;
        var newStatus = document.getElementById('modal-status-select').value;
        var order = orders.find(function (o) { return o.id === currentOrderId; });
        if (order) {
            order.status = newStatus;
            loadOrders(ordersPagination.currentPage);
            updateOverviewStats();
        }
        closeModal();
    });

    /* ----- Logout ----- */
    document.getElementById('adm-logout-btn').addEventListener('click', function () {
        if (confirm('Are you sure you want to logout from admin?')) {
            window.location.href = basePath + 'includes/logout.php';
        }
    });

    // Initial loads
    loadOrders(1);
    loadCustomers(1);

});
