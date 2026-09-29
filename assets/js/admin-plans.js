document.addEventListener('DOMContentLoaded', function () {

    var basePath = adminDashboardConfig.basePath;

    var services = [];
    var groups = [];
    var plans = [];
    var plansPagination = { currentPage: 1, totalPages: 1 };
    var planFilters = { serviceId: 0, groupKey: '', search: '' };

    initSidebarToggle('.adm-sidebar', '#admin-menu-toggle');

    // ─── Meta: services and package groups ─────────────────────────

    function loadMeta() {
        return fetch(basePath + 'includes/admin/services-list.php?t=' + Date.now())
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert(data.message);
                    return data;
                }
                services = data.services || [];
                groups = data.groups || [];
                renderServicesTable();
                fillServiceFilter();
                fillPlanServiceSelect();
                fillGroupDatalist();
                return data;
            })
            .catch(function () { alert('The services could not be loaded.'); });
    }

    // ─── Services table ────────────────────────────────────────────

    function renderServicesTable() {
        var tbody = document.getElementById('adm-services-tbody');
        var emptyMsg = document.getElementById('adm-services-empty');
        if (!tbody) return;

        if (!services.length) {
            tbody.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('d-none');
            return;
        }
        if (emptyMsg) emptyMsg.classList.add('d-none');

        tbody.innerHTML = services.map(function (s) {
            return '<tr>' +
                '<td><strong>' + esc(s.name) + '</strong></td>' +
                '<td>' + esc(s.slug) + '</td>' +
                '<td>' + (s.booking_mode === 'quote' ? 'Quote only' : 'Packages') + '</td>' +
                '<td>' + esc(s.price_note || '—') + '</td>' +
                '<td>' + esc(s.plan_count) + '</td>' +
                '<td>' +
                    (s.is_active == 1
                        ? '<span class="panel-status-badge status-delivered">Active</span>'
                        : '<span class="panel-status-badge status-cancelled">Inactive</span>') + '</td>' +
                '<td>' +
                    '<button class="adm-view-btn adm-edit-service" data-id="' + esc(s.id) + '"><i class="fa-solid fa-pen"></i></button> ' +
                    '<button class="adm-view-btn adm-delete-service" data-id="' + esc(s.id) + '" style="color:#ef4444;border-color:#fca5a5;"><i class="fa-solid fa-trash"></i></button>' +
                '</td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('.adm-edit-service').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openServiceModal(parseInt(this.getAttribute('data-id'), 10));
            });
        });
        tbody.querySelectorAll('.adm-delete-service').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (confirm('Remove this service from the site? Existing orders keep their record.')) {
                    deleteService(parseInt(this.getAttribute('data-id'), 10));
                }
            });
        });
    }

    function fillServiceFilter() {
        var select = document.getElementById('adm-plan-service-filter');
        if (!select) return;

        var current = select.value;
        select.innerHTML = '<option value="0">All Services</option>' + services.map(function (s) {
            return '<option value="' + esc(s.id) + '">' + esc(s.name) + '</option>';
        }).join('');
        select.value = current || '0';
        fillGroupFilter();
    }

    function fillGroupFilter() {
        var select = document.getElementById('adm-plan-group-filter');
        if (!select) return;

        var current = select.value;
        var visible = groups.filter(function (g) {
            return !planFilters.serviceId || parseInt(g.service_id, 10) === planFilters.serviceId;
        });

        var seen = {};
        var options = ['<option value="">All Groups</option>'];

        visible.forEach(function (g) {
            var key = g.group_key || '';
            if (seen[key]) return;
            seen[key] = true;
            var label = g.group_label || key || '(no group)';
            options.push('<option value="' + esc(key) + '">' + esc(label) + '</option>');
        });

        select.innerHTML = options.join('');
        select.value = seen[current] ? current : '';
        planFilters.groupKey = select.value;
    }

    // ─── Packages table ────────────────────────────────────────────

    function loadPlans(page) {
        page = page || 1;
        var params = 'page=' + page + '&per_page=15';
        if (planFilters.serviceId) params += '&service_id=' + planFilters.serviceId;
        if (planFilters.groupKey) params += '&group_key=' + encodeURIComponent(planFilters.groupKey);
        if (planFilters.search) params += '&search=' + encodeURIComponent(planFilters.search);

        return fetch(basePath + 'includes/admin/plans-list.php?' + params + '&t=' + Date.now())
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    plansPagination = data.pagination;
                    plans = data.plans || [];
                    renderPlansTable(plans);
                } else {
                    alert(data.message);
                }
                return plans;
            })
            .catch(function () { alert('The packages could not be loaded.'); });
    }

    function renderPlansTable(list) {
        var tbody = document.getElementById('adm-plans-tbody');
        var emptyMsg = document.getElementById('adm-plans-empty');
        var pagWrap = document.getElementById('adm-plans-pagination');
        if (!tbody) return;

        if (!list || !list.length) {
            tbody.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('d-none');
            if (pagWrap) pagWrap.innerHTML = '';
            return;
        }
        if (emptyMsg) emptyMsg.classList.add('d-none');

        tbody.innerHTML = list.map(function (p) {
            var flags = '';
            if (p.is_default == 1) flags += '<span class="panel-status-badge status-delivered" style="margin-right:4px;">Default</span>';
            if (p.is_enquiry == 1) flags += '<span class="panel-status-badge status-pending" style="margin-right:4px;">Enquiry</span>';
            if (p.is_orderable != 1) flags += '<span class="panel-status-badge">Reference only</span>';
            if (!flags) flags = '—';

            return '<tr>' +
                '<td>' + esc(p.service) + '</td>' +
                '<td>' + esc(p.group_label || p.group_key || '—') + '</td>' +
                '<td><strong>' + esc(p.name) + '</strong></td>' +
                '<td>' + (p.is_enquiry == 1 ? '—' : esc(money(p.price))) + '</td>' +
                '<td>' + esc(p.price_note || '—') + '</td>' +
                '<td>' + flags + '</td>' +
                '<td>' +
                    (p.is_active == 1
                        ? '<span class="panel-status-badge status-delivered">Active</span>'
                        : '<span class="panel-status-badge status-cancelled">Inactive</span>') + '</td>' +
                '<td>' +
                    '<button class="adm-view-btn adm-edit-plan" data-id="' + esc(p.id) + '"><i class="fa-solid fa-pen"></i></button> ' +
                    '<button class="adm-view-btn adm-delete-plan" data-id="' + esc(p.id) + '" style="color:#ef4444;border-color:#fca5a5;"><i class="fa-solid fa-trash"></i></button>' +
                '</td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('.adm-edit-plan').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = parseInt(this.getAttribute('data-id'), 10);
                openPlanModal(id, plans);
            });
        });
        tbody.querySelectorAll('.adm-delete-plan').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (confirm('Remove this package from the site? Existing orders keep their record.')) {
                    deletePlan(parseInt(this.getAttribute('data-id'), 10));
                }
            });
        });

        if (pagWrap) {
            pagWrap.innerHTML = renderPaginationHtml(plansPagination, function (page) {
                loadPlans(page);
            });
        }
    }

    var serviceFilter = document.getElementById('adm-plan-service-filter');
    if (serviceFilter) {
        serviceFilter.addEventListener('change', function () {
            planFilters.serviceId = parseInt(this.value, 10) || 0;
            fillGroupFilter();
            loadPlans(1);
        });
    }

    var groupFilter = document.getElementById('adm-plan-group-filter');
    if (groupFilter) {
        groupFilter.addEventListener('change', function () {
            planFilters.groupKey = this.value;
            loadPlans(1);
        });
    }

    var planSearch = document.getElementById('adm-plan-search');
    if (planSearch) {
        var searchTimer = null;
        planSearch.addEventListener('input', function () {
            planFilters.search = this.value.trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { loadPlans(1); }, 250);
        });
    }

    // ─── Service modal ─────────────────────────────────────────────

    var serviceModal = document.getElementById('adm-service-modal');
    var serviceBackdrop = document.getElementById('service-modal-backdrop');
    var serviceCloseBtn = document.getElementById('service-modal-close');
    var serviceCancelBtn = document.getElementById('service-modal-close-btn');
    var addServiceBtn = document.getElementById('adm-add-service-btn');

    function openServiceModal(id) {
        var form = document.getElementById('service-form');
        if (!form) return;

        form.reset();
        document.getElementById('service-modal-id').value = 0;
        document.getElementById('service-modal-title').textContent = id > 0 ? 'Edit Service' : 'Add New Service';
        document.getElementById('service-active').checked = true;
        document.getElementById('service-booking-mode').value = 'packages';

        if (id > 0) {
            var service = services.find(function (s) { return s.id === id; });
            if (service) {
                document.getElementById('service-modal-id').value = service.id;
                document.getElementById('service-name').value = service.name;
                document.getElementById('service-slug').value = service.slug;
                document.getElementById('service-booking-mode').value = service.booking_mode;
                document.getElementById('service-price-note').value = service.price_note || '';
                document.getElementById('service-active').checked = service.is_active == 1;
            }
        }

        serviceModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeServiceModal() {
        if (!serviceModal) return;
        serviceModal.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (addServiceBtn) addServiceBtn.addEventListener('click', function () { openServiceModal(0); });
    if (serviceCloseBtn) serviceCloseBtn.addEventListener('click', closeServiceModal);
    if (serviceCancelBtn) serviceCancelBtn.addEventListener('click', closeServiceModal);
    if (serviceBackdrop) serviceBackdrop.addEventListener('click', closeServiceModal);

    var serviceForm = document.getElementById('service-form');
    if (serviceForm) {
        serviceForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var payload = {
                id: parseInt(document.getElementById('service-modal-id').value, 10) || 0,
                name: document.getElementById('service-name').value.trim(),
                slug: document.getElementById('service-slug').value.trim(),
                booking_mode: document.getElementById('service-booking-mode').value,
                price_note: document.getElementById('service-price-note').value.trim(),
                is_active: document.getElementById('service-active').checked ? 1 : 0,
                csrf_token: adminDashboardConfig.csrfToken
            };

            fetch(basePath + 'includes/admin/service-save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    closeServiceModal();
                    loadMeta().then(function () { loadPlans(plansPagination.currentPage); });
                } else {
                    alert(data.message);
                }
            })
            .catch(function () { alert('Save failed.'); });
        });
    }

    function deleteService(id) {
        fetch(basePath + 'includes/admin/service-delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, csrf_token: adminDashboardConfig.csrfToken })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                loadMeta().then(function () { loadPlans(1); });
            } else {
                alert(data.message);
            }
        })
        .catch(function () { alert('Delete failed.'); });
    }

    // ─── Package modal ─────────────────────────────────────────────

    var planModal = document.getElementById('adm-plan-modal');
    var planBackdrop = document.getElementById('plan-modal-backdrop');
    var planCloseBtn = document.getElementById('plan-modal-close');
    var planCancelBtn = document.getElementById('plan-modal-close-btn');
    var addPlanBtn = document.getElementById('adm-add-plan-btn');

    function fillPlanServiceSelect() {
        var select = document.getElementById('plan-service');
        if (!select) return;

        var current = select.value;
        select.innerHTML = '<option value="">Select Service</option>' + services.map(function (s) {
            return '<option value="' + esc(s.id) + '">' + esc(s.name) + '</option>';
        }).join('');
        select.value = current || '';
    }

    function fillGroupDatalist(serviceId) {
        var list = document.getElementById('plan-group-label-list');
        if (!list) return;

        list.innerHTML = groups
            .filter(function (g) { return !serviceId || parseInt(g.service_id, 10) === serviceId; })
            .map(function (g) { return '<option value="' + esc(g.group_label || g.group_key) + '">'; })
            .join('');
    }

    var planServiceSelect = document.getElementById('plan-service');
    if (planServiceSelect) {
        planServiceSelect.addEventListener('change', function () {
            fillGroupDatalist(parseInt(this.value, 10) || 0);
        });
    }

    // An enquiry tier is quoted, so a price would be meaningless on it. The
    // field is greyed out rather than cleared, so ticking it back off restores
    // what was typed.
    var planEnquiry = document.getElementById('plan-enquiry');
    var planPrice = document.getElementById('plan-price');

    function syncEnquiryPrice() {
        if (!planEnquiry || !planPrice) return;
        planPrice.disabled = planEnquiry.checked;
        if (planEnquiry.checked) planPrice.value = '0';
    }

    if (planEnquiry) planEnquiry.addEventListener('change', syncEnquiryPrice);

    // Typing a name fills the group key while it is still empty, so the common
    // case never needs the key field at all.
    var planName = document.getElementById('plan-name');
    var planGroupKey = document.getElementById('plan-group-key');
    var planGroupKeyTouched = false;

    if (planGroupKey) {
        planGroupKey.addEventListener('input', function () { planGroupKeyTouched = true; });
    }

    if (planName) {
        planName.addEventListener('input', function () {
            if (planGroupKeyTouched || !planGroupKey) return;
            planGroupKey.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        });
    }

    function openPlanModal(id, planList) {
        var form = document.getElementById('plan-form');
        if (!form) return;

        form.reset();
        planGroupKeyTouched = false;
        document.getElementById('plan-modal-id').value = 0;
        document.getElementById('plan-modal-title').textContent = id > 0 ? 'Edit Package' : 'Add New Package';
        document.getElementById('plan-active').checked = true;
        document.getElementById('plan-default').checked = false;
        document.getElementById('plan-enquiry').checked = false;
        document.getElementById('plan-price').value = '0';
        document.getElementById('plan-sort-order').value = '0';
        document.getElementById('plan-features').value = '';

        fillPlanServiceSelect();
        fillGroupDatalist(0);

        if (id > 0) {
            var plan = (planList || plans).find(function (p) { return p.id === id; });
            if (plan) {
                document.getElementById('plan-modal-id').value = plan.id;
                document.getElementById('plan-service').value = plan.service_id;
                document.getElementById('plan-name').value = plan.name;
                document.getElementById('plan-group-key').value = plan.group_key || '';
                document.getElementById('plan-group-label').value = plan.group_label || '';
                document.getElementById('plan-price').value = plan.price;
                document.getElementById('plan-price-note').value = plan.price_note || '';
                document.getElementById('plan-description').value = plan.description || '';
                document.getElementById('plan-best-for').value = plan.best_for || '';
                document.getElementById('plan-features').value = (plan.features || []).join('\n');
                document.getElementById('plan-sort-order').value = plan.sort_order || 0;
                document.getElementById('plan-default').checked = plan.is_default == 1;
                document.getElementById('plan-active').checked = plan.is_active == 1;
                document.getElementById('plan-enquiry').checked = plan.is_enquiry == 1;
    document.getElementById('plan-orderable').checked = plan.is_orderable == 1;
                planGroupKeyTouched = true;
                fillGroupDatalist(plan.service_id);
            }
        }

        syncEnquiryPrice();

        planModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closePlanModal() {
        if (!planModal) return;
        planModal.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (addPlanBtn) addPlanBtn.addEventListener('click', function () { openPlanModal(0, plans); });
    if (planCloseBtn) planCloseBtn.addEventListener('click', closePlanModal);
    if (planCancelBtn) planCancelBtn.addEventListener('click', closePlanModal);
    if (planBackdrop) planBackdrop.addEventListener('click', closePlanModal);

    var planForm = document.getElementById('plan-form');
    if (planForm) {
        planForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var features = document.getElementById('plan-features').value
                .split(/\r?\n/)
                .map(function (line) { return line.trim(); })
                .filter(function (line) { return line.length > 0; });

            var payload = {
                id: parseInt(document.getElementById('plan-modal-id').value, 10) || 0,
                service_id: parseInt(document.getElementById('plan-service').value, 10) || 0,
                group_key: document.getElementById('plan-group-key').value.trim(),
                group_label: document.getElementById('plan-group-label').value.trim(),
                name: document.getElementById('plan-name').value.trim(),
                price: document.getElementById('plan-price').value,
                price_note: document.getElementById('plan-price-note').value.trim(),
                description: document.getElementById('plan-description').value.trim(),
                best_for: document.getElementById('plan-best-for').value.trim(),
                sort_order: parseInt(document.getElementById('plan-sort-order').value, 10) || 0,
                is_default: document.getElementById('plan-default').checked ? 1 : 0,
                is_active: document.getElementById('plan-active').checked ? 1 : 0,
                is_enquiry: document.getElementById('plan-enquiry').checked ? 1 : 0,
    is_orderable: document.getElementById('plan-orderable').checked ? 1 : 0,
                features: features,
                csrf_token: adminDashboardConfig.csrfToken
            };

            fetch(basePath + 'includes/admin/plan-save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    closePlanModal();
                    loadPlans(plansPagination.currentPage);
                    loadMeta();
                } else {
                    alert(data.message);
                }
            })
            .catch(function () { alert('Save failed.'); });
        });
    }

    function deletePlan(id) {
        fetch(basePath + 'includes/admin/plan-delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, csrf_token: adminDashboardConfig.csrfToken })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                loadPlans(plansPagination.currentPage);
                loadMeta();
            } else {
                alert(data.message);
            }
        })
        .catch(function () { alert('Delete failed.'); });
    }

    // Escape closes whichever modal is open, matching the rest of the panel.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (planModal && planModal.classList.contains('open')) closePlanModal();
        if (serviceModal && serviceModal.classList.contains('open')) closeServiceModal();
    });

    // Initial load
    if (document.getElementById('adm-plans-tbody')) {
        loadMeta().then(function () { loadPlans(1); });
    }
});
