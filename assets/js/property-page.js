/* nfedit Property Page — sub-nav scroll-spy, sticky mobile CTA, banner dismissal, FAQ toggle */

(function () {
    // ─── Banner dismissal (cookie, 7d TTL) ───
    var banner = document.querySelector('[data-nfedit-offer-banner]');
    if (banner) {
        var dismissed = document.cookie.split('; ').some(function (c) { return c.indexOf('nfedit_offer_dismissed=') === 0; });
        if (dismissed) {
            banner.classList.add('is-dismissed');
        } else {
            var btn = banner.querySelector('[data-nfedit-offer-dismiss]');
            if (btn) {
                btn.addEventListener('click', function () {
                    banner.classList.add('is-dismissed');
                    document.cookie = 'nfedit_offer_dismissed=1; max-age=604800; path=/; samesite=lax';
                });
            }
        }
    }

    // ─── Sub-nav active section spy ───
    var subnavLinks = document.querySelectorAll('[data-subnav-link]');
    if (subnavLinks.length) {
        function onScrollSpy() {
            var current = subnavLinks[0].getAttribute('data-subnav-link');
            for (var i = 0; i < subnavLinks.length; i++) {
                var id = subnavLinks[i].getAttribute('data-subnav-link');
                var el = document.getElementById(id);
                if (el && el.getBoundingClientRect().top < 200) current = id;
            }
            subnavLinks.forEach(function (a) {
                a.classList.toggle('is-active', a.getAttribute('data-subnav-link') === current);
            });
        }
        window.addEventListener('scroll', onScrollSpy, { passive: true });
        onScrollSpy();
    }

    // ─── Sticky mobile CTA — visible past hero ───
    var cta = document.querySelector('[data-property-sticky-cta]');
    var hero = document.querySelector('[data-property-hero]');
    if (cta && hero) {
        cta.removeAttribute('hidden');
        function onScrollCTA() {
            var heroBottom = hero.offsetTop + hero.offsetHeight;
            cta.classList.toggle('is-visible', window.scrollY > heroBottom);
        }
        window.addEventListener('scroll', onScrollCTA, { passive: true });
        onScrollCTA();
    }

    // ─── FAQ expand/collapse ───
    document.querySelectorAll('[data-nfedit-faq]').forEach(function (faq) {
        var qBtn = faq.querySelector('.nfedit-faq__q');
        if (!qBtn) return;
        qBtn.addEventListener('click', function () {
            var isOpen = faq.classList.toggle('is-open');
            qBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    // ─── Share button ───
    var shareBtns = document.querySelectorAll('[data-nfedit-share]');
    shareBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var data = { title: document.title, url: window.location.href };
            if (navigator.share) {
                navigator.share(data).catch(function () { /* user cancelled */ });
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href).then(function () {
                    btn.setAttribute('aria-label', 'Link copied');
                });
            }
        });
    });
})();
