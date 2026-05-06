/* nfedit Oracle widget — chip → input fill, focus expand, newsletter form placeholder */

(function () {
    var oracles = document.querySelectorAll('[data-nfedit-oracle]');
    oracles.forEach(function (o) {
        var textarea = o.querySelector('[data-oracle-textarea]');
        var chips    = o.querySelector('[data-oracle-chips]');
        var chipBtns = o.querySelectorAll('[data-oracle-chip]');

        if (!textarea) return;

        textarea.addEventListener('focus', function () {
            textarea.rows = 3;
            if (chips) chips.removeAttribute('hidden');
        });

        textarea.addEventListener('blur', function () {
            if (!textarea.value.trim()) {
                textarea.rows = 1;
            }
        });

        chipBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                textarea.value = btn.textContent.trim();
                textarea.focus();
            });
        });
    });

    // Newsletter form placeholder (homepage variant)
    var newsletter = document.querySelector('[data-nfedit-newsletter].nfedit-home-newsletter__form, .nfedit-home-newsletter__form[data-nfedit-newsletter]');
    if (newsletter) {
        newsletter.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = newsletter.querySelector('input[type=email]');
            if (input && input.value) {
                input.value = '';
                var note = document.createElement('p');
                note.className = 'nfedit-home-newsletter__success';
                note.textContent = 'Thanks — we\'ll be in touch.';
                note.style.cssText = 'color: hsl(var(--moss)); margin-top: 1rem; font-family: var(--font-body); font-size: 0.875rem;';
                newsletter.parentNode.insertBefore(note, newsletter.nextSibling);
                setTimeout(function () { note.remove(); }, 5000);
            }
        });
    }
})();
