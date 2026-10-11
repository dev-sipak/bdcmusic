document.addEventListener('DOMContentLoaded', function () {

  var overview = adminDashboardConfig.overview || { stats: {} };
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

	/* ----- Overview Stats -----
	   Four figures, each aggregated in SQL on bdc-admin/index.php: what the
	   studio has taken, what it has given back, how large the artist roster is
	   and how large the release catalogue is. The recent-orders table that used
	   to sit below these cards is gone; the Orders page is where an order is
	   read and acted on. */
	function updateOverviewStats() {
		var stats = overview.stats || {};

		var salesEl = document.getElementById('stat-sales');
		var refundedEl = document.getElementById('stat-refunded');
		var artistsEl = document.getElementById('stat-artists');
		var releasesEl = document.getElementById('stat-releases');

		if (salesEl) salesEl.textContent = money(stats.sales || 0);
		if (refundedEl) refundedEl.textContent = money(stats.refunded || 0);
		if (artistsEl) artistsEl.textContent = stats.artists || 0;
		if (releasesEl) releasesEl.textContent = stats.releases || 0;
	}

	updateOverviewStats();

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
			var amounts = o.amounts || {};

			// A row nobody has opened yet is called out with a dot next to the
			// order id, which is the same set of orders the sidebar badge counts.
			return '<tr' + (o.isUnread ? ' class="is-unread"' : '') + '>' +
				'<td><strong>' + esc(o.id) + '</strong>' + (o.isUnread ? '<span class="adm-unread-dot" title="Not viewed yet"></span>' : '') + '</td>' +
				'<td>' + esc(o.customer) + '</td>' +
				'<td>' + esc(o.service) + '</td>' +
				'<td>' + esc(o.date) + '</td>' +
				'<td>' + money(amounts.total !== undefined ? amounts.total : o.amount) + '</td>' +
				'<td>' + statusBadgeHtml(o.status) + '</td>' +
				'<td>' + paymentBadgeHtml(o.paymentStatus, o.paymentLabel) + '</td>' +
				'<td><button class="adm-view-btn" data-order-id="' + esc(o.id) + '" aria-label="View order ' + esc(o.id) + '"><i data-lucide="eye"></i></button></td>' +
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

	// The two arrangement dates are Flatpickr fields, kept in sync with the
	// vendor library. The Orders page loads the vendor script and its
	// stylesheet; the other admin pages that load this file do not, so the
	// initialisation bails out and the fields stay plain text boxes.

	const datepickers = {};

	function initArrangementDatepickers() {
		if (typeof window.flatpickr === 'undefined' || Object.keys(datepickers).length) return;

		const ids = ['modal-starts-on', 'modal-ends-on'];
		for (let i = 0; i < ids.length; i++) {
			const el = document.getElementById(ids[i]);
			if (el) {
				datepickers[ids[i]] = window.flatpickr(el, {
					dateFormat: 'Y-m-d',
					disableMobile: true,
					allowInput: false
				});
			}
		}
	}

	initArrangementDatepickers();

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
			if (!field) return;
			// Writing straight into a Flatpickr input desyncs its internal date,
			// so the dates go through the API while every other control takes a value.
			var fp = datepickers[field.id];
			if (fp) {
				fp.setDate(record[column] || null, true);
			} else {
				field.value = record[column] || '';
			}
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
	 * The package name an order is shown under.
	 *
	 * booking_items holds one line per package while the bookings row only
	 * snapshots the first, so a multi-package order is named from its lines.
	 * @param {object} order
	 * @returns {string}
	 */
	function orderPackageName(order) {
		var plan  = order.plan || {};
		var items = order.items || [];

		if (items.length > 1) return items.map(function (it) { return it.name; }).join(', ');

		return plan.name || (items.length === 1 ? items[0].name : '—');
	}

	/**
	 * Render the package and money breakdown.
	 * @param {object} order
	 */
	function renderOrderPackage(order) {
		var plan    = order.plan || {};
		var items   = order.items || [];
		var amounts = order.amounts || {};

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

		pairs.push(detailItem('Package Price', money(amounts.subtotal)));
		pairs.push(detailItem('Total Charged', money(amounts.total)));

		var planEl = document.getElementById('modal-plan-fields');
		if (planEl) planEl.innerHTML = pairs.join('');
	}

	/**
	 * Render the payment summary for one order.
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
	}

	function openOrderModal(orderId) {
		currentOrderId = orderId;
		// This endpoint records the viewing, so the sidebar badge has one less
		// order to show as soon as the details arrive. Announced so
		// admin-nav-badges.js can recount rather than guess.
		document.dispatchEvent(new CustomEvent('admin:badge-refresh'));
		document.getElementById('modal-customer').textContent = '';
		document.getElementById('modal-status-select').value = 'pending';
		// Hide the async sections up front so a previous order's package and
		// payment details cannot linger while the next one loads.
		['modal-plan-fields', 'modal-payment-fields'].forEach(function (id) {
			var el = document.getElementById(id);
			if (el) el.innerHTML = '';
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
				document.getElementById('modal-package').textContent   = orderPackageName(order);
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
						var icon = 'file';
						if (f.mime_type && f.mime_type.indexOf('audio') !== -1) icon = 'file-audio';
						else if (f.mime_type && f.mime_type.indexOf('video') !== -1) icon = 'file-video';
						else if (f.mime_type && f.mime_type.indexOf('image') !== -1) icon = 'file-image';
						else if (f.mime_type && f.mime_type.indexOf('pdf') !== -1) icon = 'file-text';
						return '<div class="modal-file-item">' +
							'<i data-lucide="' + icon + ' modal-file-icon"></i>' +
							'<span class="modal-file-name">' + esc(f.original_name) + '</span>' +
							'<span class="modal-file-size">' + sizeStr + '</span>' +
							'<a href="' + basePath + 'includes/download-file.php?file=' + encodeURIComponent(f.file_path) + '" class="modal-file-download" target="_blank"><i data-lucide="download"></i></a>' +
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
