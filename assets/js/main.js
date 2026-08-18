/* Global site behavior: header scroll state, mobile nav drawer, flash timeout. */
(function () {
    'use strict';

    // ---- Sticky header shadow on scroll ----
    var header = document.querySelector('.site-header');
    if (header) {
        var onScroll = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // ---- Mobile nav drawer ----
    var drawer = document.getElementById('mobileDrawer');
    var openBtn = document.getElementById('navToggle');
    var closeBtn = document.getElementById('mobileDrawerClose');
    var scrim = drawer ? drawer.querySelector('.mobile-drawer__scrim') : null;

    function toggleDrawer(open) {
        if (!drawer) return;
        drawer.classList.toggle('is-open', open);
        document.body.style.overflow = open ? 'hidden' : '';
    }
    if (openBtn) openBtn.addEventListener('click', function () { toggleDrawer(true); });
    if (closeBtn) closeBtn.addEventListener('click', function () { toggleDrawer(false); });
    if (scrim) scrim.addEventListener('click', function () { toggleDrawer(false); });

    // ---- Flash messages: auto-dismiss ----
    document.querySelectorAll('.flash').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 300ms ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 320);
        }, 4200);
    });

    // ---- Newsletter form (AJAX) ----
    var newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(newsletterForm);
            var statusEl = newsletterForm.querySelector('.newsletter-status');
            fetch(newsletterForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (statusEl) {
                        statusEl.textContent = data.message;
                        statusEl.style.color = data.success ? 'var(--success)' : 'var(--danger)';
                    }
                    if (data.success) newsletterForm.reset();
                })
                .catch(function () {
                    if (statusEl) statusEl.textContent = 'Something went wrong. Please try again.';
                });
        });
    }
})();
