/*=========================================================
  SHARED UI UTILITIES
  Reusable escaping, sidebar toggle, status badge,
  table row and accordion behavior.
  =========================================================*/


/**
 * Escape a value for interpolation into HTML.
 * Every value that reaches innerHTML must pass through this, otherwise a
 * stored value such as an enquiry name becomes stored XSS in the panel.
 * @param {*} value
 * @returns {string}
 */
function esc(value) {
	if (value === null || value === undefined) return '';
	return String(value)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;');
}

/**
 * Restrict a value to characters that are safe inside a class attribute.
 * @param {*} value
 * @param {string} fallback
 * @returns {string}
 */
function cssToken(value, fallback) {
	var token = String(value === null || value === undefined ? '' : value)
		.toLowerCase()
		.replace(/[^a-z0-9_-]+/g, '-')
		.replace(/^-+|-+$/g, '');
	return token === '' ? fallback : token;
}


/**
 * Lucide icon rendering.
 *
 * Markup carries `<i data-lucide="name"></i>` placeholders, and Lucide swaps
 * each one for an inline SVG. That only happens for the placeholders that
 * already exist when createIcons() runs, and most of this app builds its
 * tables, rows and modals from HTML strings after load, so a single call would
 * leave every later icon blank. The observer below renders whatever a script
 * injects, which is why no dashboard file has to remember to call anything.
 *
 * Rendered SVGs are skipped by tag name. Lucide keeps the data-lucide attribute
 * on the SVG it creates, so re-rendering one would only replace it with an
 * identical copy, mutate the DOM again and spin.
 */
window.bdcIcons = (function () {
	function render(root) {
		if (!window.lucide || typeof window.lucide.createIcons !== 'function') return;
		if (!root || (root.nodeType !== 1 && root.nodeType !== 9)) return;
		if (root.tagName === 'svg') return;
		if (!root.querySelector('[data-lucide]')) return;
		window.lucide.createIcons({ root: root });
	}

	function start() {
		if (!document.body) return;
		new MutationObserver(function (mutations) {
			for (var i = 0; i < mutations.length; i++) {
				var added = mutations[i].addedNodes;
				for (var n = 0; n < added.length; n++) render(added[n]);
			}
		}).observe(document.body, { childList: true, subtree: true });
		render(document);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}

	return { render: render, renderAll: function () { render(document); } };
})();


/**
 * Initialize the admin sidebar.
 *
 * On a desktop the sidebar is a fixed left rail and there is nothing to toggle.
 * Under 992px it becomes an off-canvas drawer, and this wires up every way in and
 * out of it: the burger (which doubles as the X), the X inside the drawer, the
 * shaded backdrop, the Escape key, and a body scroll lock so the page cannot
 * slide around under the drawer's thumb.
 *
 * Section switching is real page navigation, so nothing here intercepts a link
 * click; the drawer is simply closed again on the next page load.
 *
 * @param {string} sidebarSelector - sidebar selector (e.g. '.adm-sidebar')
 * @param {string} menuBtnSelector - burger selector (e.g. '#admin-menu-toggle')
 */
function initSidebarToggle(sidebarSelector, menuBtnSelector) {
	var sidebar  = document.querySelector(sidebarSelector);
	var menuBtn  = document.querySelector(menuBtnSelector);
	var backdrop = document.getElementById('adm-sidebar-backdrop');
	var closeBtn = document.getElementById('adm-sidebar-close');

	if (!sidebar || !menuBtn) return;

	// The breakpoint has to match the one in pages/_admin.scss. Above it the
	// sidebar is a static rail, so none of this should run at all.
	var mq = window.matchMedia('(max-width: 992px)');

	function setOpen(open) {
		sidebar.classList.toggle('is-open', open);
		menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		menuBtn.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
		if (backdrop) backdrop.classList.toggle('is-open', open);
		document.body.classList.toggle('adm-drawer-open', open);
	}

	function isOpen() {
		return sidebar.classList.contains('is-open');
	}

	menuBtn.addEventListener('click', function () {
		setOpen(!isOpen());
	});

	if (closeBtn) {
		closeBtn.addEventListener('click', function () {
			setOpen(false);
			menuBtn.focus();
		});
	}

	if (backdrop) {
		backdrop.addEventListener('click', function () {
			setOpen(false);
			menuBtn.focus();
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && isOpen()) {
			setOpen(false);
			menuBtn.focus();
		}
	});

	// Rotating or resizing past the breakpoint has to leave the page clean: a
	// drawer left open would come back as an open, full-height rail, and a locked
	// body scroll would survive on a desktop.
	function onBreakpointChange() {
		if (mq.matches) return;
		setOpen(false);
	}

	if (typeof mq.addEventListener === 'function') {
		mq.addEventListener('change', onBreakpointChange);
	} else if (typeof mq.addListener === 'function') {
		mq.addListener(onBreakpointChange);
	}

	// Starts closed no matter how the page was rendered.
	setOpen(false);
}

/**
 * Render a status badge HTML string. The status is escaped for display and
 * reduced to a safe CSS token, because it is used both as text and as part of
 * a class name.
 * @param {string} status
 * @returns {string}
 */
