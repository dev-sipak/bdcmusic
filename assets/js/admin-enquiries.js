document.addEventListener('DOMContentLoaded', function () {

    var basePath = adminDashboardConfig.basePath;
    var enquiriesPagination = { currentPage: 1, totalPages: 1 };
    var enquiriesStatus = 'all';

    initSidebarToggle('.adm-sidebar', '#admin-menu-toggle');

    function loadEnquiries(page) {
        page = page || 1;
        var params = 'page=' + page + '&per_page=10';
        if (enquiriesStatus !== 'all') params += '&status=' + encodeURIComponent(enquiriesStatus);

        return fetch(basePath + 'includes/admin/enquiries-list.php?' + params)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    enquiriesPagination = data.pagination;
                    renderEnquiriesTable(data.enquiries);
                }
                return data.enquiries || [];
            });
    }

    function renderEnquiriesTable(enquiriesList) {
        var tbody = document.getElementById('adm-enquiries-tbody');
        var emptyMsg = document.getElementById('adm-enquiries-empty');
        var pagWrap = document.getElementById('adm-enquiries-pagination');
        if (!tbody) return;

        if (!enquiriesList || enquiriesList.length === 0) {
            tbody.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('d-none');
            if (pagWrap) pagWrap.innerHTML = '';
            return;
        }
        if (emptyMsg) emptyMsg.classList.add('d-none');

        tbody.innerHTML = enquiriesList.map(function (e) {
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

            var eyeIcon = '<button class="btn enquiry-view-btn" data-id="' + e.id + '" title="View Enquiry" style="background:transparent;border:none;cursor:pointer;padding:4px 8px;color:var(--primary);font-size:1rem;">' +
                '<i class="fa-solid fa-eye"></i></button>';

            return '<tr>' +
                '<td><strong>' + e.name + '</strong></td>' +
                '<td>' + e.email + '</td>' +
                '<td>' + (e.phone || '-') + '</td>' +
                '<td title="' + (e.message || '').replace(/"/g, '&quot;') + '">' + msg + '</td>' +
                '<td>' + e.created_at + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + eyeIcon + '</td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('.enquiry-view-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = parseInt(this.getAttribute('data-id'));
                openEnquiryModal(id, enquiriesList);
            });
        });

        if (pagWrap) {
            pagWrap.innerHTML = renderPaginationHtml(enquiriesPagination, function (page) {
                loadEnquiries(page);
            });
        }
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
                loadEnquiries(enquiriesPagination.currentPage);
            } else {
                alert(data.message);
            }
        })
        .catch(function () { alert('Update failed.'); });
    }

    function openEnquiryModal(id, enquiriesList) {
        var e = enquiriesList.find(function (enq) { return enq.id === id; });
        if (!e) return;

        document.getElementById('reply-enquiry-id').value = e.id;
        document.getElementById('reply-enquiry-name').textContent = e.name;
        document.getElementById('reply-enquiry-email').textContent = e.email;
        document.getElementById('reply-enquiry-message').textContent = e.message || 'No message provided.';
        document.getElementById('reply-message').value = '';

        var prevWrap = document.getElementById('reply-previous-wrap');
        if (prevWrap) prevWrap.style.display = 'none';

        document.getElementById('reply-error').style.display = 'none';
        document.getElementById('reply-success').style.display = 'none';

        document.getElementById('adm-reply-modal').classList.add('open');

        if (e.status === 'new') {
            updateEnquiryStatus(id, 'read');
        }
    }

    function closeReplyModal() {
        document.getElementById('adm-reply-modal').classList.remove('open');
    }

    function sendReply() {
        var id = parseInt(document.getElementById('reply-enquiry-id').value);
        var message = document.getElementById('reply-message').value.trim();

        if (!message) {
            var errEl = document.getElementById('reply-error');
            errEl.textContent = 'Please type a reply before sending.';
            errEl.style.display = 'block';
            document.getElementById('reply-success').style.display = 'none';
            return;
        }

        var sendBtn = document.getElementById('reply-send-btn');
        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';

        fetch(basePath + 'includes/admin/enquiry-reply.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, reply_message: message })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send Reply';

            if (data.success) {
                loadEnquiries(enquiriesPagination.currentPage);

                document.getElementById('reply-success').textContent = data.message || 'Reply sent successfully.';
                document.getElementById('reply-success').style.display = 'block';
                document.getElementById('reply-error').style.display = 'none';
                document.getElementById('reply-message').value = '';

                setTimeout(closeReplyModal, 2000);
            } else {
                document.getElementById('reply-error').textContent = data.message || 'Failed to send reply.';
                document.getElementById('reply-error').style.display = 'block';
                document.getElementById('reply-success').style.display = 'none';
            }
        })
        .catch(function () {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send Reply';
            document.getElementById('reply-error').textContent = 'Network error. Please try again.';
            document.getElementById('reply-error').style.display = 'block';
            document.getElementById('reply-success').style.display = 'none';
        });
    }

    var statusFilter = document.getElementById('enquiry-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            enquiriesStatus = this.value;
            loadEnquiries(1);
        });
    }

    var replyModalClose = document.getElementById('reply-modal-close');
    if (replyModalClose) {
        replyModalClose.addEventListener('click', closeReplyModal);
    }

    var replyModalBackdrop = document.getElementById('reply-modal-backdrop');
    if (replyModalBackdrop) {
        replyModalBackdrop.addEventListener('click', closeReplyModal);
    }

    var replyModalCancel = document.getElementById('reply-modal-cancel');
    if (replyModalCancel) {
        replyModalCancel.addEventListener('click', closeReplyModal);
    }

    var replySendBtn = document.getElementById('reply-send-btn');
    if (replySendBtn) {
        replySendBtn.addEventListener('click', sendReply);
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

    // Initial load
    if (document.getElementById('adm-enquiries-tbody')) {
        loadEnquiries(1);
    }
});
