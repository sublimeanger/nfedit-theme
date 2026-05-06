(function () {
    const form = document.querySelector('[data-nfedit-contact-form]');
    if (!form) return;

    const status  = form.querySelector('[data-status]');
    const success = document.querySelector('[data-success]');
    const submit  = form.querySelector('button[type=submit]');

    const setStatus = (msg, isError) => {
        if (!status) return;
        status.textContent = msg;
        status.style.color = isError ? 'hsl(var(--ink))' : 'hsl(var(--moss))';
        status.removeAttribute('hidden');
    };

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Honeypot
        const hp = form.querySelector('input[name=website]');
        if (hp && hp.value) {
            setStatus("Submission blocked.", true);
            return;
        }

        const data = new FormData(form);
        const payload = {
            name:    data.get('name'),
            email:   data.get('email'),
            subject: data.get('subject'),
            message: data.get('message'),
        };

        if (submit) { submit.disabled = true; submit.textContent = 'Sending…'; }
        setStatus('', false);
        if (status) status.setAttribute('hidden', '');

        try {
            const res = await fetch('/wp-json/nfedit/v1/contact', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload),
            });
            const body = await res.json().catch(() => ({}));

            if (res.ok && body && body.ok) {
                form.style.display = 'none';
                if (success) success.removeAttribute('hidden');
                window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth' });
            } else {
                const msg = (body && body.error) ? body.error : 'Something went wrong. Please try again.';
                setStatus(msg, true);
                if (submit) { submit.disabled = false; submit.textContent = 'Send'; }
            }
        } catch (err) {
            setStatus("Network error. Please try again.", true);
            if (submit) { submit.disabled = false; submit.textContent = 'Send'; }
        }
    });
})();
