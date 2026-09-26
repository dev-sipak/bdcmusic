document.addEventListener('DOMContentLoaded', function () {

    var config = (typeof customerDashboardConfig !== 'undefined') ? customerDashboardConfig : null;
    if (!config) return;

    var basePath = config.basePath;

    initSidebarToggle('.panel-sidebar', '#dash-menu-toggle');

    function esc(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /* ----- My Orders (Paginated) ----- */
    var ordersPagination = { currentPage: 1, totalPages: 1 };
    var ordersFilters = { status: 'all', service: 'all' };

    function loadOrders(page) {
        page = page || 1;
        var params = 'page=' + page + '&per_page=10';
        if (ordersFilters.status !== 'all') params += '&status=' + encodeURIComponent(ordersFilters.status);
        if (ordersFilters.service !== 'all') params += '&service=' + encodeURIComponent(ordersFilters.service);

        fetch(basePath + 'includes/customer/orders-list.php?' + params)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    ordersPagination = data.pagination;
                    renderOrders(data.orders);
                }
            });
    }

    function renderOrders(ordersList) {
        var tbody    = document.getElementById('orders-tbody');
        var emptyMsg = document.getElementById('orders-empty');
        var pagWrap  = document.getElementById('orders-pagination');
        if (!tbody) return;

        if (!ordersList || ordersList.length === 0) {
            tbody.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('d-none');
            if (pagWrap) pagWrap.innerHTML = '';
            return;
        }

        if (emptyMsg) emptyMsg.classList.add('d-none');
        tbody.innerHTML = ordersList.map(function (o) {
            return '<tr>' +
                '<td><strong>' + esc(o.id) + '</strong></td>' +
                '<td>' + esc(o.service) + '</td>' +
                '<td>' + esc(o.date) + '</td>' +
                '<td>\u20B9' + esc(o.amount) + '</td>' +
                '<td>' + statusBadgeHtml(o.status) + '</td>' +
                '<td><button class="adm-view-btn view-order" data-order-id="' + esc(o.id) + '" title="View full order details"><i class="fa-solid fa-eye"></i></button></td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('.view-order').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showOrderDetail(btn.getAttribute('data-order-id'));
            });
        });

        if (pagWrap) {
            pagWrap.innerHTML = renderPaginationHtml(ordersPagination, function (page) {
                loadOrders(page);
            });
        }
    }

    var orderStatusFilter = document.getElementById('order-status-filter');
    var orderServiceFilter = document.getElementById('order-service-filter');

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

    /* ----- Order Detail Modal ("View More" on My Orders) -----
       Replaces the old standalone Order Details page. Shows every field the
       customer submitted when placing the order, plus the arrangements the
       admin has recorded in service_records.                                */
    var orderModal    = document.getElementById('panel-order-modal');
    var orderBackdrop = document.getElementById('panel-modal-backdrop');
    var orderCloseBtn = document.getElementById('pm-close');

    function showOrderDetail(orderId) {
        if (!orderModal || !orderId) return;

        var statusEl = document.getElementById('pm-status');
        if (statusEl) statusEl.innerHTML = '<span class="panel-muted">Loading...</span>';

        orderModal.classList.add('open');
        document.body.style.overflow = 'hidden';

        fetch(config.orderDetailUrl + '?id=' + encodeURIComponent(orderId))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.success) {
                    if (statusEl) statusEl.innerHTML = '<span class="panel-muted">' + esc(data && data.message ? data.message : 'Could not load this order.') + '</span>';
                    return;
                }

                var order = data.order;

                document.getElementById('pm-order-id').textContent = 'Order ' + order.id;
                document.getElementById('pm-service').textContent  = order.service;
                document.getElementById('pm-date').textContent    = order.date;
                document.getElementById('pm-amount').textContent  = '\u20B9' + Number(order.amount || 0).toFixed(2);
                document.getElementById('pm-payment').textContent = order.paymentStatus;

                // Status, plus the service progress steps the admin recorded.
                var progressHtml = statusBadgeHtml(order.status);
                if (order.progress && order.progress.length) {
                    progressHtml += '<div class="tracking-list" style="margin-top:14px;">' +
                        order.progress.map(function (step) {
                            var icon = step.state === 'done' ? 'fa-check-circle'
                                : (step.state === 'current' ? 'fa-circle-dot' : 'fa-circle');
                            return '<div class="timeline-item ' + esc(step.state) + '">' +
                                '<i class="fa-solid ' + icon + '"></i>' +
                                '<div><strong>' + esc(step.label) + '</strong></div>' +
                            '</div>';
                        }).join('') + '</div>';
                }
                if (statusEl) statusEl.innerHTML = progressHtml;

                // Everything the customer filled in at order time.
                var fieldsEl = document.getElementById('pm-order-fields');
                var fieldsSection = document.getElementById('pm-order-fields-section');
                if (order.metaFields && order.metaFields.length) {
                    fieldsEl.innerHTML = order.metaFields.map(function (f) {
                        return '<div class="detail-item">' +
                            '<span class="panel-label">' + esc(f.label) + '</span>' +
                            '<span class="panel-value">' + esc(f.value) + '</span>' +
                        '</div>';
                    }).join('');
                    fieldsSection.classList.remove('d-none');
                } else {
                    fieldsEl.innerHTML = '';
                    fieldsSection.classList.add('d-none');
                }

                // Customer note.
                var messageEl   = document.getElementById('pm-message');
                var messageSec  = document.getElementById('pm-message-section');
                if (order.message) {
                    messageEl.textContent = order.message;
                    messageSec.classList.remove('d-none');
                } else {
                    messageSec.classList.add('d-none');
                }

                // Arrangements recorded by admin.
                var arrangementEl = document.getElementById('pm-arrangement');
                var emptyEl        = document.getElementById('pm-arrangement-empty');
                var lockedNote     = document.getElementById('pm-locked-note');
                var columns = ['headline', 'sub_headline', 'location', 'starts_on', 'ends_on'];

                if (order.arrangement) {
                    var labels = order.arrangementFields || {};
                    arrangementEl.innerHTML = columns.map(function (column) {
                        var value = order.arrangement[column];
                        if (!value) return '';
                        var meta = labels[column] || { label: column };
                        return '<div class="detail-item">' +
                            '<span class="panel-label">' + esc(meta.label) + '</span>' +
                            '<span class="panel-value">' + esc(value) + '</span>' +
                        '</div>';
                    }).join('') +
                    (order.arrangement.notes
                        ? '<div class="detail-item" style="grid-column:1/-1;">' +
                          '<span class="panel-label">Notes</span>' +
                          '<span class="panel-value">' + esc(order.arrangement.notes) + '</span>' +
                          '</div>'
                        : '');
                    if (emptyEl) emptyEl.classList.add('d-none');
                } else {
                    arrangementEl.innerHTML = '';
                    if (emptyEl) {
                        emptyEl.textContent = order.unlocked
                            ? 'Our team has not added the arrangements for this order yet.'
                            : 'Arrangements appear here once this order moves into progress.';
                        emptyEl.classList.remove('d-none');
                    }
                }
                if (lockedNote) lockedNote.classList.add('d-none');

                // Files the customer uploaded.
                var filesEl  = document.getElementById('pm-files-list');
                var filesSec = document.getElementById('pm-files-section');
                if (order.files && order.files.length) {
                    filesEl.innerHTML = order.files.map(function (f) {
                        var sizeMB = (f.file_size / (1024 * 1024)).toFixed(1);
                        var sizeKB = (f.file_size / 1024).toFixed(1);
                        var size   = f.file_size > 1048576 ? sizeMB + ' MB' : sizeKB + ' KB';
                        var icon   = 'fa-file';
                        if (f.mime_type && f.mime_type.indexOf('audio') !== -1) icon = 'fa-file-audio';
                        else if (f.mime_type && f.mime_type.indexOf('video') !== -1) icon = 'fa-file-video';
                        else if (f.mime_type && f.mime_type.indexOf('image') !== -1) icon = 'fa-file-image';
                        else if (f.mime_type && f.mime_type.indexOf('pdf') !== -1) icon = 'fa-file-pdf';
                        return '<div class="modal-file-item">' +
                            '<i class="fa-solid ' + icon + ' modal-file-icon"></i>' +
                            '<span class="modal-file-name">' + esc(f.original_name) + '</span>' +
                            '<span class="modal-file-size">' + esc(size) + '</span>' +
                            '<a href="' + basePath + 'includes/download-file.php?file=' + encodeURIComponent(f.file_path) + '" class="modal-file-download" target="_blank"><i class="fa-solid fa-download"></i></a>' +
                        '</div>';
                    }).join('');
                    filesSec.classList.remove('d-none');
                } else {
                    filesEl.innerHTML = '';
                    filesSec.classList.add('d-none');
                }
            })
            .catch(function () {
                if (statusEl) statusEl.innerHTML = '<span class="panel-muted">Could not load this order.</span>';
            });
    }

    function closeOrderModal() {
        if (!orderModal) return;
        orderModal.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (orderCloseBtn) orderCloseBtn.addEventListener('click', closeOrderModal);
    if (orderBackdrop) orderBackdrop.addEventListener('click', closeOrderModal);

    /* ----- Profile Picture Upload ----- */
    var avatarInput   = document.getElementById('avatar-file-input');
    var avatarImg     = document.getElementById('profile-avatar-img');
    var avatarStatus  = document.getElementById('avatar-status');

    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('Image must be under 2 MB.');
                avatarInput.value = '';
                return;
            }

            var allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (allowed.indexOf(file.type) === -1) {
                alert('Only JPG, PNG, and WebP images are allowed.');
                avatarInput.value = '';
                return;
            }

            avatarStatus.textContent = 'Uploading...';
            avatarStatus.classList.remove('d-none');

            var fd = new FormData();
            fd.append('profile_picture', file);

            fetch(config.uploadProfilePicUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        avatarStatus.textContent = data.message;
                        avatarStatus.className = 'panel-avatar-status notice success';

                        var imgEl = avatarImg.tagName === 'IMG' ? avatarImg : null;
                        if (imgEl) {
                            imgEl.src = data.url + '?t=' + Date.now();
                        } else {
                            var newImg = document.createElement('img');
                            newImg.src = data.url + '?t=' + Date.now();
                            newImg.alt = 'Profile';
                            newImg.className = 'panel-avatar-img';
                            newImg.id = 'profile-avatar-img';
                            avatarImg.replaceWith(newImg);
                        }
                    } else {
                        avatarStatus.textContent = data.message;
                        avatarStatus.className = 'panel-avatar-status notice error';
                    }
                    avatarInput.value = '';
                    setTimeout(function () { avatarStatus.classList.add('d-none'); }, 3000);
                })
                .catch(function () {
                    avatarStatus.textContent = 'Upload failed. Please try again.';
                    avatarStatus.className = 'panel-avatar-status notice error';
                    avatarInput.value = '';
                    setTimeout(function () { avatarStatus.classList.add('d-none'); }, 3000);
                });
        });
    }

    /* ----- Edit Profile ----- */
    var profileForm    = document.getElementById('profile-form');
    var profileSuccess = document.getElementById('profile-success');
    var profileError   = document.getElementById('profile-error');
    var profileSaveBtn = document.getElementById('profile-save-btn');

    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
            profileSuccess.classList.add('d-none');
            profileError.classList.add('d-none');

            var name  = document.getElementById('profile-name').value.trim();
            var phone = document.getElementById('profile-phone').value.trim();

            if (!name) {
                profileError.querySelector('#profile-error-msg').textContent = 'Name is required.';
                profileError.classList.remove('d-none');
                return;
            }

            var fd = new FormData();
            fd.append('name', name);
            fd.append('phone', phone);

            profileSaveBtn.disabled = true;
            profileSaveBtn.textContent = 'Saving...';

            fetch(config.updateProfileUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        profileSuccess.querySelector('#profile-success-msg').textContent = data.message;
                        profileSuccess.classList.remove('d-none');
                        profileError.classList.add('d-none');
                    } else {
                        profileError.querySelector('#profile-error-msg').textContent = data.message;
                        profileError.classList.remove('d-none');
                        profileSuccess.classList.add('d-none');
                    }
                    profileSaveBtn.disabled = false;
                    profileSaveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Save Changes';
                })
                .catch(function () {
                    profileError.querySelector('#profile-error-msg').textContent = 'Something went wrong. Please try again.';
                    profileError.classList.remove('d-none');
                    profileSuccess.classList.add('d-none');
                    profileSaveBtn.disabled = false;
                    profileSaveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Save Changes';
                });
        });
    }

    /* ----- Change Password ----- */
    var passForm    = document.getElementById('password-form');
    var passSuccess = document.getElementById('pass-success');
    var passError   = document.getElementById('pass-error');
    var passSaveBtn = document.getElementById('pass-save-btn');

    if (passForm) {
        passForm.addEventListener('submit', function (e) {
            e.preventDefault();
            passSuccess.classList.add('d-none');
            passError.classList.add('d-none');

            var current = document.getElementById('current-password').value;
            var newPass = document.getElementById('new-password').value;
            var confirm = document.getElementById('confirm-password').value;

            if (!current || !newPass || !confirm) {
                passError.querySelector('#pass-error-msg').textContent = 'Please fill in all fields.';
                passError.classList.remove('d-none');
                return;
            }

            if (newPass.length < 6) {
                passError.querySelector('#pass-error-msg').textContent = 'New password must be at least 6 characters.';
                passError.classList.remove('d-none');
                return;
            }

            if (newPass !== confirm) {
                passError.querySelector('#pass-error-msg').textContent = 'New passwords do not match.';
                passError.classList.remove('d-none');
                return;
            }

            var fd = new FormData();
            fd.append('current_password', current);
            fd.append('new_password', newPass);
            fd.append('confirm_password', confirm);

            passSaveBtn.disabled = true;
            passSaveBtn.textContent = 'Updating...';

            fetch(config.changePasswordUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        passSuccess.querySelector('#pass-success-msg').textContent = data.message;
                        passSuccess.classList.remove('d-none');
                        passError.classList.add('d-none');
                        passForm.reset();
                    } else {
                        passError.querySelector('#pass-error-msg').textContent = data.message;
                        passError.classList.remove('d-none');
                        passSuccess.classList.add('d-none');
                    }
                    passSaveBtn.disabled = false;
                    passSaveBtn.innerHTML = '<i class="fa-solid fa-key"></i> Update Password';
                })
                .catch(function () {
                    passError.querySelector('#pass-error-msg').textContent = 'Something went wrong. Please try again.';
                    passError.classList.remove('d-none');
                    passSuccess.classList.add('d-none');
                    passSaveBtn.disabled = false;
                    passSaveBtn.innerHTML = '<i class="fa-solid fa-key"></i> Update Password';
                });
        });
    }

    /* ----- Logout ----- */
    var logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = basePath + 'includes/logout';
            }
        });
    }

    // Initial loads (only for the sections present on this page)
    if (document.getElementById('orders-tbody')) {
        loadOrders(1);
    }

});
