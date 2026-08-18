/* Product detail page: gallery switching, size/color selection with stock
   awareness, quantity stepper, and description/size-chart tabs. */
(function () {
    'use strict';

    var root = document.querySelector('[data-product-root]');
    if (!root) return;

    var variations = JSON.parse(root.dataset.variations || '[]');

    // ---- Gallery ----
    var mainImages = root.querySelectorAll('.gallery__main img');
    var thumbs = root.querySelectorAll('.gallery__thumbs button');
    thumbs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var idx = btn.dataset.index;
            thumbs.forEach(function (t) { t.classList.remove('is-active'); });
            btn.classList.add('is-active');
            mainImages.forEach(function (img) {
                img.classList.toggle('is-active', img.dataset.index === idx);
            });
        });
    });

    // ---- Tabs ----
    var tabButtons = root.querySelectorAll('.tabs__nav button');
    var tabPanels = root.querySelectorAll('.tabs__panel');
    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tabButtons.forEach(function (b) { b.classList.remove('is-active'); });
            tabPanels.forEach(function (p) { p.classList.remove('is-active'); });
            btn.classList.add('is-active');
            document.getElementById(btn.dataset.tab).classList.add('is-active');
        });
    });

    // ---- Variation selection ----
    var sizeButtons = root.querySelectorAll('[data-size-option]');
    var colorButtons = root.querySelectorAll('[data-color-option]');
    var variationInput = root.querySelector('input[name="variation_id"]');
    var selectedSizeEl = root.querySelector('[data-selected-size]');
    var selectedColorEl = root.querySelector('[data-selected-color]');
    var stockNote = root.querySelector('[data-stock-note]');
    var actionButtons = root.querySelectorAll('[data-add-to-cart-btn], [data-buy-now]');
    var qtyInput = root.querySelector('[data-qty-input]');

    function setActionButtonsDisabled(disabled) {
        actionButtons.forEach(function (btn) { btn.disabled = disabled; });
    }

    var state = { size: sizeButtons.length ? null : undefined, color: colorButtons.length ? null : undefined };

    function findVariation() {
        return variations.find(function (v) {
            var sizeMatch = state.size === undefined || v.size_label === state.size;
            var colorMatch = state.color === undefined || v.color_name === state.color;
            return sizeMatch && colorMatch;
        });
    }

    function isSizeAvailable(size) {
        return variations.some(function (v) {
            var colorMatch = state.color === undefined || state.color === null || v.color_name === state.color;
            return v.size_label === size && colorMatch && v.stock_quantity > 0;
        });
    }

    function updateUI() {
        sizeButtons.forEach(function (btn) {
            var available = isSizeAvailable(btn.dataset.sizeOption);
            btn.classList.toggle('is-disabled', !available);
            btn.classList.toggle('is-selected', state.size === btn.dataset.sizeOption);
        });
        colorButtons.forEach(function (btn) {
            btn.classList.toggle('is-selected', state.color === btn.dataset.colorOption);
        });
        if (selectedSizeEl && state.size) selectedSizeEl.textContent = state.size;
        if (selectedColorEl && state.color) selectedColorEl.textContent = state.color;

        var needsSize = sizeButtons.length > 0;
        var needsColor = colorButtons.length > 0;
        var ready = (!needsSize || state.size) && (!needsColor || state.color);

        if (!variations.length) {
            // simple product, no variations
            setActionButtonsDisabled(false);
            return;
        }

        if (!ready) {
            if (stockNote) { stockNote.textContent = 'Select options to see availability'; stockNote.className = 'stock-note'; }
            setActionButtonsDisabled(true);
            if (variationInput) variationInput.value = '';
            return;
        }

        var variation = findVariation();
        if (!variation) {
            if (stockNote) { stockNote.textContent = 'This combination is unavailable'; stockNote.className = 'stock-note out'; }
            setActionButtonsDisabled(true);
            return;
        }

        if (variationInput) variationInput.value = variation.id;
        if (variation.stock_quantity <= 0) {
            if (stockNote) { stockNote.textContent = 'Out of stock'; stockNote.className = 'stock-note out'; }
            setActionButtonsDisabled(true);
        } else if (variation.stock_quantity <= 5) {
            if (stockNote) { stockNote.textContent = 'Only ' + variation.stock_quantity + ' left in stock'; stockNote.className = 'stock-note low'; }
            setActionButtonsDisabled(false);
            if (qtyInput) qtyInput.max = variation.stock_quantity;
        } else {
            if (stockNote) { stockNote.textContent = 'In stock'; stockNote.className = 'stock-note in'; }
            setActionButtonsDisabled(false);
            if (qtyInput) qtyInput.max = variation.stock_quantity;
        }
    }

    sizeButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.classList.contains('is-disabled')) return;
            state.size = btn.dataset.sizeOption;
            updateUI();
        });
    });
    colorButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.color = btn.dataset.colorOption;
            updateUI();
        });
    });
    updateUI();

    // ---- Quantity stepper ----
    var stepper = root.querySelector('[data-qty-stepper]');
    if (stepper && qtyInput) {
        stepper.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var step = parseInt(btn.dataset.step, 10);
                var max = parseInt(qtyInput.max || '99', 10);
                var next = Math.min(max, Math.max(1, parseInt(qtyInput.value, 10) + step));
                qtyInput.value = next;
            });
        });
    }
})();
