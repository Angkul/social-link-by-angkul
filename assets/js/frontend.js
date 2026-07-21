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

    /* Match the button's open width to the menu's width (capped at 280px) —
       CSS alone can't size one box to match a sibling with different
       content, so measure .appt-fab__menu and expose it as a custom
       property that .appt-fab__main reads. The menu isn't display:none
       (only opacity:0), so its layout size is measurable even before the
       first open.

       Guarded with a floor: if the menu items are short (e.g. a phone number
       label) but the button's own title/subtitle text is longer, matching
       the menu's width exactly would clip the button's text. So the width is
       whichever is bigger — menu width or the button's own required width —
       capped at 280px either way. */
    var menu = fab.querySelector('.appt-fab__menu');
    var txt  = fab.querySelector('.appt-fab__txt');
    function syncMainWidth() {
        if (!menu) return;
        var ownNeed = 0;
        if (txt) {
            // 24 = txt's padding-left, 12 = flex gap, 22 = close icon,
            // 18 = close icon's right margin — the chrome around the text
            // that isn't part of txt.scrollWidth itself.
            ownNeed = txt.scrollWidth + 24 + 12 + 22 + 18;
        }
        // offsetWidth, NOT scrollWidth — while the menu is closed its items
        // sit at translateX(20px), and scrollWidth counts that transform
        // overflow, inflating the measurement by 20px. offsetWidth is pure
        // layout geometry and ignores transforms entirely.
        var w = Math.min(280, Math.max(menu.offsetWidth, ownNeed));
        fab.style.setProperty('--sla-main-w', w + 'px');
    }
    syncMainWidth();
    /* Re-measure once webfonts finish loading — the initial measurement runs
       against fallback-font metrics, which can differ from the real font
       (e.g. Noto Sans Thai) by tens of pixels, leaving the button wider or
       narrower than the menu. */
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(syncMainWidth);
    }
    window.addEventListener('load', syncMainWidth);
    window.addEventListener('resize', syncMainWidth);

    /* toggle open/close */
    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        /* fresh measurement right before opening, so any late layout/font
           changes are always accounted for */
        if (!fab.classList.contains('is-open')) syncMainWidth();
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
