/*=========================================================
PROGRAM TABS (Online/Offline Classes)
=========================================================

"Choose Your Learning Program" is a tab bar over the course blocks: picking a
chip shows that one course's packages and hides the other three, the way a tab
bar is expected to behave. Before, the chips were only links that scrolled to a
block, so every course was on the page at once and the highlighted chip was
painted by CSS (:first-child) regardless of what the visitor had chosen.

Nothing here is required for the chips to work as links: each one is a plain
anchor to a real element id, and every block starts visible in the markup, so a
visitor without JavaScript sees the whole comparison as before. This file only
adds the switching, the roving tab focus, and hash-linked state.
=========================================================*/

(() => {
	const tablist = document.querySelector("[data-program-tabs]");

	if (!tablist) return;

	// The anchor is the single source of truth for which block a chip opens,
	// which is what keeps a chip and its section from drifting apart. The
	// server renders aria-controls with the same id.
	const tabs = Array.from(tablist.querySelectorAll("a[href^='#']")).filter(
		(tab) => {
			const id = tab.getAttribute("href").slice(1);

			return id !== "" && document.getElementById(id);
		}
	);

	if (!tabs.length) return;

	const panels = tabs.map((tab) => document.getElementById(tab.getAttribute("href").slice(1)));

	/**
	 * Shows one course's packages and hides the rest.
	 *
	 * @param {number} index Tab to show.
	 * @param {boolean} moveFocus Whether focus should follow the switch. Arrow
	 *                            keys need it; a click already has focus.
	 */
	function showTab(index, moveFocus) {
		tabs.forEach((tab, position) => {
			const isActive = position === index;

			tab.classList.toggle("is-active", isActive);
			tab.setAttribute("aria-selected", isActive ? "true" : "false");

			// Roving tab focus: the tab bar is one stop in the tab order, and the
			// arrows move within it. Without JavaScript no tabindex is written at
			// all, so every chip stays reachable.
			tab.setAttribute("tabindex", isActive ? "0" : "-1");

			panels[position].hidden = !isActive;
		});

		if (moveFocus) {
			tabs[index].focus();
		}
	}

	/**
	 * The tab a deep link or the back button names, or -1 when the hash is not
	 * about a program at all.
	 *
	 * @param {string} id Element id from the hash.
	 * @return {number} Index into tabs, or -1.
	 */
	function tabIndexFor(id) {
		return tabs.findIndex((tab) => tab.getAttribute("href") === "#" + id);
	}

	tabs.forEach((tab, index) => {
		tab.addEventListener("click", (event) => {
			event.preventDefault();

			showTab(index, false);

			// The chosen program is a real state on the page, so it is linkable
			// and survives the back button.
			if (window.history && window.history.pushState) {
				window.history.pushState(null, "", tab.getAttribute("href"));
			}
		});

		tab.addEventListener("keydown", (event) => {
			const last = tabs.length - 1;
			let target = null;

			switch (event.key) {
				case "ArrowRight":
				case "ArrowDown":
					target = index === last ? 0 : index + 1;
					break;
				case "ArrowLeft":
				case "ArrowUp":
					target = index === 0 ? last : index - 1;
					break;
				case "Home":
					target = 0;
					break;
				case "End":
					target = last;
					break;
				default:
					return;
			}

			event.preventDefault();
			showTab(target, true);

			window.history.replaceState(null, "", tabs[target].getAttribute("href"));
		});
	});

	/**
	 * Keeps the tab bar in step with the address bar.
	 *
	 * A hash that names no program is left alone: the "Choose Your Package" links
	 * jump down to the package section, and following one of those must not throw
	 * away the program the visitor had chosen. An empty hash means the start of
	 * the page, which is the first program.
	 */
	function syncFromHash() {
		const id = window.location.hash.slice(1);
		const index = tabIndexFor(id);

		if (index === -1 && id !== "") return;

		showTab(index === -1 ? 0 : index, false);
	}

	// A deep link, or the back button after switching programs, is reflected
	// immediately rather than waiting for a click.
	window.addEventListener("hashchange", syncFromHash);
	window.addEventListener("popstate", syncFromHash);

	syncFromHash();
})();