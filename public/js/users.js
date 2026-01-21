// AI copilot funkcia
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.delete-user');
    if (!btn) return;
    e.preventDefault();

    const id = btn.getAttribute('data-id');
    if (!id) return;

    if (!confirm('Naozaj chcete vymazať tohto používateľa? Táto akcia je nezvratná.')) return;

    // Read delete URL from container data attribute
    const root = document.getElementById('users-root');
    let url = (root && root.dataset && root.dataset.deleteUrl) ? root.dataset.deleteUrl : null;
    if (!url) {
        // fallback to router-style URL (c=user&a=delete)
        url = (window.location.pathname || '/') + '?c=user&a=delete';
    }

    const form = new FormData();
    form.append('id', id);

    fetch(url, {
        method: 'POST',
        body: form,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(r => r.json()).then(data => {
        if (data && data.success) {
            // remove row from table
            const tr = document.querySelector('tr[data-id="' + id + '"]');
            if (tr) tr.remove();
        } else {
            const msg = (data && data.error) ? data.error : (data && data.message) ? data.message : 'Chyba';
            alert('Nebolo možné odstrániť používateľa: ' + msg);
        }
    }).catch(function(){
        alert('Chyba pri kontakte so serverom.');
    });
});
