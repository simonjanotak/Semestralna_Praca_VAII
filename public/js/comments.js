(function(){
    'use strict';

    // --- URL endpointy pre AJAX operácie ---
    var LIST = window.COMMENT_URL_LIST || '/?c=comment&a=list';       // načítanie zoznamu komentárov
    var CREATE = window.COMMENT_URL_CREATE || '/?c=comment&a=create'; // pridanie nového komentára
    var DELETE = window.COMMENT_URL_DELETE || '/?c=comment&a=delete'; // zmazanie komentára
    var EDIT = window.COMMENT_URL_EDIT || '/?c=comment&a=edit';       // úprava komentára

    // --- Pomocné funkcie ---
    function qs(s,p){ return (p||document).querySelector(s) } // skrátené querySelector
    function qsa(s,p){ return Array.prototype.slice.call((p||document).querySelectorAll(s)) } // querySelectorAll do poľa
    function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') }
    // escapuje HTML špeciálne znaky, aby sme predišli XSS

    // --- Funkcia na vytvorenie HTML elementu komentára ---
    function renderComment(c){
        var d = document.createElement('div');
        d.className = 'comment mb-2 p-2 border rounded'; // Bootstrap triedy
        d.setAttribute('data-id', c.id || '');

        // horný riadok: používateľ + čas
        var h = document.createElement('div');
        h.className = 'small text-muted mb-1';
        h.textContent = (c.user || 'Neznámy') + ' • ' + (c.created_at || '');

        // --- Pridanie linku "Upraviť", ak je povolené ---
        if (c.can_edit) {
            try {
                var editUrl = new URL(EDIT, window.location.href);
                editUrl.searchParams.set('id', c.id);
                var a = document.createElement('a');
                a.href = editUrl.toString();
                a.className = 'btn btn-sm btn-outline-primary comment-edit ms-2';
                a.textContent = 'Upraviť';
                h.appendChild(a);
            } catch (err) {
                // ignoruj chyby
            }
        }

        // --- Pridanie tlačidla "Zmazať", ak je povolené ---
        if (c.can_delete) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-danger comment-delete';
            btn.style.marginLeft = '8px';
            btn.textContent = 'Zmazať';
            btn.setAttribute('data-id', c.id || '');
            h.appendChild(btn); // tlačidlo pridáme do horného riadku
        }

        // telo komentára
        var b = document.createElement('div');
        b.className = 'comment-body';
        b.innerHTML = esc(c.content).replace(/\n/g,'<br>'); // escapovanie + newline na <br>

        d.appendChild(h);
        d.appendChild(b);

        return d; // vráti celý element komentára
    }

    // --- Načítanie komentárov pre konkrétny post ---
    async function loadFor(postId){
        var container = qs('#comments-list-'+postId); // container, kam ideme vložiť komentáre
        if(!container) return;

        container.innerHTML = '<p class="text-muted">Načítavam komentáre...</p>';

        try {
            // vytvorenie URL endpointu
            var endpoint;
            try {
                endpoint = new URL(LIST, window.location.href);
            } catch (err) {
                endpoint = new URL(window.location.origin + LIST);
            }
            endpoint.searchParams.set('post_id', postId);

            // fetch request na získanie komentárov
            var res = await fetch(endpoint.toString(), {
                headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                credentials:'same-origin'
            });

            if(!res.ok){
                container.innerHTML='<p class="text-muted text-danger">Chyba pri načítaní komentárov.</p>';
                return;
            }

            var ct = (res.headers.get('content-type')||'').toLowerCase();
            if(ct.indexOf('application/json')===-1){
                container.innerHTML='<p class="text-muted text-danger">Nepodarilo sa načítať komentáre.</p>';
                return;
            }

            var json = await res.json();

            // kontrola, či server poslal chybu
            if(json && typeof json === 'object' && !Array.isArray(json) && json.error){
                container.innerHTML = '<p class="text-muted text-danger">Chyba servera: '+json.error+'</p>';
                return;
            }

            container.innerHTML=''; // vymažeme načítavací text
            if(!Array.isArray(json)||json.length===0){
                container.innerHTML='<p class="text-muted">Žiadne komentáre.</p>';
                return;
            }

            // render každého komentára
            json.forEach(function(c){ container.appendChild(renderComment(c)); });

        } catch(e){
            container.innerHTML='<p class="text-muted text-danger">Chyba pri načítaní komentárov.</p>';
        }
    }

    // --- Odosielanie formulára komentára cez AJAX ---
    document.addEventListener('submit', async function(e){
        var form = e.target;
        if (!form || !form.classList || !form.classList.contains('comment-form')) return;
        e.preventDefault(); // zabráni reloadu stránky

        try{
            var postId = form.querySelector('input[name="post_id"]')?.value;
            var fd = new FormData(form);

            // pridanie CSRF tokenu, ak je nastavený
            try {
                if (typeof window !== 'undefined' && window.CSRF_TOKEN && !fd.has('csrf_token'))
                    fd.append('csrf_token', window.CSRF_TOKEN);
            } catch(e){}

            var resp = await fetch(CREATE, {
                method:'POST',
                body: fd,
                credentials:'same-origin',
                headers:{'X-Requested-With':'XMLHttpRequest'}
            });

            if (resp.status === 401) {
                alert('Pre pridanie komentára sa musíš prihlásiť.');
                return;
            }

            var ct = (resp.headers.get('content-type')||'').toLowerCase();
            if (ct.indexOf('application/json') === -1) {
                const txt = await resp.text();
                alert('Server odpovedal neočakávanou odpoveďou: '+txt);
                return;
            }

            var json = await resp.json();
            if (json && json.error) { alert(json.error); return; }

            // ak sa vrátil nový komentár
            if (json && json.id) {
                var list = qs('#comments-list-'+postId);
                if (list) list.insertBefore(renderComment(json), list.firstChild); // vložíme hore
                form.reset(); // vyprázdnime formulár
            }

        } catch(err){
            alert('Chyba pri odoslaní komentára.');
        }
    }, false);

    // --- Odstránenie komentára cez AJAX ---
    document.addEventListener('click', async function(e){
        var target = e.target;
        if (!target || !target.classList) return;
        if (!target.classList.contains('comment-delete')) return;

        var id = target.getAttribute('data-id') || target.dataset.id;
        if (!id) return;
        if (!confirm('Naozaj zmazať tento komentár?')) return; // potvrdenie

        try {
            var endpoint;
            try { endpoint = new URL(DELETE, window.location.href); } catch (err) { endpoint = new URL(window.location.origin + DELETE); }

            var fd = new FormData();
            fd.append('id', id);

            // pridanie CSRF tokenu pre delete
            try { if (typeof window !== 'undefined' && window.CSRF_TOKEN) fd.append('csrf_token', window.CSRF_TOKEN); } catch(e){}

            var resp = await fetch(endpoint.toString(), {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: {'X-Requested-With':'XMLHttpRequest'}
            });

            if (!resp.ok) {
                const txt = await resp.text();
                alert('Chyba pri mazaní komentára: '+txt);
                return;
            }

            var json = await resp.json();
            if (json && json.error) { alert(json.error); return; }
            if (json && json.ok) {
                var commentEl = target.closest('.comment');
                if (commentEl) commentEl.remove(); // odstráni element zo stránky
            }
        } catch (err) {
            alert('Chyba pri mazaní komentára.');
        }
    }, false);

    // --- Po načítaní stránky, načítanie všetkých komentárov ---
    document.addEventListener('DOMContentLoaded', function(){
        qsa('.comments').forEach(function(b){
            var id = b.getAttribute('data-post-id');
            if(id) loadFor(id); // načítanie komentárov pre každý post
        });
    });

})();
