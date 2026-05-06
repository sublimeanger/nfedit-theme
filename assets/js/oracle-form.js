(function () {
    const article = document.querySelector('.nfedit-oracle');
    if (!article) return;

    const form    = article.querySelector('[data-nfedit-oracle-form]');
    const status  = article.querySelector('[data-oracle-status]');
    const chips   = article.querySelectorAll('[data-chip]');
    const textarea = form ? form.querySelector('textarea[name=query]') : null;
    const isComingSoon = article.getAttribute('data-coming-soon') === '1';

    // Chip click → fill textarea
    chips.forEach(c => c.addEventListener('click', () => {
        if (textarea) {
            textarea.value = c.getAttribute('data-chip') || '';
            textarea.focus();
            textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }));

    if (!form) return;

    const setStatus = (msg, isError) => {
        if (!status) return;
        status.textContent = msg;
        status.style.color = isError ? 'hsl(var(--ink))' : 'hsl(var(--moss))';
        status.removeAttribute('hidden');
    };

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const submit = form.querySelector('button[type=submit]');
        const query  = (textarea && textarea.value.trim()) || '';
        if (!query) return;

        if (isComingSoon) {
            // Phase 10A behaviour — collect optional notify email, show success
            const emailInput = form.querySelector('input[name=notify_email]');
            const email = emailInput ? emailInput.value.trim() : '';

            if (submit) { submit.disabled = true; submit.textContent = 'Saving…'; }

            try {
                if (email) {
                    await fetch('/wp-json/nfedit/v1/oracle-notify', {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body:    JSON.stringify({ email, query }),
                    });
                }
            } catch (err) {
                // Non-blocking — still show success
            }

            form.style.display = 'none';
            setStatus("Thanks — we'll let you know when the Oracle is live. Until then, browse cottages or ask via the contact page.", false);
            return;
        }

        // Phase 11 behaviour will replace this branch with real Oracle backend POST.
        setStatus("The Oracle isn't live yet — we'll launch in Phase 11.", true);
    });
})();