function statusBadgeHtml(status) {
	var text = String(status === null || status === undefined ? '' : status);
	return '<span class="panel-status-badge status-' + cssToken(text, 'pending') + '">' + esc(text) + '</span>';
}

/**
 * Render a payment-status badge. Kept separate from statusBadgeHtml because
 * bookings.payment_status is a different enum to bookings.status and has its
 * own colour set (the pay-* classes). The status is whitelisted because it
 * becomes part of a class name.
 * @param {string} value
 * @param {string} [label] - human label; falls back to the raw status
 * @returns {string}
 */
function paymentBadgeHtml(value, label) {
	var known = { awaiting: 1, paid: 1, refunded: 1, failed: 1, cancelled: 1, created: 1 };
	var status = String(value == null ? '' : value).toLowerCase();
	var key    = known[status] ? status : 'awaiting';
	var text   = String(label == null || label === '' ? status : label);

	return '<span class="panel-status-badge pay-' + key + '">' + esc(text) + '</span>';
}

/**
 * Format a rupee amount for display. Whole amounts are shown without
 * decimals and with Indian digit grouping; part amounts keep two decimals.
 * @param {number} value
 * @returns {string}
 */
function money(value) {
	var amount = Number(value || 0);
	if (!isFinite(amount)) amount = 0;

	return '₹' + (amount % 1 === 0
		? amount.toLocaleString('en-IN')
		: amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}

/**
 * Render pagination HTML for JS-driven pages.
 * @param {object} pagination - { currentPage, totalPages, totalRecords, perPage, hasPrev, hasNext }
 * @param {function} onPageClick - callback(pageNumber) when a page link is clicked
 * @returns {string} HTML string
 */
function renderPaginationHtml(pagination, onPageClick) {
	if (!pagination || pagination.totalPages <= 1) return '';

	var currentPage = pagination.currentPage;
	var totalPages = pagination.totalPages;
	var hasPrev = pagination.hasPrev;
	var hasNext = pagination.hasNext;
	var start = (currentPage - 1) * pagination.perPage + 1;
	var end = Math.min(currentPage * pagination.perPage, pagination.totalRecords);

	var maxVisible = 7;
	var startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
	var endPage = Math.min(totalPages, startPage + maxVisible - 1);
	if (endPage - startPage < maxVisible - 1) {
		startPage = Math.max(1, endPage - maxVisible + 1);
	}

	var html = '<div class="pagination-wrapper">';
	html += '<p class="pagination-info">Showing ' + start + '\u2013' + end + ' of ' + pagination.totalRecords + '</p>';
	html += '<nav class="pagination" aria-label="Page navigation">';

	// Previous
	if (hasPrev) {
		html += '<a class="pagination__link pagination__prev" href="#" data-page="' + (currentPage - 1) + '">&laquo; Previous</a>';
	} else {
		html += '<span class="pagination__link pagination__prev pagination__link--disabled" aria-disabled="true">&laquo; Previous</span>';
	}

	// First + ellipsis
	if (startPage > 1) {
		html += '<a class="pagination__link" href="#" data-page="1">1</a>';
		if (startPage > 2) {
			html += '<span class="pagination__ellipsis">&hellip;</span>';
		}
	}

	// Page numbers
	for (var i = startPage; i <= endPage; i++) {
		if (i === currentPage) {
			html += '<span class="pagination__link pagination__link--active" aria-current="page">' + i + '</span>';
		} else {
			html += '<a class="pagination__link" href="#" data-page="' + i + '">' + i + '</a>';
		}
	}

	// Last + ellipsis
	if (endPage < totalPages) {
		if (endPage < totalPages - 1) {
			html += '<span class="pagination__ellipsis">&hellip;</span>';
		}
		html += '<a class="pagination__link" href="#" data-page="' + totalPages + '">' + totalPages + '</a>';
	}

	// Next
	if (hasNext) {
		html += '<a class="pagination__link pagination__next" href="#" data-page="' + (currentPage + 1) + '">Next &raquo;</a>';
	} else {
		html += '<span class="pagination__link pagination__next pagination__link--disabled" aria-disabled="true">Next &raquo;</span>';
	}

	html += '</nav></div>';

	// Attach click handlers after rendering
	setTimeout(function () {
		var container = document.querySelector('.pagination');
		if (!container) return;
		container.querySelectorAll('a[data-page]').forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				var page = parseInt(this.getAttribute('data-page'));
				if (page && typeof onPageClick === 'function') {
					onPageClick(page);
				}
			});
		});
	}, 0);

	return html;
}

/**
 * Generic accordion behavior for FAQ items.
 */
function initFaqAccordion() {
	document.querySelectorAll('.faq-item').forEach(function (item) {
		item.addEventListener('toggle', function () {
			if (!item.open) {
				item.classList.remove('active');
				return;
			}
			document.querySelectorAll('.faq-item').forEach(function (faq) {
				if (faq !== item) {
					faq.open = false;
					faq.classList.remove('active');
				}
			});
			item.classList.add('active');
		});
	});
}
