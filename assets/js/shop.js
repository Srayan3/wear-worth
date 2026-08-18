/* Shop listing page: mobile filter drawer toggle + auto-submit on control change. */
(function () {
    'use strict';

    var sidebar = document.getElementById('shopSidebar');
    var openBtn = document.getElementById('filterToggle');
    var closeBtn = document.getElementById('shopSidebarClose');

    if (openBtn && sidebar) {
        openBtn.addEventListener('click', function () { sidebar.classList.add('is-open'); document.body.style.overflow = 'hidden'; });
    }
    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', function () { sidebar.classList.remove('is-open'); document.body.style.overflow = ''; });
    }

    var sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            sortSelect.closest('form').submit();
        });
    }

    document.querySelectorAll('[data-auto-submit]').forEach(function (el) {
        el.addEventListener('change', function () { el.closest('form').submit(); });
    });
})();
