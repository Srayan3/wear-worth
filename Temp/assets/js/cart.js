/* Mini-cart drawer: AJAX add-to-cart, live item list, quantity/remove controls. */
(function () {
    'use strict';

    var drawer = document.getElementById('cartDrawer');
    var itemsEl = document.getElementById('cartDrawerItems');
    var subtotalEl = document.getElementById('cartDrawerSubtotal');
    var countEls = document.querySelectorAll('.cart-count');
    var csrfInput = document.querySelector('input[name="csrf_token"]');
    var csrfToken = csrfInput ? csrfInput.value : (window.CSRF_TOKEN || '');
    // Site may be hosted at the domain root or in a subdirectory
    // (e.g. http://localhost/WearWorth/store/) — always build request
    // URLs from this rather than hardcoding a leading slash.
    var BASE = window.SITE_BASE_URL || '/';
    var BASE_PATH = (function () { try { return new URL(BASE).pathname; } catch (e) { return '/'; } })();

    function openDrawer() {
        if (!drawer) return;
        drawer.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        if (!drawer) return;
        drawer.classList.remove('is-open');
        document.body.style.overflow = '';
    }
    document.querySelectorAll('[data-cart-open]').forEach(function (btn) {
        btn.addEventListener('click', function (e) { e.preventDefault(); openDrawer(); refreshDrawer(); });
    });
    document.querySelectorAll('[data-cart-close]').forEach(function (btn) {
        btn.addEventListener('click', closeDrawer);
    });

    function setCartCount(n) {
        countEls.forEach(function (el) {
            el.textContent = n;
            el.style.display = n > 0 ? 'flex' : 'none';
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : str;
        return div.innerHTML;
    }

    function renderDrawer(data) {
        if (!itemsEl) return;
        if (!data.items.length) {
            itemsEl.innerHTML = '<div class="empty-state" style="padding:60px 0;"><p>Your bag is empty.</p><a href="/shop" class="btn btn-outline btn-sm">Start Shopping</a></div>';
        } else {
            itemsEl.innerHTML = data.items.map(function (item) {
                var meta = [item.size, item.color].filter(Boolean).join(' / ');
                return (
                    '<div class="cart-line" data-item-id="' + item.cart_item_id + '">' +
                    '  <div class="cart-line__media"><img src="' + item.image + '" alt="' + escapeHtml(item.name) + '" loading="lazy"></div>' +
                    '  <div class="cart-line__body">' +
                    '    <div class="cart-line__title">' + escapeHtml(item.name) + '</div>' +
                    (meta ? '<div class="cart-line__meta">' + escapeHtml(meta) + '</div>' : '') +
                    '    <div class="cart-line__row">' +
                    '      <div class="qty-stepper" data-qty-stepper>' +
                    '        <button type="button" data-step="-1" aria-label="Decrease quantity">–</button>' +
                    '        <input type="text" inputmode="numeric" value="' + item.quantity + '" readonly>' +
                    '        <button type="button" data-step="1" aria-label="Increase quantity">+</button>' +
                    '      </div>' +
                    '      <strong>' + item.line_total_formatted + '</strong>' +
                    '    </div>' +
                    '    <button type="button" class="cart-line__remove" data-remove>Remove</button>' +
                    '  </div>' +
                    '</div>'
                );
            }).join('');
        }
        if (subtotalEl) subtotalEl.textContent = data.subtotal;
        setCartCount(data.count);
        bindLineEvents();
    }

    function refreshDrawer() {
        fetch(BASE + 'cart/items', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(renderDrawer)
            .catch(function () {});
    }

    function bindLineEvents() {
        if (!itemsEl) return;
        itemsEl.querySelectorAll('[data-step]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var line = btn.closest('.cart-line');
                var input = line.querySelector('input');
                var newQty = parseInt(input.value, 10) + parseInt(btn.dataset.step, 10);
                updateQuantity(line.dataset.itemId, Math.max(0, newQty));
            });
        });
        itemsEl.querySelectorAll('[data-remove]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var line = btn.closest('.cart-line');
                updateQuantity(line.dataset.itemId, 0);
            });
        });
    }

    function updateQuantity(itemId, qty) {
        var body = new URLSearchParams({ cart_item_id: itemId, quantity: qty, csrf_token: csrfToken });
        fetch(BASE + 'cart/update', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        })
            .then(function (r) { return r.json(); })
            .then(function () { refreshDrawer(); if (window.location.pathname === BASE_PATH + 'cart') window.location.reload(); })
            .catch(function () {});
    }

    // ---- Add-to-cart forms anywhere on the page (product card quick-add, product page) ----
    // Track which submit button was actually clicked, since the product page
    // now has two ("Add to Bag" and "Buy Now") in the same form.
    var lastClickedSubmit = null;
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-add-to-cart-form] [type="submit"]');
        if (btn) lastClickedSubmit = btn;
    }, true);

    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-add-to-cart-form]');
        if (!form) return;
        e.preventDefault();

        var submitBtn = (lastClickedSubmit && form.contains(lastClickedSubmit)) ? lastClickedSubmit : form.querySelector('[type="submit"]');
        var isBuyNow = !!(submitBtn && submitBtn.hasAttribute('data-buy-now'));
        var originalText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = isBuyNow ? 'Processing…' : 'Adding…'; }

        fetch(BASE + 'cart/add', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    showToast(data.message, false);
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
                    return;
                }
                setCartCount(data.cart_count);
                if (isBuyNow) {
                    window.location.href = BASE + 'checkout';
                    return;
                }
                showToast(data.message, true);
                openDrawer();
                refreshDrawer();
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            })
            .catch(function () {
                showToast('Something went wrong. Please try again.', false);
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
            });
    });

    function showToast(message, success) {
        if (!message) return;
        var stack = document.querySelector('.flash-stack') || (function () {
            var s = document.createElement('div');
            s.className = 'flash-stack';
            document.body.appendChild(s);
            return s;
        })();
        var el = document.createElement('div');
        el.className = 'flash flash--' + (success ? 'success' : 'error');
        el.textContent = message;
        stack.appendChild(el);
        setTimeout(function () {
            el.style.transition = 'opacity 300ms ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 320);
        }, 3600);
    }

    window.AtelierCart = { refreshDrawer: refreshDrawer, openDrawer: openDrawer };
})();
