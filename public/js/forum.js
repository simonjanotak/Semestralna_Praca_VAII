(function () {
    'use strict';

    // Získanie URL pre AJAX vyhľadávanie príspevkov
    const SEARCH_URL = (typeof window !== 'undefined' && window.SEARCH_URL) ?
        window.SEARCH_URL :
        (window.location.origin + '/?c=home&a=searchPosts');

    //  AI Funkcia na spustenie callbacku až po načítaní DOM
    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    onReady(function () {
        // Elementy zo stránky
        const input = document.getElementById('postSearch'); // vyhľadávacie pole
        const info = document.getElementById('postSearchInfo'); // text informácie o výsledkoch
        const container = document.getElementById('postsContainer'); // kontajner pre príspevky
        const categoryLinks = document.querySelectorAll('.list-group-item[data-category]'); // odkazy kategórií

        if (!container) return;

        // Uložíme pôvodný HTML obsah, aby sme ho mohli obnoviť po vymazaní vyhľadávania
        const initialMarkup = container.innerHTML || '';

        // Zistí aktívnu kategóriu z HTML, ak nie je, predvolene "all"
        var activeLink = document.querySelector('.list-group-item[data-category].active');
        var activeCategory = (activeLink && activeLink.getAttribute('data-category')) ? activeLink.getAttribute('data-category') : 'all';

        if (info) info.textContent = 'Zobrazené všetky príspevky';

        const DEBOUNCE_MS = 300; // oneskorenie pri vyhľadávaní

        // Pomocná debounce funkcia, aby sme nevolali AJAX pri každom písaní
        function debounce(fn, wait) {
            var t = null;
            return function () {
                var args = arguments;
                clearTimeout(t);
                t = setTimeout(function () { fn.apply(null, args); }, wait);
            };
        }

        // Escapovanie HTML, aby sa nezobrazili škodlivé znaky
        function escapeHtml(s) {
            return String(s || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Normalizácia textu kategórie pre porovnanie
        function norm(s) {
            try { return String(s || '').trim().toLowerCase(); } catch (e) { return ''; }
        }

        //Funkcia na vykreslenie príspevkov do DOM po AJAX vyhľadávaní
        function renderPosts(posts) {
            if (!Array.isArray(posts) || posts.length === 0) {
                container.innerHTML = '<p class="text-muted">Neboli nájdené žiadne príspevky.</p>';
                return;
            }

            var html = posts.map(function (p) {
                var title = escapeHtml(p.title || '');
                var content = escapeHtml(p.content || '');
                var category = escapeHtml(p.category || '');
                return (
                    '<article class="mb-4 p-3 border rounded bg-white shadow-sm" data-category="' + category + '">' +
                    '<h5 class="mb-1 text-orange">' + title + '</h5>' +
                    '<div class="text-muted small mb-2">' + (p.created_at || '') + '</div>' +
                    '<div>' + content.replace(/\n/g, '<br>') + '</div>' +
                    '</article>'
                );
            }).join('\n');

            container.innerHTML = html;
            applyCategoryFilter(); // aplikujeme aktuálny filter kategórie
        }

        // ÚPRAVY OD AI Funkcia na vyhľadávanie príspevkov cez AJAX
        async function searchQuery(q) {
            if (info) info.textContent = 'Hľadám...';
            try {
                var sep = SEARCH_URL.indexOf('?') !== -1 ? '&' : '?';
                var url = SEARCH_URL + sep + 'q=' + encodeURIComponent(q);
                var resp = await fetch(url, { headers: { 'Accept': 'application/json' } });

                if (!resp.ok) {
                    if (info) info.textContent = 'Chyba servera: ' + resp.status;
                    renderPosts([]);
                    return;
                }

                var json = await resp.json();
                renderPosts(json);
                if (info) info.textContent = 'Zobrazené výsledky pre: "' + q + '"';
            } catch (err) {
                if (info) info.textContent = 'Chyba sieťového spojenia';
                renderPosts([]);
            }
        }

        // AI Funkcia na filtrovanie príspevkov podľa kategórie
        function applyCategoryFilter() {
            var articles = container.querySelectorAll('article');
            var target = norm(activeCategory);

            Array.prototype.forEach.call(articles, function (article) {
                var postCategory = norm((article.dataset && article.dataset.category) ? article.dataset.category : '');
                if (!target || target === 'all') {
                    article.style.display = '';
                } else {
                    article.style.display = (postCategory === target) ? '' : 'none';
                }
            });
        }

        // Debounce vyhľadávanie po písaní do inputu
        var debounced = debounce(function () {
            var q = (input && input.value || '').trim();

            if (!q) {
                // ak je prázdny input, obnovíme pôvodný HTML a filter
                container.innerHTML = initialMarkup;
                var aLink = document.querySelector('.list-group-item[data-category].active');
                activeCategory = (aLink && aLink.getAttribute('data-category')) ? aLink.getAttribute('data-category') : 'all';
                applyCategoryFilter();
                if (info) info.textContent = 'Zobrazené všetky príspevky';
                return;
            }

            // volanie AJAX vyhľadávania
            searchQuery(q);
        }, DEBOUNCE_MS);

        if (input) {
            input.addEventListener('input', debounced);
        }

        // Kliknutie na kategórie
        if (categoryLinks && categoryLinks.length) {
            Array.prototype.forEach.call(categoryLinks, function (link) {
                link.addEventListener('click', function (e) {
                    try { e.preventDefault(); } catch (e) {}
                    // zmena aktívnej kategórie vizuálne
                    Array.prototype.forEach.call(categoryLinks, function (l) { l.classList.remove('active'); });
                    this.classList.add('active');
                    // aktualizácia filteru
                    activeCategory = this.getAttribute('data-category') || 'all';
                    applyCategoryFilter();
                });
            });
        }

        // Inicialne aplikovanie filtra kategórie
        applyCategoryFilter();
    });

})();
