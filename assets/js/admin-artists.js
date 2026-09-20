document.addEventListener('DOMContentLoaded', function () {

    var basePath = adminDashboardConfig.basePath;
    var categories = [];
    var artistsPagination = { currentPage: 1, totalPages: 1 };
    var artistsSearch = '';

    initTabNav('.adm-nav-btn[data-tab]', '.adm-tab', 'adm-tab-', '.adm-sidebar', '#admin-menu-toggle');

    function loadCategories() {
        return fetch(basePath + 'includes/admin/categories-list.php?t=' + Date.now())
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) { categories = data.categories; }
                return categories;
            });
    }

    function loadArtists(page) {
        page = page || 1;
        var params = 'page=' + page + '&per_page=10';
        if (artistsSearch) params += '&search=' + encodeURIComponent(artistsSearch);

        return fetch(basePath + 'includes/admin/artists-list.php?' + params + '&t=' + Date.now())
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    artistsPagination = data.pagination;
                    renderArtistsTable(data.artists);
                }
                return data.artists || [];
            });
    }

    function renderArtistsTable(artistsList) {
        var tbody = document.getElementById('adm-artists-tbody');
        var emptyMsg = document.getElementById('adm-artists-empty');
        var pagWrap = document.getElementById('adm-artists-pagination');
        if (!tbody) return;

        if (!artistsList || artistsList.length === 0) {
            tbody.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('d-none');
            if (pagWrap) pagWrap.innerHTML = '';
            return;
        }
        if (emptyMsg) emptyMsg.classList.add('d-none');

        tbody.innerHTML = artistsList.map(function (a) {
            var imgSrc = a.image;
            if (imgSrc && imgSrc.indexOf('http') !== 0) {
                imgSrc = basePath + imgSrc;
            }
            var imgHtml = imgSrc
                ? '<img src="' + imgSrc + '" alt="" style="width:40px;height:56px;object-fit:cover;border-radius:6px;">'
                : '<div style="width:40px;height:56px;background:var(--secondary);border-radius:6px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-user" style="color:var(--muted);"></i></div>';
            return '<tr>' +
                '<td>' + imgHtml + '</td>' +
                '<td><strong>' + a.name + '</strong></td>' +
                '<td>' + a.category + '</td>' +
                '<td>' + (a.location || 'N/A') + '</td>' +
                '<td>' + (a.is_active == 1
                    ? '<span class="panel-status-badge status-delivered">Active</span>'
                    : '<span class="panel-status-badge status-cancelled">Inactive</span>') + '</td>' +
                '<td>' +
                    '<button class="adm-view-btn adm-edit-artist" data-id="' + a.id + '"><i class="fa-solid fa-pen"></i></button> ' +
                    '<button class="adm-view-btn adm-delete-artist" data-id="' + a.id + '" style="color:#ef4444;border-color:#fca5a5;"><i class="fa-solid fa-trash"></i></button>' +
                '</td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('.adm-edit-artist').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = parseInt(this.getAttribute('data-id'));
                openArtistModal(id, artistsList);
            });
        });
        tbody.querySelectorAll('.adm-delete-artist').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (confirm('Are you sure you want to delete this artist?')) {
                    deleteArtist(parseInt(this.getAttribute('data-id')));
                }
            });
        });

        if (pagWrap) {
            pagWrap.innerHTML = renderPaginationHtml(artistsPagination, function (page) {
                loadArtists(page);
            });
        }
    }

    var artistModal = document.getElementById('adm-artist-modal');
    var artistBackdrop = document.getElementById('artist-modal-backdrop');
    var artistCloseBtn = document.getElementById('artist-modal-close');

    document.getElementById('adm-add-artist-btn').addEventListener('click', function () {
        openArtistModal(0, []);
    });

    function openArtistModal(id, artistsList) {
        var form = document.getElementById('artist-form');
        form.reset();
        document.getElementById('artist-modal-id').value = 0;
        document.getElementById('artist-modal-title').textContent = id > 0 ? 'Edit Artist' : 'Add New Artist';

        var catSelect = document.getElementById('artist-category');
        catSelect.innerHTML = '<option value="">Select Category</option>';
        categories.forEach(function (c) {
            catSelect.innerHTML += '<option value="' + c.id + '">' + c.name + '</option>';
        });

        var pricingBody = document.getElementById('artist-pricing-body');
        pricingBody.innerHTML = '';
        addPricingRow('', '');

        if (id > 0) {
            var artist = artistsList.find(function (a) { return a.id === id; });
            if (artist) {
                document.getElementById('artist-modal-id').value = artist.id;
                document.getElementById('artist-name').value = artist.name;
                document.getElementById('artist-category').value = artist.category_id || '';
                document.getElementById('artist-location').value = artist.location || '';
                document.getElementById('artist-bio').value = artist.bio || '';
                document.getElementById('artist-active').checked = artist.is_active == 1;

                if (artist.image) {
                    var imgPath = artist.image;
                    if (imgPath.indexOf('http') !== 0) { imgPath = basePath + imgPath; }
                    document.getElementById('artist-image-url').value = artist.image;
                    document.getElementById('artist-image-preview').src = imgPath;
                    document.getElementById('artist-image-preview').style.display = 'block';
                } else {
                    document.getElementById('artist-image-url').value = '';
                    document.getElementById('artist-image-preview').style.display = 'none';
                }

                if (artist.pricing && artist.pricing.length > 0) {
                    pricingBody.innerHTML = '';
                    artist.pricing.forEach(function (p) {
                        addPricingRow(p.service_type, p.price);
                    });
                }
            }
        }

        artistModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeArtistModal() {
        artistModal.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (artistCloseBtn) artistCloseBtn.addEventListener('click', closeArtistModal);
    if (artistBackdrop) artistBackdrop.addEventListener('click', closeArtistModal);

    function addPricingRow(serviceType, price) {
        var tbody = document.getElementById('artist-pricing-body');
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="panel-input pricing-service" value="' + (serviceType || '') + '" placeholder="e.g. Each Video"></td>' +
            '<td><input type="number" class="panel-input pricing-price" value="' + (price || '') + '" placeholder="0.00" step="0.01" min="0"></td>' +
            '<td><button type="button" class="adm-view-btn remove-pricing-row" style="color:#ef4444;border-color:#fca5a5;"><i class="fa-solid fa-xmark"></i></button></td>';
        tbody.appendChild(tr);

        tr.querySelector('.remove-pricing-row').addEventListener('click', function () {
            tr.remove();
        });
    }

    document.getElementById('add-pricing-row').addEventListener('click', function () {
        addPricingRow('', '');
    });

    document.getElementById('artist-image-input').addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) { alert('Image must be under 2 MB.'); this.value = ''; return; }
        var fd = new FormData();
        fd.append('artist_image', file);
        fetch(basePath + 'includes/upload-artist-image.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    document.getElementById('artist-image-url').value = data.url;
                    var previewSrc = data.url;
                    if (previewSrc.indexOf('http') !== 0) { previewSrc = basePath + previewSrc; }
                    document.getElementById('artist-image-preview').src = previewSrc;
                    document.getElementById('artist-image-preview').style.display = 'block';
                } else {
                    alert(data.message);
                }
            })
            .catch(function () { alert('Upload failed.'); });
    });

    document.getElementById('artist-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var pricingRows = document.querySelectorAll('#artist-pricing-body tr');
        var pricing = [];
        pricingRows.forEach(function (row) {
            var svc = row.querySelector('.pricing-service').value.trim();
            var price = parseFloat(row.querySelector('.pricing-price').value) || 0;
            if (svc) { pricing.push({ service_type: svc, price: price }); }
        });

        var payload = {
            id: parseInt(document.getElementById('artist-modal-id').value) || 0,
            name: document.getElementById('artist-name').value.trim(),
            category_id: parseInt(document.getElementById('artist-category').value) || 0,
            location: document.getElementById('artist-location').value.trim(),
            bio: document.getElementById('artist-bio').value.trim(),
            is_active: document.getElementById('artist-active').checked ? 1 : 0,
            image: document.getElementById('artist-image-url').value,
            pricing: pricing
        };

        fetch(basePath + 'includes/admin/artist-save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                closeArtistModal();
                loadArtists(artistsPagination.currentPage);
            } else {
                alert(data.message);
            }
        })
        .catch(function () { alert('Save failed.'); });
    });

    function deleteArtist(id) {
        fetch(basePath + 'includes/admin/artist-delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) { loadArtists(artistsPagination.currentPage); }
            else { alert(data.message); }
        })
        .catch(function () { alert('Delete failed.'); });
    }

    loadCategories().then(function () { loadArtists(1); });
});
