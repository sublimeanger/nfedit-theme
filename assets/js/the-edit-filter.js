/* nfedit The Edit index — cluster filter strip (in-page item show/hide + URL hash sync) */

(function () {
    var filterStrip = document.querySelector('[data-the-edit-filters]');
    var grid        = document.querySelector('[data-the-edit-grid]');
    if (!filterStrip || !grid) return;

    var buttons   = filterStrip.querySelectorAll('[data-cluster-filter]');
    var items     = grid.querySelectorAll('[data-grid-item-cluster]');
    var noResults = grid.querySelector('[data-no-results]');
    var countEl   = grid.querySelector('[data-grid-count]');
    var countNum  = grid.querySelector('[data-count-num]');
    var countSfx  = grid.querySelector('[data-count-suffix]');
    var countLab  = grid.querySelector('[data-count-label]');

    function apply(slug) {
        var visible = 0;
        items.forEach(function (it) {
            var c = it.getAttribute('data-grid-item-cluster');
            var show = (slug === 'all' || c === slug);
            it.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        buttons.forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-cluster-filter') === slug);
        });

        if (slug !== 'all' && countEl) {
            var labelEl = filterStrip.querySelector('[data-cluster-filter="' + slug + '"]');
            var label = labelEl ? labelEl.textContent.trim() : slug;
            if (countNum) countNum.textContent = String(visible);
            if (countSfx) countSfx.textContent = visible === 1 ? 'y' : 'ies';
            if (countLab) countLab.textContent = label.toLowerCase();
            countEl.removeAttribute('hidden');
        } else if (countEl) {
            countEl.setAttribute('hidden', '');
        }

        if (noResults) {
            if (visible === 0) noResults.removeAttribute('hidden');
            else noResults.setAttribute('hidden', '');
        }

        if (slug !== 'all') {
            history.replaceState(null, '', '#' + slug);
        } else {
            history.replaceState(null, '', window.location.pathname);
        }
    }

    buttons.forEach(function (b) {
        b.addEventListener('click', function () {
            apply(b.getAttribute('data-cluster-filter'));
        });
    });

    var initial = (window.location.hash || '').replace('#', '');
    if (initial && filterStrip.querySelector('[data-cluster-filter="' + initial + '"]')) {
        apply(initial);
    }
})();
