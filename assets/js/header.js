(function () {
    var header = document.querySelector('.nfedit-header');
    if (!header) return;

    function onScroll() {
        if (window.scrollY > 200) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var toggle = header.querySelector('.nfedit-header__mobile-toggle');
    var nav    = document.getElementById('nfedit-mobile-nav');
    var close  = nav ? nav.querySelector('.nfedit-mobile-nav__close') : null;

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.removeAttribute('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        });
    }

    if (close && nav) {
        close.addEventListener('click', function () {
            nav.setAttribute('hidden', '');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        });
    }

    // Close on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && nav && !nav.hasAttribute('hidden')) {
            nav.setAttribute('hidden', '');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });
})();
