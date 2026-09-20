document.addEventListener('DOMContentLoaded', function () {

    var orders = customerDashboardConfig.orders;
    var uploads = customerDashboardConfig.uploads || [];
    var basePath = customerDashboardConfig.basePath;
    var statusSteps = ['Pending', 'Processing', 'Hold', 'Delivered'];

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
                '<td>' + o.date + '</td>' +
                '<td>\u20B9' + o.amount + '</td>' +
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

    if (uploads.length === 0) {
        uploadsTbody.innerHTML = '';
        uploadsEmpty.classList.remove('d-none');
    } else {
        uploadsEmpty.classList.add('d-none');
        uploadsTbody.innerHTML = uploads.map(function (u) {
            var sizeKB = (u.file_size / 1024).toFixed(1);
            var sizeMB = (u.file_size / (1024 * 1024)).toFixed(1);
            var sizeStr = u.file_size > 1048576 ? sizeMB + ' MB' : sizeKB + ' KB';
            return '<tr>' +
                '<td><strong>' + u.booking_id + '</strong></td>' +
                '<td>' + u.original_name + '</td>' +
                '<td>' + sizeStr + '</td>' +
                '<td><a href="' + basePath + 'includes/download-file.php?file=' + encodeURIComponent(u.file_path) + '" class="panel-download-btn" target="_blank"><i class="fa-solid fa-download"></i> Download</a></td>' +
            '</tr>';
        }).join('');
    }

    /* ----- Order History ----- */
    var historyTbody = document.getElementById('history-tbody');
    historyTbody.innerHTML = orders.map(function (o) {
        return historyRowHtml(o);
    }).join('');

    /* ----- Order Details ----- */
    var detailSelect = document.getElementById('details-order-select');
    var detailView   = document.getElementById('order-detail-view');

    orders.forEach(function (o) {
        var opt = document.createElement('option');
        opt.value = o.id;
        opt.textContent = o.id;
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
        document.getElementById('detail-date').textContent    = order.date;
        document.getElementById('detail-amount').textContent  = '\u20B9' + order.amount;

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
                '</div>' +
            '</div>';
        }).join('');

        detailView.classList.remove('d-none');
    });

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

            fetch(customerDashboardConfig.uploadProfilePicUrl, { method: 'POST', body: fd })
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

            fetch(customerDashboardConfig.updateProfileUrl, { method: 'POST', body: fd })
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

            fetch(customerDashboardConfig.changePasswordUrl, { method: 'POST', body: fd })
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
    document.getElementById('logout-btn').addEventListener('click', function () {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = basePath + 'includes/logout';
        }
    });

});
