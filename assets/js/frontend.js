(function () {
    var fab  = document.getElementById('apptFab');
    if (!fab) return;

    var mode = fab.getAttribute('data-mode');

    /* ── Direct mode: entrance animation only ── */
    if (mode === 'direct') {
        requestAnimationFrame(function () {
            fab.classList.add('sla-ready');
        });
        return;
    }

    /* ── Parent (FAB) mode ── */
    var toggle = document.getElementById('apptFabToggle');
    if (!toggle) return;

    /* toggle open/close */
    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = fab.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen);
    });

    /* click outside = close */
    document.addEventListener('click', function (e) {
        if (!fab.contains(e.target)) {
            fab.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    /* ESC = close */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            fab.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();
