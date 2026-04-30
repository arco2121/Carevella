const addSlot = (btn, day, step) => {
    const list = btn.previousElementSibling;
    const template = document.getElementById('med-slot-template');
    const clone = template.content.cloneNode(true);
    clone.querySelector('select').name = `schedule[${day}][${step}][]`;
    list.appendChild(clone);
}

const removeSlot = (btn) => {
    const row = btn.closest('.med-slot-row');
    const list = row.parentElement;
    if (list.querySelectorAll('.med-slot-row').length > 1) {
        row.remove();
    } else {
        const sel = row.querySelector('select');
        sel.value = '';
        sel.classList.remove('has-value');
    }
}
