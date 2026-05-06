(function () {
    const tocList = document.querySelector('[data-legal-toc]');
    if (!tocList) return;

    const tocLinks = tocList.querySelectorAll('[data-toc-target]');
    const targets = [];
    tocLinks.forEach(link => {
        const id = link.getAttribute('data-toc-target');
        const el = document.getElementById(id);
        if (el) targets.push({ id, el, link, item: link.closest('.nfedit-legal-toc__item') });
    });
    if (!targets.length) return;

    const setActive = (id) => {
        targets.forEach(t => {
            if (t.item) t.item.classList.toggle('is-active', t.id === id);
        });
    };

    setActive(targets[0].id);

    const observer = new IntersectionObserver((entries) => {
        let topMost = null;
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                if (!topMost || entry.boundingClientRect.top < topMost.boundingClientRect.top) {
                    topMost = entry;
                }
            }
        });
        if (topMost) setActive(topMost.target.id);
    }, {
        rootMargin: '-100px 0px -50% 0px',
        threshold: 0,
    });

    targets.forEach(t => observer.observe(t.el));

    tocLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const id = link.getAttribute('data-toc-target');
            const el = document.getElementById(id);
            if (el) {
                e.preventDefault();
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', '#' + id);
            }
        });
    });
})();
