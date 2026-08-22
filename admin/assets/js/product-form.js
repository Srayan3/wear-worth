/* Product form: AJAX image manager (upload, delete, set primary, drag reorder)
   and repeatable-row builders for variations and the size chart. */
(function () {
    'use strict';

    var csrfToken = document.querySelector('input[name="csrf_token"]') ? document.querySelector('input[name="csrf_token"]').value : '';

    // ---- Image manager ----
    var grid = document.getElementById('imageGrid');
    var uploadInput = document.getElementById('imageUploadInput');
    var uploadStatus = document.getElementById('uploadStatus');

    if (uploadInput && grid) {
        uploadInput.addEventListener('change', function () {
            var file = uploadInput.files[0];
            if (!file) return;
            var formData = new FormData();
            formData.append('action', 'upload');
            formData.append('product_id', grid.dataset.productId);
            formData.append('csrf_token', csrfToken);
            formData.append('image', file);

            uploadStatus.textContent = 'Uploading…';
            fetch('product-images.php', { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) {
                        uploadStatus.textContent = data.message || 'Upload failed.';
                        return;
                    }
                    uploadStatus.textContent = '';
                    var tile = document.createElement('div');
                    tile.className = 'a-image-tile' + (data.image.is_primary ? ' is-primary' : '');
                    tile.draggable = true;
                    tile.dataset.imageId = data.image.id;
                    tile.innerHTML =
                        (data.image.is_primary ? '<span class="a-image-tile__primary-flag">Primary</span>' : '') +
                        '<img src="' + data.image.url + '" alt="">' +
                        '<div class="a-image-tile__bar"><button type="button" data-set-primary>Set Primary</button><button type="button" data-delete-image>Delete</button></div>';
                    grid.appendChild(tile);
                    bindTile(tile);
                    uploadInput.value = '';
                })
                .catch(function () { uploadStatus.textContent = 'Upload failed. Please try again.'; });
        });

        function bindTile(tile) {
            tile.querySelector('[data-set-primary]').addEventListener('click', function () {
                post({ action: 'set_primary', product_id: grid.dataset.productId, image_id: tile.dataset.imageId }).then(function () {
                    grid.querySelectorAll('.a-image-tile').forEach(function (t) {
                        t.classList.remove('is-primary');
                        var flag = t.querySelector('.a-image-tile__primary-flag');
                        if (flag) flag.remove();
                    });
                    tile.classList.add('is-primary');
                    tile.insertAdjacentHTML('afterbegin', '<span class="a-image-tile__primary-flag">Primary</span>');
                });
            });
            tile.querySelector('[data-delete-image]').addEventListener('click', function () {
                if (!window.confirm('Delete this image?')) return;
                post({ action: 'delete', product_id: grid.dataset.productId, image_id: tile.dataset.imageId }).then(function () {
                    tile.remove();
                });
            });
            tile.addEventListener('dragstart', function () { tile.classList.add('is-dragging'); });
            tile.addEventListener('dragend', function () {
                tile.classList.remove('is-dragging');
                var ids = Array.prototype.map.call(grid.querySelectorAll('.a-image-tile'), function (t) { return t.dataset.imageId; });
                post({ action: 'reorder', product_id: grid.dataset.productId, image_ids: ids });
            });
        }

        grid.querySelectorAll('.a-image-tile').forEach(bindTile);

        grid.addEventListener('dragover', function (e) {
            e.preventDefault();
            var dragging = grid.querySelector('.is-dragging');
            if (!dragging) return;
            var after = getDragAfterElement(grid, e.clientX);
            if (after == null) {
                grid.appendChild(dragging);
            } else {
                grid.insertBefore(dragging, after);
            }
        });

        function getDragAfterElement(container, x) {
            var els = Array.prototype.slice.call(container.querySelectorAll('.a-image-tile:not(.is-dragging)'));
            return els.reduce(function (closest, child) {
                var box = child.getBoundingClientRect();
                var offset = x - box.left - box.width / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function post(body) {
            var params = new URLSearchParams();
            Object.keys(body).forEach(function (k) {
                if (Array.isArray(body[k])) {
                    body[k].forEach(function (v) { params.append(k + '[]', v); });
                } else {
                    params.append(k, body[k]);
                }
            });
            params.append('csrf_token', csrfToken);
            return fetch('product-images.php', { method: 'POST', body: params }).then(function (r) { return r.json(); });
        }
    }

    // ---- Repeater: variations ----
    var addVariationBtn = document.getElementById('addVariationRow');
    if (addVariationBtn) {
        addVariationBtn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'a-repeater-row a-repeater-row--size';
            row.innerHTML =
                '<input type="text" name="var_size[]" class="a-input" placeholder="Size (e.g. M)">' +
                '<input type="text" name="var_color[]" class="a-input" placeholder="Color name">' +
                '<input type="color" name="var_hex[]" class="a-input" style="padding:2px;" value="#141414">' +
                '<input type="text" name="var_sku[]" class="a-input" placeholder="Variant SKU">' +
                '<input type="number" name="var_price[]" class="a-input" placeholder="Price override" step="0.01">' +
                '<input type="number" name="var_qty[]" class="a-input" placeholder="Stock" style="max-width:80px;">' +
                '<button type="button" class="a-repeater-remove">✕</button>';
            row.querySelector('.a-repeater-remove').addEventListener('click', function () { row.remove(); });
            document.getElementById('variationRows').appendChild(row);
        });
    }

    // ---- Repeater: size chart (clothing) ----
    var addSizeChartBtn = document.getElementById('addSizeChartRow');
    if (addSizeChartBtn) {
        addSizeChartBtn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'a-repeater-row';
            row.innerHTML =
                '<input type="text" name="sc_size[]" class="a-input" placeholder="Size (e.g. M)">' +
                '<input type="number" step="0.1" name="sc_chest[]" class="a-input" placeholder="Chest (in)">' +
                '<input type="number" step="0.1" name="sc_waist[]" class="a-input" placeholder="Waist (in)">' +
                '<input type="number" step="0.1" name="sc_hip[]" class="a-input" placeholder="Hip (in)">' +
                '<input type="number" step="0.1" name="sc_length[]" class="a-input" placeholder="Length (in)">' +
                '<button type="button" class="a-repeater-remove">✕</button>';
            row.querySelector('.a-repeater-remove').addEventListener('click', function () { row.remove(); });
            document.getElementById('sizeChartRows').appendChild(row);
        });
    }

    // ---- Repeater: size chart (footwear) ----
    var addFootwearChartBtn = document.getElementById('addFootwearChartRow');
    if (addFootwearChartBtn) {
        addFootwearChartBtn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'a-repeater-row a-repeater-row--4col';
            row.innerHTML =
                '<input type="text" name="sc_brand_size[]" class="a-input" placeholder="Brand Size (e.g. 38)">' +
                '<input type="text" name="sc_uk[]" class="a-input" placeholder="UK / Bata">' +
                '<input type="text" name="sc_eu[]" class="a-input" placeholder="EU / Apex">' +
                '<input type="text" name="sc_us[]" class="a-input" placeholder="US">' +
                '<button type="button" class="a-repeater-remove">✕</button>';
            row.querySelector('.a-repeater-remove').addEventListener('click', function () { row.remove(); });
            document.getElementById('footwearChartRows').appendChild(row);
        });
    }

    // ---- Size chart type toggle ----
    // Only the visible block's inputs should submit — disabling the hidden
    // block's inputs excludes them from the form POST entirely.
    var sizeChartTypeSelect = document.getElementById('sizeChartType');
    if (sizeChartTypeSelect) {
        var clothingBlock = document.getElementById('clothingSizeChart');
        var footwearBlock = document.getElementById('footwearSizeChart');
        function syncSizeChartType() {
            var isFootwear = sizeChartTypeSelect.value === 'footwear';
            clothingBlock.style.display = isFootwear ? 'none' : '';
            footwearBlock.style.display = isFootwear ? '' : 'none';
            clothingBlock.querySelectorAll('input').forEach(function (el) { el.disabled = isFootwear; });
            footwearBlock.querySelectorAll('input').forEach(function (el) { el.disabled = !isFootwear; });
        }
        sizeChartTypeSelect.addEventListener('change', syncSizeChartType);
    }
})();
