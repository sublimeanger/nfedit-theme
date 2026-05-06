/* nfedit Gallery Lightbox */

(function () {
    var galleries = document.querySelectorAll('[data-nfedit-gallery]');
    if (!galleries.length) return;

    var lb = document.createElement('div');
    lb.className = 'nfedit-lightbox';
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.innerHTML = ''
        + '<button class="nfedit-lightbox__close" aria-label="Close">'
        +   '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>'
        + '</button>'
        + '<button class="nfedit-lightbox__nav nfedit-lightbox__nav--prev" aria-label="Previous">'
        +   '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>'
        + '</button>'
        + '<img class="nfedit-lightbox__image" src="" alt="" />'
        + '<button class="nfedit-lightbox__nav nfedit-lightbox__nav--next" aria-label="Next">'
        +   '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>'
        + '</button>'
        + '<p class="nfedit-lightbox__counter"></p>';
    document.body.appendChild(lb);

    var img      = lb.querySelector('.nfedit-lightbox__image');
    var counter  = lb.querySelector('.nfedit-lightbox__counter');
    var closeBtn = lb.querySelector('.nfedit-lightbox__close');
    var prevBtn  = lb.querySelector('.nfedit-lightbox__nav--prev');
    var nextBtn  = lb.querySelector('.nfedit-lightbox__nav--next');

    var currentImages = [];
    var currentIndex  = 0;

    function render() {
        if (!currentImages.length) return;
        img.src = currentImages[currentIndex];
        counter.textContent = (currentIndex + 1) + ' of ' + currentImages.length;
    }
    function open(images, idx) {
        currentImages = images;
        currentIndex  = idx;
        render();
        lb.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        lb.classList.remove('is-open');
        document.body.style.overflow = '';
    }
    function prev() {
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        render();
    }
    function next() {
        currentIndex = (currentIndex + 1) % currentImages.length;
        render();
    }

    closeBtn.addEventListener('click', close);
    prevBtn.addEventListener('click', prev);
    nextBtn.addEventListener('click', next);
    lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
    document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('is-open')) return;
        if (e.key === 'Escape')     close();
        if (e.key === 'ArrowLeft')  prev();
        if (e.key === 'ArrowRight') next();
    });

    galleries.forEach(function (g) {
        var images = [];
        try { images = JSON.parse(g.getAttribute('data-images') || '[]'); } catch (e) {}
        if (!images.length) return;

        g.querySelectorAll('[data-gallery-index]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx = parseInt(btn.getAttribute('data-gallery-index'), 10) || 0;
                open(images, idx);
            });
        });

        var overflow = g.parentElement && g.parentElement.querySelector('[data-gallery-overflow]');
        if (overflow) {
            overflow.addEventListener('click', function () { open(images, 0); });
        }
    });
})();
