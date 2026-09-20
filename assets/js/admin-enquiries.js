document.addEventListener('DOMContentLoaded', function () {

    var basePath = adminDashboardConfig.basePath;
    var enquiries = [];

    function loadEnquiries() {
        return fetch(basePath + 'includes/admin/enquiries-list.php')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) { enquiries = data.enquiries; }
                renderEnquiriesTable();
                return enquiries;
            });
    }

    function renderEnquiriesTable() {
        var tbody = document.getElementById('adm-enquiries-tbody');
        var emptyMsg = document.getElementById('adm-enquiries-empty');
        var statusFilter = document.getElementById('enquiry-status-filter');
        if (!tbody) return;

        var filter = statusFilter ? statusFilter.value : 'all';
        var filtered = filter === 'all' ? enquiries : enquiries.filter(function (e) { return e.status === filter; });

        if (filtered.length === 0) {
            tbody.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('d-none');
            return;
        }
        if (emptyMsg) emptyMsg.classList.add('d-none');

        tbody.innerHTML = filtered.map(function (e) {
            var statusBadge = '';
            if (e.status === 'new') {
                statusBadge = '<span class="panel-status-badge status-pending">New</span>';
            } else if (e.status === 'read') {
                statusBadge = '<span class="panel-status-badge status-processing">Read</span>';
            } else {
                statusBadge = '<span class="panel-status-badge status-delivered">Replied</span>';
            }

            var msg = e.message || '';
            if (msg.length > 60) { msg = msg.substring(0, 60) + '...'; }

            return '<tr>' +
                '<td>' + e.id + '</td>' +
                '<td>' + (e.artist_name || 'N/A') + '</td>' +
                '<td><strong>' + e.name + '</strong></td>' +
                '<td>' + e.email + '</td>' +
                '<td>' + (e.phone || '-') + '</td>' +
                '<td title="' + (e.message || '').replace(/"/g, '&quot;') + '">' + msg + '</td>' +
                '<td>' + e.created_at + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' +
                    '<select class="adm-filter-select enquiry-status-select" data-id="' + e.id + '" style="font-size:0.78rem;padding:4px 8px;">' +
                        '<option value="new"' + (e.status === 'new' ? ' selected' : '') + '>New</option>' +
                        '<option value="read"' + (e.status === 'read' ? ' selected' : '') + '>Read</option>' +
                        '<option value="replied"' + (e.status === 'replied' ? ' selected' : '') + '>Replied</option>' +
                    '</select>' +
                '</td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('.enquiry-status-select').forEach(function (sel) {
            sel.addEventListener('change', function () {
                var id = parseInt(this.getAttribute('data-id'));
                var status = this.value;
                updateEnquiryStatus(id, status);
            });
        });
    }

    function updateEnquiryStatus(id, status) {
        fetch(basePath + 'includes/admin/enquiry-update-status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, status: status })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var e = enquiries.find(function (enq) { return enq.id === id; });
                if (e) e.status = status;
                renderEnquiriesTable();
            } else {
                alert(data.message);
            }
        })
        .catch(function () { alert('Update failed.'); });
    }

    var statusFilter = document.getElementById('enquiry-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', renderEnquiriesTable);
    }

    loadEnquiries();
});
