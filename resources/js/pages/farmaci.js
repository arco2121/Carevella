document.addEventListener('DOMContentLoaded', () => {

    document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-toggle-edit]');
        if (!btn) return;

        const id = btn.dataset.toggleEdit;
        const panel = document.getElementById('edit-' + id);
        if (!panel) return;

        panel.classList.toggle('open');

        if (panel.classList.contains('open')) {
            panel.querySelector('input')?.focus();
        }
    });

    document.body.addEventListener('submit', (e) => {
        const form = e.target.closest('form[data-confirm]');
        if (!form) return;

        if (!confirm(form.dataset.confirm)) {
            e.preventDefault();
        }
    });

    document.body.addEventListener('input', (e) => {
        if (e.target.classList.contains('farm-input--code')) {
            const input = e.target;
            const start = input.selectionStart;
            const end = input.selectionEnd;
            input.value = input.value.toUpperCase();
            input.setSelectionRange(start, end);
        }
    });

});
