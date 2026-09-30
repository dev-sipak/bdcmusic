document.addEventListener('DOMContentLoaded', function () {

	if (typeof customerReleasesConfig === 'undefined') return;

	var config = customerReleasesConfig;
	var basePath = config.basePath;
	var releasesPagination = { currentPage: 1, totalPages: 1 };
	// The status filter is resolved server-side from ?status= and rendered
	// as links, so it is only read here to build the API query.
	var activeTab = config.activeTab || 'all';
	var currentReleases = [];

	function esc(value) {
		if (value === null || value === undefined) return '';
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	function isExternalUrl(url) {
		return /^https?:\/\//i.test(String(url || ''));
	}

	function loadReleases(page) {
		page = page || 1;
		var params = 'page=' + page + '&per_page=10';
		if (activeTab !== 'all') params += '&status=' + encodeURIComponent(activeTab);

		fetch(basePath + 'includes/customer/releases-list.php?' + params)
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (data.success) {
					releasesPagination = data.pagination;
					currentReleases = data.releases || [];
					renderReleases();
				}
			});
	}

	function renderReleases() {
		var tbody = document.getElementById('releases-tbody');
		var emptyMsg = document.getElementById('releases-empty');
		var pagWrap = document.getElementById('releases-pagination');
		if (!tbody) return;

		if (!currentReleases || currentReleases.length === 0) {
			tbody.innerHTML = '';
			if (emptyMsg) emptyMsg.classList.remove('d-none');
			if (pagWrap) pagWrap.innerHTML = '';
			return;
		}
		if (emptyMsg) emptyMsg.classList.add('d-none');

		tbody.innerHTML = currentReleases.map(function (r) {
			var trackCount = (r.tracks || []).length;
			return '<tr>' +
				'<td><strong>' + esc(r.title) + '</strong></td>' +
				'<td>' + esc(r.type) + '</td>' +
				'<td>' + (trackCount > 0 ? trackCount : '-') + '</td>' +
				'<td>' + esc(r.isrc || 'N/A') + '</td>' +
				'<td>' + esc(r.go_live_date || 'TBD') + '</td>' +
				'<td>' + statusBadgeHtml(r.status) + '</td>' +
				'<td><button class="adm-view-btn view-release" data-id="' + r.id + '" title="View release details"><i data-lucide="eye"></i></button></td>' +
			'</tr>';
		}).join('');

		tbody.querySelectorAll('.view-release').forEach(function (btn) {
			btn.addEventListener('click', function () {
				showReleaseDetail(parseInt(btn.getAttribute('data-id')));
			});
		});

		if (pagWrap) {
			pagWrap.innerHTML = renderPaginationHtml(releasesPagination, function (page) {
				loadReleases(page);
			});
		}
	}

	/* ----- Track list (albums hold many tracks, singles one) ----- */
	function renderTracks(release) {
		var tbody = document.getElementById('rel-tracks-tbody');
		var note  = document.getElementById('rel-tracks-note');
		if (!tbody) return;

		var tracks = release.tracks || [];

		if (tracks.length === 0) {
			tbody.innerHTML = '';
			if (note) {
				note.textContent = release.status === 'draft'
					? 'Tracks are added once you upload your audio.'
					: 'The track list for this release is being prepared.';
			}
			return;
		}

		if (note) {
			note.textContent = tracks.length + (tracks.length === 1 ? ' track in this release.' : ' tracks in this release.');
		}

		tbody.innerHTML = tracks.map(function (t) {
			return '<tr>' +
				'<td>' + esc(t.track_no) + '</td>' +
				'<td><strong>' + esc(t.title) + '</strong></td>' +
				'<td>' + esc(t.isrc || 'N/A') + '</td>' +
				'<td>' + esc(t.duration || 'N/A') + '</td>' +
			'</tr>';
		}).join('');
	}

	/* ----- Platform live links, added by admin ----- */
	function renderLinks(release) {
		var list = document.getElementById('rel-links-list');
		if (!list) return;

		var links = release.links || [];

		if (links.length === 0) {
			list.innerHTML = '<p class="panel-note">No live links yet. Links appear here as each platform goes live.</p>';
			return;
		}

		list.innerHTML = links.map(function (l) {
			return '<a class="panel-link-btn" href="' + esc(l.url) + '" target="_blank" rel="noopener noreferrer">' +
				'<i data-lucide="play"></i> ' + esc(l.platform) +
				'<i data-lucide="external-link" class="panel-link-external"></i>' +
			'</a>';
		}).join('');
	}

	function showReleaseDetail(id) {
		var r = currentReleases.find(function (rel) { return rel.id === id; });
		if (!r) return;

		document.getElementById('rel-detail-title').textContent = r.title;
		var statusEl = document.getElementById('rel-detail-status');
		statusEl.textContent = r.status;
		statusEl.className = 'panel-status-badge status-' + r.status;

		document.getElementById('rel-detail-type').textContent   = r.type;
		document.getElementById('rel-detail-isrc').textContent   = r.isrc || 'N/A';
		document.getElementById('rel-detail-upc').textContent    = r.upc || 'N/A';
		document.getElementById('rel-detail-golive').textContent = r.go_live_date || 'TBD';

		var artistsList = (r.artists || []).map(function (a) {
			return '<span class="panel-status-badge" style="background:var(--secondary);">' + esc(a.role) + ': ' + esc(a.name) + '</span>';
		}).join(' ');
		document.getElementById('rel-detail-artists').innerHTML = artistsList || 'N/A';

		renderTracks(r);
		renderLinks(r);

		var historyHtml = (r.history || []).map(function (h) {
			return '<div class="release-history-item">' +
				'<strong>' + esc(h.action) + '</strong>' +
				'<span class="panel-muted">' + esc(h.created_at) + '</span>' +
				(h.message ? '<p>' + esc(h.message) + '</p>' : '') +
				(h.reviewer_note ? '<p class="panel-note"><i data-lucide="message-square"></i> ' + esc(h.reviewer_note) + '</p>' : '') +
			'</div>';
		}).join('');
		document.getElementById('rel-detail-history').innerHTML = historyHtml || '<p class="panel-note">No history yet.</p>';

		document.getElementById('rel-detail-lyrics').textContent = r.lyrics || 'N/A';

		var detailPanel = document.getElementById('release-detail-panel');
		detailPanel.classList.remove('d-none');
		document.getElementById('releases-list-panel').classList.add('d-none');
	}

	var backBtn = document.getElementById('back-to-releases');
	if (backBtn) {
		backBtn.addEventListener('click', function () {
			document.getElementById('release-detail-panel').classList.add('d-none');
			document.getElementById('releases-list-panel').classList.remove('d-none');
		});
	}

	var createForm = document.getElementById('create-release-form');
	if (createForm) {
		createForm.addEventListener('submit', function (e) {
			e.preventDefault();
			var artists = [];
			document.querySelectorAll('.release-artist-row').forEach(function (row) {
				var role = row.querySelector('.ra-role').value.trim();
				var name = row.querySelector('.ra-name').value.trim();
				if (role && name) { artists.push({ role: role, name: name }); }
			});

			var payload = {
				title: document.getElementById('rel-title').value.trim(),
				type: document.getElementById('rel-type').value,
				isrc: document.getElementById('rel-isrc').value.trim(),
				go_live_date: document.getElementById('rel-golive').value,
				lyrics: document.getElementById('rel-lyrics').value.trim(),
				dolby: document.getElementById('rel-dolby').checked ? 1 : 0,
				apple_itunes: document.getElementById('rel-apple').checked ? 1 : 0,
				artists: artists,
				csrf_token: config.csrfToken
			};

			fetch(basePath + 'includes/customer/release-save.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload)
			})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (data.success) {
					alert(data.message);
					window.location.reload();
				} else {
					alert(data.message);
				}
			})
			.catch(function () { alert('Save failed.'); });
		});
	}

	var addArtistBtn = document.getElementById('add-artist-row');
	if (addArtistBtn) {
		addArtistBtn.addEventListener('click', function () {
			var container = document.getElementById('release-artists-container');
			var div = document.createElement('div');
			div.className = 'release-artist-row';
			div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
			div.innerHTML =
				'<input type="text" class="panel-input ra-role" placeholder="Role (e.g. Singer)" style="flex:1;">' +
				'<input type="text" class="panel-input ra-name" placeholder="Name" style="flex:1;">' +
				'<button type="button" class="adm-view-btn remove-artist-row"><i data-lucide="x"></i></button>';
			container.appendChild(div);
			div.querySelector('.remove-artist-row').addEventListener('click', function () { div.remove(); });
		});
	}

	// Initial load
	if (document.getElementById('releases-tbody')) {
		loadReleases(1);
	}
});
