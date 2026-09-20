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

    /* ----- Orders Management ----- */
    function renderAdminOrders() {
        var statusVal  = document.getElementById('admin-order-status').value;
        var serviceVal = document.getElementById('admin-order-service').value;
        var searchVal  = document.getElementById('admin-order-search').value.toLowerCase();
        var tbody      = document.getElementById('admin-orders-tbody');
        var emptyMsg   = document.getElementById('admin-orders-empty');

        var filtered = orders.filter(function (o) {
            if (statusVal !== 'all' && o.status !== statusVal) return false;
            if (serviceVal !== 'all' && o.service !== serviceVal) return false;
            if (searchVal) {
                var haystack = (o.id + ' ' + o.customer + ' ' + o.email + ' ' + o.phone).toLowerCase();
                if (haystack.indexOf(searchVal) === -1) return false;
            }
            return true;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = '';
            emptyMsg.classList.remove('d-none');
            return;
        }

        emptyMsg.classList.add('d-none');
        tbody.innerHTML = filtered.map(function (o) {
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
    }

    document.getElementById('admin-order-status').addEventListener('change', renderAdminOrders);
    document.getElementById('admin-order-service').addEventListener('change', renderAdminOrders);
    document.getElementById('admin-order-search').addEventListener('input', renderAdminOrders);
    renderAdminOrders();

    /* ----- Customers ----- */
    var customersData = {};
    orders.forEach(function (o) {
        if (!customersData[o.email]) {
            customersData[o.email] = {
                name:       o.customer,
                email:      o.email,
                phone:      o.phone || '',
                totalOrders: 0,
                totalSpent:  0,
                lastOrder:   o.date
            };
        }
        customersData[o.email].totalOrders++;
        var amt = parseFloat(o.amount.replace(/[₹$,]/g, ''));
        customersData[o.email].totalSpent += amt;
        if (o.date > customersData[o.email].lastOrder) {
            customersData[o.email].lastOrder = o.date;
        }
    });

    var custTbody = document.getElementById('admin-customers-tbody');
    var custArr   = Object.values(customersData);
    custTbody.innerHTML = custArr.map(function (c) {
        return '<tr>' +
            '<td><strong>' + c.name + '</strong></td>' +
            '<td>' + c.email + '</td>' +
            '<td>' + c.phone + '</td>' +
            '<td>' + c.totalOrders + '</td>' +
            '<td>₹' + c.totalSpent.toLocaleString() + '</td>' +
            '<td>' + c.lastOrder + '</td>' +
        '</tr>';
    }).join('');

    /* ----- Modal ----- */
    var modal     = document.getElementById('admin-order-modal');
    var backdrop  = document.getElementById('modal-backdrop');
    var closeBtn  = document.getElementById('modal-close');
    var currentOrderId = null;

    function openOrderModal(orderId) {
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
            renderAdminOrders();
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

});
