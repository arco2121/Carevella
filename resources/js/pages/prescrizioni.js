document.addEventListener('DOMContentLoaded', () => {
    const patientSelect = document.getElementById('patient-select');
    if (patientSelect) {
        patientSelect.addEventListener('change', () => {
            document.getElementById('patient-form').submit();
        });
    }

    const clearForm = document.getElementById('clear-prescriptions-form');
    if (clearForm) {
        clearForm.addEventListener('submit', (e) => {
            if (!confirm('Cancellare tutte le prescrizioni di questo paziente?')) {
                e.preventDefault();
            }
        });
    }

    document.querySelectorAll('.med-select').forEach(sel => {
        sel.classList.toggle('has-value', sel.value !== '');
    });

    document.body.addEventListener('click', (e) => {
        if (e.target.matches('.add-slot-btn')) {
            const btn = e.target;
            const day = btn.getAttribute('data-day');
            const step = btn.getAttribute('data-step');

            const list = btn.previousElementSibling;
            const template = document.getElementById('med-slot-template');
            const clone = template.content.cloneNode(true);

            const select = clone.querySelector('select');
            const input = clone.querySelector('input');

            select.name = `schedule[${day}][${step}][medicines][]`;
            input.name = `schedule[${day}][${step}][amounts][]`;

            list.appendChild(clone);
            list.lastElementChild?.querySelector('select')?.focus();
        }

        if (e.target.matches('.slot-remove-btn')) {
            const btn = e.target;
            const row = btn.closest('.med-slot-row');
            const list = row.parentElement;

            if (list.querySelectorAll('.med-slot-row').length > 1) {
                row.remove();
            } else {
                const sel = row.querySelector('select');
                const inp = row.querySelector('input');
                if (sel) { sel.value = ''; sel.classList.remove('has-value'); }
                if (inp) inp.value = '1';
            }
        }
    });

    document.body.addEventListener('change', (e) => {
        if (e.target.matches('.med-select')) {
            e.target.classList.toggle('has-value', e.target.value !== '');
        }
    });
});
