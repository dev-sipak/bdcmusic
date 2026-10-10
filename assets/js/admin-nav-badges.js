/*=========================================================
ADMIN SIDEBAR BADGES
=========================================================

The Orders and Verifications items in the admin sidebar carry a count badge:
orders no admin has opened yet, and marketplace registrations still waiting to
be checked.

The count is rendered server-side, so it is already correct on the first paint.
This file only keeps it current afterwards, by asking
includes/admin/badge-counts.php on a slow timer and whenever a page says it has
changed something.

Pages announce a change by dispatching an event rather than reaching in here:

	document.dispatchEvent(new CustomEvent('admin:badge-refresh'));

The verification queue and the order modal both do that, so the badge drops the
moment the work behind it is done instead of at the next poll.
========================================================*/

(() => {
	const badges = Array.from(document.querySelectorAll("[data-admin-badge]"));

	if (!badges.length) return;

	// The endpoint is derived from the page's own basePath value, which every
	// admin page gets from admin-config.php. Fall back to reading it off the
	// Orders link so the script still works if the config is ever reordered.
	let basePath = (window.adminDashboardConfig && adminDashboardConfig.basePath) || "/";

	if (basePath === "/") {
		const ordersLink = document.querySelector(".adm-sidebar a[href*='bdc-admin/orders']");
		if (ordersLink) {
			basePath = ordersLink.getAttribute("href").split("bdc-admin/")[0];
		}
	}

	function apply(counts) {
		badges.forEach((badge) => {
			const key = badge.getAttribute("data-admin-badge");
			const value = Number(counts[key] || 0);

			badge.textContent = value;
			badge.title = value + " item" + (value === 1 ? "" : "s") + " waiting for review";
			// Hidden rather than showing a zero: an empty badge still reads as a
			// notification, and it keeps the label from being pushed sideways.
			badge.classList.toggle("d-none", value === 0);
			badge.setAttribute("aria-label", value + " waiting for review");
		});
	}

	function refresh() {
		fetch(basePath + "includes/admin/badge-counts.php", {
			headers: { "Accept": "application/json" },
			credentials: "same-origin"
		})
			.then((response) => response.json())
			.then((data) => {
				if (data && data.success && data.counts) apply(data.counts);
			})
			.catch(() => {
				// A failed poll leaves the last known count on screen. Showing an
				// error would be worse than a badge that is briefly out of date.
			});
	}

	// A minute is often enough to notice a new order without holding a request
	// open on every admin tab, and the pages that change a count also trigger an
	// immediate refresh.
	const POLL_MS = 60000;

	setInterval(refresh, POLL_MS);

	document.addEventListener("visibilitychange", () => {
		if (!document.hidden) refresh();
	});

	document.addEventListener("admin:badge-refresh", refresh);

	// Reconcile once on load: the sidebar and the current page were rendered in
	// separate queries, so an order that arrived between them would show the
	// wrong badge.
	refresh();
})();
