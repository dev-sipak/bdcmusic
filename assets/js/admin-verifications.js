/*=========================================================
ARTIST VERIFICATION QUEUE
=========================================================

The studio's review list for BDC Marketplace registrations. Each row is one
package purchase waiting to be checked; opening it shows what the artist
submitted, and the three decisions:

  Verify  Assigns an artist ID and issues the ID Card and Certificate, which
		  immediately appear in the artist's own dashboard.
  Reject  Sends the recorded reason to the artist, so a reason is required.
  Reopen  Puts a rejected registration back in the queue.

After any decision the sidebar badge is told to recount, so the Verifications
number drops as soon as the work behind it is done.
========================================================*/

document.addEventListener('DOMContentLoaded', function () {
	var basePath = adminDashboardConfig.basePath;

	var registrationPagination = { currentPage: 1, totalPages: 1 };
	var registrationFilters = { status: 'pending', search: '' };
	var currentRegistration = null;

	initSidebarToggle('.adm-sidebar', '#admin-menu-toggle');

	function loadRegistrations(page) {
		page = page || 1;

		var params = 'page=' + page + '&per_page=10&status=' + encodeURIComponent(registrationFilters.status);
		if (registrationFilters.search) params += '&search=' + encodeURIComponent(registrationFilters.search);

		return fetch(basePath + 'includes/admin/artist-registrations-list.php?' + params + '&t=' + Date.now())
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (data && data.success) {
					registrationPagination = data.pagination;
					updateFilterCounts(data.counts);
					renderRegistrations(data.registrations);
				}
			});
	}

	/**
	 * Shows how many registrations sit behind each status, so the filter itself
	 * says whether the queue is empty rather than only the table saying so.
	 */
	function updateFilterCounts(counts) {
		if (!counts) return;

		var select = document.getElementById('adm-registration-status');
		if (!select) return;

		var labels = {
			pending: 'Pending Verification',
			verified: 'Verified',
			rejected: 'Rejected',
			all: 'All Statuses'
		};

		Array.prototype.forEach.call(select.options, function (option) {
			var key = option.value;
			option.textContent = labels[key] + ' (' + (counts[key] || 0) + ')';
		});
	}

	function registrationBadge(status, label) {
		var cls = 'status-pending';

		if (status === 'verified') cls = 'status-delivered';
		else if (status === 'rejected') cls = 'status-cancelled';

		return '<span class="panel-status-badge ' + cls + '">' + esc(label || status) + '</span>';
	}

	function renderRegistrations(list) {
		var tbody = document.getElementById('adm-verifications-tbody');
		var emptyMsg = document.getElementById('adm-verifications-empty');
		var pagWrap = document.getElementById('adm-verifications-pagination');

		if (!tbody) return;

		if (!list || list.length === 0) {
			tbody.innerHTML = '';
			if (emptyMsg) emptyMsg.classList.remove('d-none');
			if (pagWrap) pagWrap.innerHTML = '';
			return;
		}

		if (emptyMsg) emptyMsg.classList.add('d-none');

		tbody.innerHTML = list.map(function (r) {
			var contact = [r.artist_email, r.artist_phone].filter(Boolean).join(' · ');

			return '<tr>' +
				'<td><strong>' + esc(r.artist_name) + '</strong>' +
					(contact ? cellSub(contact) : '') + '</td>' +
				'<td>' + esc(r.category_label || '—') + '</td>' +
				'<td>' + esc(r.plan_name || '—') + (r.plan_price !== null ? cellSub(money(r.plan_price)) : '') + '</td>' +
				'<td><strong>' + esc(r.booking_id) + '</strong></td>' +
				'<td>' + esc(r.created_at) + '</td>' +
				'<td>' + (r.artist_code ? esc(r.artist_code) : '—') + '</td>' +
				'<td>' + registrationBadge(r.status, r.status_label) + '</td>' +
				'<td><button class="adm-view-btn adm-review-registration" data-id="' + esc(r.id) + '" aria-label="Review registration ' + esc(r.id) + '"><i data-lucide="eye"></i></button></td>' +
			'</tr>';
		}).join('');

		tbody.querySelectorAll('.adm-review-registration').forEach(function (btn) {
			btn.addEventListener('click', function () {
				openVerificationModal(this.getAttribute('data-id'));
			});
		});

		if (pagWrap) {
			pagWrap.innerHTML = renderPaginationHtml(registrationPagination, function (page) {
				loadRegistrations(page);
			});
		}

		if (typeof lucide !== 'undefined') lucide.createIcons();
	}

	/* ----- Review panel ----- */

	var modal = document.getElementById('adm-verification-modal');
	var currentId = null;

	function detailItem(label, value) {
		if (value === null || value === undefined || value === '') return '';

		return '<div class="modal-detail-item">' +
			'<span class="panel-label">' + esc(label) + '</span>' +
			'<span class="panel-value">' + esc(value) + '</span>' +
		'</div>';
	}

	/**
	 * A table cell with a quieter second line, for the artist name over their
	 * contact details.
	 */
	function cellSub(text) {
		return '<div style="font-size:0.78rem;color:var(--muted);">' + esc(text) + '</div>';
	}

	function renderDetail(reg) {
		document.getElementById('verification-summary-grid').innerHTML = [
			'<div class="modal-detail-item"><span class="panel-label">Status</span>' +
				'<span class="panel-value">' + registrationBadge(reg.status, reg.statusLabel) + '</span></div>',
			detailItem('Artist', reg.artistName),
			detailItem('Artist ID', reg.artistCode || 'Not issued yet'),
			detailItem('Email', reg.artistEmail),
			detailItem('Phone', reg.artistPhone),
			detailItem('Package', reg.planName),
			detailItem('Amount paid', money(reg.planPrice)),
			detailItem('Order', reg.bookingId),
			detailItem('Invoice', reg.invoiceNo),
			detailItem('Order status', reg.orderStatus),
			detailItem('Payment', reg.paymentStatus),
			detailItem('Submitted', reg.createdAt),
			// Only present once someone has decided, which is what tells a returning
			// admin that this row was already looked at and by whom.
			detailItem('Reviewed by', reg.reviewerName),
			detailItem('Reviewed at', reg.reviewedAt)
		].join('');

		var fieldsGrid = document.getElementById('verification-fields-grid');

		fieldsGrid.innerHTML = (reg.detailFields || []).map(function (f) {
			return detailItem(f.label, f.value);
		}).join('') || '<span class="panel-hint">No details were recorded with this registration.</span>';

		// Uploads, opened through the same authenticated download endpoint the
		// order modal uses.
		var filesSection = document.getElementById('verification-files-section');
		var filesList = document.getElementById('verification-files-list');

		if (reg.files && reg.files.length) {
			filesList.innerHTML = reg.files.map(function (f) {
				var sizeKB = (f.file_size / 1024).toFixed(1);
				return '<div class="modal-file-item">' +
					'<i data-lucide="file" class="modal-file-icon"></i>' +
					'<span class="modal-file-name">' + esc(f.original_name) + '</span>' +
					'<span class="modal-file-size">' + esc(sizeKB) + ' KB</span>' +
					'<a href="' + basePath + 'includes/download-file.php?file=' + encodeURIComponent(f.file_path) + '" class="modal-file-download" target="_blank" rel="noopener"><i data-lucide="download"></i></a>' +
				'</div>';
			}).join('');
			filesSection.classList.remove('d-none');
		} else {
			filesList.innerHTML = '';
			filesSection.classList.add('d-none');
		}

		// The documents an admin can open for themselves after issuing them.
		var documentsSection = document.getElementById('verification-documents-section');
		var documentsList = document.getElementById('verification-documents-list');

		if (reg.status === 'verified' && reg.artistCode) {
			documentsList.innerHTML = [
				documentLink(reg, 'id-card', 'Artist ID Card'),
				documentLink(reg, 'certificate', 'Certificate of Registration')
			].join('');
			documentsSection.classList.remove('d-none');
		} else {
			documentsList.innerHTML = '<span class="panel-hint">Documents are issued when this registration is verified.</span>';
			documentsSection.classList.remove('d-none');
		}

		// The note doubles as the rejection reason, so it is required only while
		// rejecting.
		document.getElementById('verification-note').value = reg.reviewNote || '';

		var verified = reg.status === 'verified';

		document.getElementById('verification-verify-btn').classList.toggle('d-none', verified);
		document.getElementById('verification-reject-btn').classList.toggle('d-none', verified);
		document.getElementById('verification-reopen-btn').classList.toggle('d-none', reg.status !== 'rejected');

		// The same note doubles as the rejection reason, so it is marked required
		// exactly while rejecting is on offer.
		document.getElementById('verification-note-required').classList.toggle('d-none', verified);

		if (typeof lucide !== 'undefined') lucide.createIcons();
	}

	function documentLink(reg, kind, label) {
		var url = basePath + 'includes/artist-document.php?registration=' +
			encodeURIComponent(reg.id) + '&doc=' + encodeURIComponent(kind) + '&mode=view';

		return '<div class="modal-file-item">' +
			'<i data-lucide="file-badge" class="modal-file-icon"></i>' +
			'<span class="modal-file-name">' + esc(label) + '</span>' +
			'<a href="' + url + '" class="modal-file-download" target="_blank" rel="noopener" aria-label="Open ' + esc(label) + '"><i data-lucide="external-link"></i></a>' +
		'</div>';
	}

	function openVerificationModal(id) {
		currentId = id;

		document.getElementById('verification-modal-title').textContent = 'Registration Review';
		document.getElementById('verification-summary-grid').innerHTML = '';
		document.getElementById('verification-fields-grid').innerHTML = '';

		modal.classList.add('open');
		document.body.style.overflow = 'hidden';

		fetch(basePath + 'includes/admin/artist-registration-detail.php?id=' + encodeURIComponent(id) + '&t=' + Date.now())
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (!data || !data.success) {
					alert(data && data.message ? data.message : 'Could not load this registration.');
					closeVerificationModal();
					return;
				}

				currentRegistration = data.registration;
				document.getElementById('verification-modal-title').textContent =
					'Registration Review — ' + data.registration.artistName;

				renderDetail(data.registration);
			})
			.catch(function () {
				alert('Could not load this registration.');
				closeVerificationModal();
			});
	}

	function closeVerificationModal() {
		if (!modal) return;

		modal.classList.remove('open');
		document.body.style.overflow = '';
		currentRegistration = null;
	}

	var closeBtn = document.getElementById('verification-modal-close');
	var backdrop = document.getElementById('verification-modal-backdrop');

	if (closeBtn) closeBtn.addEventListener('click', closeVerificationModal);
	if (backdrop) backdrop.addEventListener('click', closeVerificationModal);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && modal && modal.classList.contains('open')) {
			closeVerificationModal();
		}
	});

	function submitDecision(action, confirmMessage, requireNote) {
		if (!currentRegistration) return;

		var note = document.getElementById('verification-note').value.trim();

		if (requireNote && !note) {
			alert('Please record a reason. It is sent to the artist with the decision.');
			document.getElementById('verification-note').focus();
			return;
		}

		if (confirmMessage && !confirm(confirmMessage)) return;

		submitRegistration({ id: currentRegistration.id, action: action, note: note });
	}

	function submitRegistration(payload) {
		payload.csrf_token = adminDashboardConfig.csrfToken;

		fetch(basePath + 'includes/admin/artist-registration-update.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(payload)
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (data && data.success) {
					alert(data.message || 'Saved.');
					closeVerificationModal();
					loadRegistrations(registrationPagination.currentPage);
					// The Verifications badge counts what is still pending.
					document.dispatchEvent(new CustomEvent('admin:badge-refresh'));
					return;
				}

				alert(data && data.message ? data.message : 'The registration could not be updated.');
			})
			.catch(function () {
				alert('The registration could not be updated. Please try again.');
			});
	}

	var verifyBtn = document.getElementById('verification-verify-btn');
	var rejectBtn = document.getElementById('verification-reject-btn');
	var reopenBtn = document.getElementById('verification-reopen-btn');

	if (verifyBtn) {
		verifyBtn.addEventListener('click', function () {
			submitDecision(
				'verify',
				'Verify this registration and issue the artist ID Card and Certificate?',
				false
			);
		});
	}

	if (rejectBtn) {
		rejectBtn.addEventListener('click', function () {
			submitDecision(
				'reject',
				'Reject this registration? The artist will be emailed your reason.',
				true
			);
		});
	}

	if (reopenBtn) {
		reopenBtn.addEventListener('click', function () {
			submitDecision(
				'reopen',
				'Move this registration back to the pending queue? Any issued documents will be withdrawn.',
				false
			);
		});
	}

	/* ----- Filters ----- */

	var statusFilter = document.getElementById('adm-registration-status');
	var searchInput = document.getElementById('adm-registration-search');

	if (statusFilter) {
		statusFilter.addEventListener('change', function () {
			registrationFilters.status = this.value;
			loadRegistrations(1);
		});
	}

	if (searchInput) {
		var searchTimer;

		searchInput.addEventListener('input', function () {
			var value = this.value;

			clearTimeout(searchTimer);
			searchTimer = setTimeout(function () {
				registrationFilters.search = value;
				loadRegistrations(1);
			}, 400);
		});
	}

	loadRegistrations(1);
});
