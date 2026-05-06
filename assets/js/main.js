// Newsletter form (placeholder — replace with real handler later)
document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('[data-nfedit-newsletter]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input  = form.querySelector('input[name="email"]');
            var button = form.querySelector('button');
            if (!input || !input.value) return;
            button.textContent = 'Thanks!';
            button.disabled = true;
            // TODO: POST to mailing-service endpoint
        });
    });
});
