(function(){
    const usernameInput = document.getElementById('username');
    const statusEl = document.getElementById('usernameStatus');
    if (!usernameInput || !statusEl) return;

    let timer = null;

    function clearStatus() {
        statusEl.textContent = '';
        statusEl.style.color = '';
    }

    async function checkUsername(q) {
        const url = window.location.origin + '/?c=auth&a=checkUsernameAvailability&q=' + encodeURIComponent(q);

        try {
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!resp.ok) { clearStatus(); return; }

            const json = await resp.json();
            if (json && typeof json.available !== 'undefined') {
                statusEl.textContent = json.message || (json.available ? 'Takéto meno ešte neexistuje ✔' : 'Používateľské meno už existuje ✖');
                statusEl.style.color = json.available ? 'green' : 'red';
            } else {
                clearStatus();
            }
        } catch (e) {
            clearStatus();
        }
    }
    usernameInput.addEventListener('input', function() {
        const q = this.value.trim();
        if (q.length < 2) {
            clearTimeout(timer);
            clearStatus();
            return;
        }
        clearTimeout(timer);
        timer = setTimeout(() => checkUsername(q), 300);
    });
})();