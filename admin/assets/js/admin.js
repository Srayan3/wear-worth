/* Admin shell behavior: mobile sidebar, reusable tabs, reusable modals,
   and confirmation prompts before destructive actions. */
(function () {
    'use strict';

    // ---- Mobile sidebar ----
    var sidebar = document.getElementById('adminSidebar');
    var toggle = document.getElementById('adminSidebarToggle');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () { sidebar.classList.toggle('is-open'); });
    }

    // ---- Tabs (any .a-tabs__nav / .a-tabs__panel pair sharing a common ancestor with [data-tabs]) ----
    document.querySelectorAll('[data-tabs]').forEach(function (group) {
        var buttons = group.querySelectorAll('.a-tabs__nav button');
        var panels = group.querySelectorAll('.a-tabs__panel');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) { b.classList.remove('is-active'); });
                panels.forEach(function (p) { p.classList.remove('is-active'); });
                btn.classList.add('is-active');
                var panel = document.getElementById(btn.dataset.tab);
                if (panel) panel.classList.add('is-active');
            });
        });
    });

    // ---- Modals: [data-modal-open="modalId"] opens, [data-modal-close] closes ----
    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modal = document.getElementById(btn.dataset.modalOpen);
            if (modal) modal.classList.add('is-open');
        });
    });
    document.addEventListener('click', function (e) {
        var closeBtn = e.target.closest('[data-modal-close]');
        if (closeBtn) {
            var modal = closeBtn.closest('.a-modal');
            if (modal) modal.classList.remove('is-open');
        }
        if (e.target.classList.contains('a-modal__scrim')) {
            e.target.closest('.a-modal').classList.remove('is-open');
        }
    });

    // ---- Confirm before destructive actions: <form data-confirm="Are you sure?"> ----
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-confirm]');
        if (form && !window.confirm(form.dataset.confirm)) {
            e.preventDefault();
        }
    });

    // ---- Auto-dismiss flash messages ----
    document.querySelectorAll('.a-flash').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 300ms ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 320);
        }, 5000);
    });
})();
