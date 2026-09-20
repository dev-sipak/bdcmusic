/*=========================================================
 SHARED UI UTILITIES
 Reusable tab navigation, sidebar toggle, status badge,
 and accordion behavior.
=========================================================*/

/**
 * Initialize tab navigation for a panel.
 * @param {string} navSelector - nav button selector (e.g. '.panel-nav-btn[data-tab]')
 * @param {string} tabSelector - tab panel selector (e.g. '.panel-tab')
 * @param {string} tabPrefix   - tab ID prefix (e.g. 'tab-' or 'adm-tab-')
 * @param {string} sidebarSelector - sidebar selector (e.g. '.panel-sidebar')
 * @param {string} menuBtnSelector - mobile menu button selector
 */
function initTabNav(navSelector, tabSelector, tabPrefix, sidebarSelector, menuBtnSelector) {
    var navBtns = document.querySelectorAll(navSelector);
    var tabPanels = document.querySelectorAll(tabSelector);
    var sidebar = document.querySelector(sidebarSelector);
    var menuBtn = document.querySelector(menuBtnSelector);

    if (!navBtns.length) return;

    navBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tab = this.getAttribute('data-tab');
            navBtns.forEach(function (b) { b.classList.remove('active'); });
            tabPanels.forEach(function (p) { p.classList.remove('active'); });
            this.classList.add('active');
            var target = document.getElementById(tabPrefix + tab);
            if (target) target.classList.add('active');
            if (sidebar) sidebar.classList.remove('open');
        });
    });

    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }
}

/**
 * Render a status badge HTML string.
 * @param {string} status
 * @returns {string}
 */
function statusBadgeHtml(status) {
    return '<span class="panel-status-badge status-' + status.toLowerCase() + '">' + status + '</span>';
}

/**
 * Render a table row for an order.
 * @param {object} o - order object
 * @param {boolean} showCustomer - include customer column
 * @returns {string}
 */
function orderRowHtml(o, showCustomer) {
    var html = '<tr>' +
        '<td><strong>' + o.id + '</strong></td>';
    if (showCustomer) {
        html += '<td>' + o.customer + '</td>';
    }
    html += '<td>' + o.service + '</td>' +
        '<td>' + o.date + '</td>' +
        '<td>\u20B9' + o.amount + '</td>' +
        '<td>' + statusBadgeHtml(o.status) + '</td>' +
        '</tr>';
    return html;
}

/**
 * Render a table row for order history (no action column).
 * @param {object} o
 * @returns {string}
 */
function historyRowHtml(o) {
    return '<tr>' +
        '<td><strong>' + o.id + '</strong></td>' +
        '<td>' + o.service + '</td>' +
        '<td>' + o.date + '</td>' +
        '<td>\u20B9' + o.amount + '</td>' +
        '<td>' + statusBadgeHtml(o.status) + '</td>' +
        '</tr>';
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
