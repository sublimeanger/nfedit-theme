(function () {
    const strip = document.querySelector('[data-guide-filters]');
    const grid  = document.querySelector('[data-guide-grid]');
    if (!strip || !grid) return;

    const buttons   = strip.querySelectorAll('[data-cat-filter]');
    const items     = grid.querySelectorAll('[data-guide-cat]');
    const noResults = grid.querySelector('[data-no-results]');
    const countEl   = grid.querySelector('[data-guide-count]');
    const countNum  = grid.querySelector('[data-count-num]');
    const countSfx  = grid.querySelector('[data-count-suffix]');
    const countLab  = grid.querySelector('[data-count-label]');

    const apply = (slug) => {
        let visible = 0;
        items.forEach(it => {
            const c = it.getAttribute('data-guide-cat');
            const show = (slug === 'all' || c === slug);
            it.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        buttons.forEach(b => b.classList.toggle('is-active', b.getAttribute('data-cat-filter') === slug));

        if (slug !== 'all' && countEl) {
            const labelEl = strip.querySelector(`[data-cat-filter="${slug}"]`);
            const label = labelEl ? labelEl.textContent.trim() : slug;
            countNum.textContent = visible;
            countSfx.textContent = visible === 1 ? '' : 's';
            countLab.textContent = label.toLowerCase();
            countEl.removeAttribute('hidden');
        } else if (countEl) {
            countEl.setAttribute('hidden', '');
        }

        if (noResults) {
            if (visible === 0) noResults.removeAttribute('hidden');
            else noResults.setAttribute('hidden', '');
        }

        if (slug !== 'all') history.replaceState(null, '', '#' + slug);
        else history.replaceState(null, '', window.location.pathname);
    };

    buttons.forEach(b => b.addEventListener('click', () => apply(b.getAttribute('data-cat-filter'))));

    const initial = (window.location.hash || '').replace('#', '');
    if (initial && strip.querySelector(`[data-cat-filter="${initial}"]`)) apply(initial);
})();
