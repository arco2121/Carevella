const toggleEdit = (id) => {
    const panel = document.getElementById('edit-' + id);
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
        panel.querySelector('input').focus();
    }
};
