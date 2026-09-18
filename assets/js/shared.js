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
        '<td>' + o.item + '</td>' +
        '<td>' + o.date + '</td>' +
        '<td>' + o.amount + '</td>' +
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
        '<td>' + o.item + '</td>' +
        '<td>' + o.date + '</td>' +
        '<td>' + o.amount + '</td>' +
        '<td>' + statusBadgeHtml(o.status) + '</td>' +
        '</tr>';
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
