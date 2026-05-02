document.addEventListener('DOMContentLoaded', () => {

    document.body.addEventListener('click', e => {
        const btn = e.target.closest('[data-toggle-edit]');
        if (!btn) return;
        const panel = document.getElementById('edit-' + btn.dataset.toggleEdit);
        if (!panel) return;
        panel.classList.toggle('open');
        if (panel.classList.contains('open')) panel.querySelector('input')?.focus();
    });

    document.body.addEventListener('submit', e => {
        const form = e.target.closest('form[data-confirm]');
        if (form && !confirm(form.dataset.confirm)) e.preventDefault();
    });

    document.body.addEventListener('input', e => {
        const input = e.target;
        if (!input.classList.contains('farm-input--code')) return;
        const { selectionStart: s, selectionEnd: end } = input;
        input.value = input.value.toUpperCase();
        input.setSelectionRange(s, end);
    });

});
