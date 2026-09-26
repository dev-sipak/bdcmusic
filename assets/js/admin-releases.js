document.addEventListener('DOMContentLoaded', function () {

    if (typeof adminReleasesConfig === 'undefined') return;

    var config = adminReleasesConfig;
    var basePath = config.basePath;
    var statuses = config.statuses || {};
    var statusKeys = Object.keys(statuses);
    var typeLabels = config.types || {};
    var releasesPagination = { currentPage: 1, totalPages: 1 };
    var filters = { status: config.activeTab || 'all', search: '' };
    var currentReleases = [];
    var currentReleaseId = null;

    // Platforms BDC distributes to. Offered as one-click presets so admin does
    // not retype the same names; anything else can still be typed in.
    var PLATFORM_PRESETS = [
        'Spotify', 'Apple Music', 'YouTube Music', 'Amazon Music',
        'JioSaavn', 'Gaana', 'Tencent Music', 'Hungama'
    ];

    function esc(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function loadReleases(page) {
        page = page || 1;
        var params = 'page=' + page + '&per_page=10';
        if (filters.status !== 'all') params += '&status=' + encodeURIComponent(filters.status);
        if (filters.search) params += '&search=' + encodeURIComponent(filters.search);

        fetch(basePath + 'includes/admin/releases-list.php?' + params)
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
        var tbody = document.getElementById('admin-releases-tbody');
        var emptyMsg = document.getElementById('admin-releases-empty');
        var pagWrap = document.getElementById('admin-releases-pagination');
        if (!tbody) return;

        if (!currentReleases || currentReleases.length === 0) {
            tbody.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('d-none');
            if (pagWrap) pagWrap.innerHTML = '';
            return;
        }
        if (emptyMsg) emptyMsg.classList.add('d-none');

        tbody.innerHTML = currentReleases.map(function (r) {
            var linkCount = (r.links || []).filter(function (l) { return l.is_active; }).length;
            return '<tr>' +
                '<td><strong>' + esc(r.title) + '</strong></td>' +
                '<td>' + esc(r.customer || 'N/A') + '</td>' +
                '<td>' + esc(typeLabels[r.type] || r.type) + '</td>' +
                '<td title="' + linkCount + ' active platform link(s)">' + (r.track_count || 0) + '</td>' +
                '<td>' + esc(r.isrc || 'N/A') + '</td>' +
                '<td>' + esc(r.go_live_date || 'TBD') + '</td>' +
                '<td>' + statusBadgeHtml(r.status) + '</td>' +
                '<td><button class="adm-view-btn view-release" data-id="' + r.id + '" title="Edit release"><i class="fa-solid fa-pen"></i></button></td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('.view-release').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openReleaseModal(parseInt(btn.getAttribute('data-id')));
            });
        });

        if (pagWrap) {
            pagWrap.innerHTML = renderPaginationHtml(releasesPagination, function (page) {
                loadReleases(page);
            });
        }
    }

    var statusFilter = document.getElementById('admin-release-status');
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            filters.status = this.value;
            loadReleases(1);
        });
    }

    var searchInput = document.getElementById('admin-release-search');
    if (searchInput) {
        var searchTimer;
        searchInput.addEventListener('input', function () {
            var val = this.value;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                filters.search = val;
                loadReleases(1);
            }, 400);
        });
    }

    /* ----- Editor rows ----- */
    function addTrackRow(track) {
        var container = document.getElementById('release-tracks-editor');
        var row = document.createElement('div');
        row.className = 'release-editor-row release-track-row';
        row.innerHTML =
            '<input type="number" class="panel-input re-track-no" min="1" placeholder="#" value="' + esc(track && track.track_no ? track.track_no : '') + '">' +
            '<input type="text" class="panel-input re-track-title" placeholder="Track title" value="' + esc(track ? track.title : '') + '">' +
            '<input type="text" class="panel-input re-track-isrc" placeholder="ISRC (optional)" value="' + esc(track ? track.isrc : '') + '">' +
            '<input type="text" class="panel-input re-track-duration" placeholder="3:45 (optional)" value="' + esc(track ? track.duration : '') + '">' +
            '<button type="button" class="adm-view-btn re-remove-row" title="Remove track"><i class="fa-solid fa-xmark"></i></button>';
        container.appendChild(row);
        row.querySelector('.re-remove-row').addEventListener('click', function () { row.remove(); });
    }

    function addLinkRow(link) {
        var container = document.getElementById('release-links-editor');
        var row = document.createElement('div');
        row.className = 'release-editor-row release-link-row';
        row.innerHTML =
            '<input type="text" class="panel-input re-link-platform" placeholder="Platform" value="' + esc(link ? link.platform : '') + '">' +
            '<input type="url" class="panel-input re-link-url" placeholder="https://" value="' + esc(link ? link.url : '') + '">' +
            '<label class="release-link-active" title="Show this link to the customer">' +
                '<input type="checkbox" class="re-link-active"' + (link && link.is_active ? ' checked' : '') + '> Live' +
            '</label>' +
            '<button type="button" class="adm-view-btn re-remove-row" title="Remove platform"><i class="fa-solid fa-xmark"></i></button>';
        container.appendChild(row);
        row.querySelector('.re-remove-row').addEventListener('click', function () { row.remove(); });
    }

    function renderPlatformPresets() {
        var wrap = document.getElementById('release-platform-presets');
        if (!wrap) return;
        wrap.innerHTML = PLATFORM_PRESETS.map(function (name) {
            return '<button type="button" class="release-preset-btn" data-platform="' + esc(name) + '">' + esc(name) + '</button>';
        }).join('');

        // A preset fills in the platform name of the first row that is still
        // missing one, so clicking several in a row builds up the list.
        wrap.querySelectorAll('.release-preset-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var rows = document.querySelectorAll('#release-links-editor .release-link-row');
                var target = null;
                rows.forEach(function (row) {
                    if (!target && !row.querySelector('.re-link-platform').value.trim()) {
                        target = row;
                    }
                });
                if (!target) {
                    addLinkRow(null);
                    rows = document.querySelectorAll('#release-links-editor .release-link-row');
                    target = rows[rows.length - 1];
                }
                target.querySelector('.re-link-platform').value = btn.getAttribute('data-platform');
                target.querySelector('.re-link-url').focus();
            });
        });
    }

    /* ----- Modal ----- */
    var modal    = document.getElementById('admin-release-modal');
    var backdrop = document.getElementById('release-modal-backdrop');
    var closeBtn = document.getElementById('release-modal-close');

    function openReleaseModal(id) {
        var r = currentReleases.find(function (rel) { return rel.id === id; });
        if (!r) return;

        currentReleaseId = id;

        document.getElementById('release-modal-title').textContent    = r.title;
        document.getElementById('release-modal-customer').textContent = r.customer || 'N/A';
        document.getElementById('release-modal-type').textContent     = typeLabels[r.type] || r.type;
        document.getElementById('release-modal-isrc').textContent     = r.isrc || 'N/A';
        document.getElementById('release-modal-upc').textContent      = r.upc || 'N/A';

        var statusSelect = document.getElementById('release-modal-status');
        statusSelect.innerHTML = statusKeys.map(function (key) {
            return '<option value="' + esc(key) + '">' + esc(statuses[key]) + '</option>';
        }).join('');
        statusSelect.value = r.status;

        document.getElementById('release-modal-note').value = '';

        var tracksEditor = document.getElementById('release-tracks-editor');
        tracksEditor.innerHTML = '';
        (r.tracks || []).forEach(addTrackRow);
        // A release with no track rows yet should still show one row to fill in.
        if ((r.tracks || []).length === 0) addTrackRow(null);

        var linksEditor = document.getElementById('release-links-editor');
        linksEditor.innerHTML = '';
        (r.links || []).forEach(addLinkRow);

        var hint = document.getElementById('release-tracks-hint');
        if (hint) {
            var expected = r.type === 'album' ? 'one row per track on the album'
                : (r.type === 'ep' ? 'one row per track on the EP' : 'a single carries exactly one track');
            hint.textContent = 'This is a ' + (typeLabels[r.type] || r.type) + ', so it needs ' + expected + '.';
        }

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('open');
        document.body.style.overflow = '';
        currentReleaseId = null;
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

    var addTrackBtn = document.getElementById('release-add-track');
    if (addTrackBtn) {
        addTrackBtn.addEventListener('click', function () { addTrackRow(null); });
    }
    var addLinkBtn = document.getElementById('release-add-link');
    if (addLinkBtn) {
        addLinkBtn.addEventListener('click', function () { addLinkRow(null); });
    }

    function postRelease(payload, button, busyLabel) {
        var originalHtml = button.innerHTML;
        button.disabled = true;
        button.textContent = busyLabel;

        return fetch(basePath + 'includes/admin/release-save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            button.disabled = false;
            button.innerHTML = originalHtml;
            if (data && data.success) {
                closeModal();
                loadReleases(releasesPagination.currentPage);
            } else {
                alert(data && data.message ? data.message : 'Update failed.');
            }
        })
        .catch(function () {
            button.disabled = false;
            button.innerHTML = originalHtml;
            alert('Update failed. Please try again.');
        });
    }

    var saveStatusBtn = document.getElementById('release-modal-save-status');
    if (saveStatusBtn) {
        saveStatusBtn.addEventListener('click', function () {
            if (!currentReleaseId) return;
            postRelease({
                id: currentReleaseId,
                status: document.getElementById('release-modal-status').value,
                reviewer_note: document.getElementById('release-modal-note').value.trim()
            }, saveStatusBtn, 'Updating...');
        });
    }

    var saveContentBtn = document.getElementById('release-modal-save-content');
    if (saveContentBtn) {
        saveContentBtn.addEventListener('click', function () {
            if (!currentReleaseId) return;

            // Track numbers are renumbered from the row order the admin sees,
            // so gaps from deleted rows never end up stored.
            var trackRows = document.querySelectorAll('#release-tracks-editor .release-track-row');
            var tracks = [];
            trackRows.forEach(function (row, index) {
                var title = row.querySelector('.re-track-title').value.trim();
                if (!title) return;
                tracks.push({
                    track_no: index + 1,
                    title: title,
                    isrc: row.querySelector('.re-track-isrc').value.trim(),
                    duration: row.querySelector('.re-track-duration').value.trim()
                });
            });

            var linkRows = document.querySelectorAll('#release-links-editor .release-link-row');
            var links = [];
            linkRows.forEach(function (row) {
                var platform = row.querySelector('.re-link-platform').value.trim();
                var url = row.querySelector('.re-link-url').value.trim();
                if (!platform || !url) return;
                links.push({
                    platform: platform,
                    url: url,
                    is_active: row.querySelector('.re-link-active').checked
                });
            });

            var payload = { id: currentReleaseId, tracks: tracks, links: links };
            var note = document.getElementById('release-modal-note').value.trim();
            if (note) payload.reviewer_note = note;

            postRelease(payload, saveContentBtn, 'Saving...');
        });
    }

    renderPlatformPresets();
    if (document.getElementById('admin-releases-tbody')) {
        loadReleases(1);
    }
});
