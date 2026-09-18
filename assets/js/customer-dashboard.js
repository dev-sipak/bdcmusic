document.addEventListener('DOMContentLoaded', function () {

    var orders = customerDashboardConfig.orders;
    var basePath = customerDashboardConfig.basePath;
    var statusSteps = ['Pending', 'Processing', 'Shipped', 'Delivered'];

    initTabNav('.panel-nav-btn[data-tab]', '.panel-tab', 'tab-', '.panel-sidebar', '#dash-menu-toggle');

    /* ----- Render Orders Table ----- */
    function renderOrders() {
        var statusVal  = document.getElementById('order-status-filter').value;
        var serviceVal = document.getElementById('order-service-filter').value;
        var tbody      = document.getElementById('orders-tbody');
        var emptyMsg   = document.getElementById('orders-empty');

        var filtered = orders.filter(function (o) {
            if (statusVal !== 'all' && o.status !== statusVal) return false;
            if (serviceVal !== 'all' && o.service !== serviceVal) return false;
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
                '<td>' + o.service + '</td>' +
                '<td>' + o.item + '</td>' +
                '<td>' + o.date + '</td>' +
                '<td>' + o.amount + '</td>' +
                '<td>' + statusBadgeHtml(o.status) + '</td>' +
            '</tr>';
        }).join('');
    }

    document.getElementById('order-status-filter').addEventListener('change', renderOrders);
    document.getElementById('order-service-filter').addEventListener('change', renderOrders);
    renderOrders();

    /* ----- My Uploads ----- */
    var uploadsTbody = document.getElementById('uploads-tbody');
    var uploadsEmpty = document.getElementById('uploads-empty');
    var allUploads = [];

    orders.forEach(function (o) {
        var files = o.files || [];
        files.forEach(function (f) {
            allUploads.push({
                orderId: o.id,
                service: o.service,
                file: f
            });
        });
    });

    if (allUploads.length === 0) {
        uploadsTbody.innerHTML = '';
        uploadsEmpty.classList.remove('d-none');
    } else {
        uploadsEmpty.classList.add('d-none');
        uploadsTbody.innerHTML = allUploads.map(function (u) {
            var sizeKB = (u.file.size / 1024).toFixed(1);
            var sizeMB = (u.file.size / (1024 * 1024)).toFixed(1);
            var sizeStr = u.file.size > 1048576 ? sizeMB + ' MB' : sizeKB + ' KB';
            return '<tr>' +
                '<td><strong>' + u.orderId + '</strong></td>' +
                '<td>' + u.service + '</td>' +
                '<td>' + u.file.name + '</td>' +
                '<td>' + sizeStr + '</td>' +
                '<td><a href="' + basePath + 'includes/download-file.php?file=' + encodeURIComponent(u.file.path) + '" class="panel-download-btn" target="_blank"><i class="fa-solid fa-download"></i> Download</a></td>' +
            '</tr>';
        }).join('');
    }

    /* ----- Order History ----- */
    var historyTbody = document.getElementById('history-tbody');
    historyTbody.innerHTML = orders.map(function (o) {
        return historyRowHtml(o);
    }).join('');

    /* ----- Order Tracking ----- */
    var trackingList = document.getElementById('tracking-list');
    var activeOrders = orders.filter(function (o) {
        return o.status !== 'Delivered' && o.status !== 'Cancelled';
    });

    trackingList.innerHTML = activeOrders.map(function (o) {
        var currentIdx = statusSteps.indexOf(o.status);
        if (currentIdx === -1) currentIdx = 0;

        var stepsHtml = statusSteps.map(function (step, i) {
            var cls = i < currentIdx ? 'done' : (i === currentIdx ? 'current' : '');
            return '<div class="tracking-step ' + cls + '">' +
                '<div class="step-dot"><i class="fa-solid fa-check"></i></div>' +
                '<span>' + step + '</span>' +
            '</div>';
        }).join('');

        return '<div class="tracking-card">' +
            '<div class="tracking-header">' +
                '<strong>' + o.id + '</strong>' +
                '<span>' + o.service + '</span>' +
            '</div>' +
            '<div class="tracking-progress">' + stepsHtml + '</div>' +
            '<p class="tracking-item">' + o.item + ' &mdash; ' + o.amount + '</p>' +
        '</div>';
    }).join('');

    if (activeOrders.length === 0) {
        trackingList.innerHTML = '<p class="panel-empty">No active orders to track.</p>';
    }

    /* ----- Order Details ----- */
    var detailSelect = document.getElementById('details-order-select');
    var detailView   = document.getElementById('order-detail-view');

    orders.forEach(function (o) {
        var opt = document.createElement('option');
        opt.value = o.id;
        opt.textContent = o.id + ' - ' + o.item;
        detailSelect.appendChild(opt);
    });

    detailSelect.addEventListener('change', function () {
        var orderId = this.value;
        if (!orderId) {
            detailView.classList.add('d-none');
            return;
        }

        var order = orders.find(function (o) { return o.id === orderId; });
        if (!order) return;

        document.getElementById('detail-order-id').textContent = order.id;
        var statusBadge = document.getElementById('detail-order-status');
        statusBadge.textContent = order.status;
        statusBadge.className = 'panel-status-badge status-' + order.status.toLowerCase();

        document.getElementById('detail-service').textContent = order.service;
        document.getElementById('detail-item').textContent    = order.item;
        document.getElementById('detail-date').textContent    = order.date;
        document.getElementById('detail-amount').textContent  = order.amount;

        var timeline = document.getElementById('detail-timeline');
        var currentIdx = statusSteps.indexOf(order.status);
        if (currentIdx === -1) currentIdx = 0;

        timeline.innerHTML = statusSteps.map(function (step, i) {
            var cls = i < currentIdx ? 'done' : (i === currentIdx ? 'current' : '');
            var icon = i < currentIdx ? 'fa-check-circle' : (i === currentIdx ? 'fa-circle-dot' : 'fa-circle');
            return '<div class="timeline-item ' + cls + '">' +
                '<i class="fa-solid ' + icon + '"></i>' +
                '<div>' +
                    '<strong>' + step + '</strong>' +
                    '<span>' + (i <= currentIdx ? 'Completed' : 'Pending') + '</span>' +
                '</div>' +
            '</div>';
        }).join('');

        detailView.classList.remove('d-none');
    });

    /* ----- Logout ----- */
    document.getElementById('logout-btn').addEventListener('click', function () {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = basePath + 'includes/logout.php';
        }
    });

});
