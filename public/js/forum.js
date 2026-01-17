(function () {
  'use strict';

  const SEARCH_URL = (typeof window !== 'undefined' && window.SEARCH_URL) ?
    window.SEARCH_URL :
    (window.location.origin + '/?c=home&a=searchPosts');

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  onReady(function () {
    const input = document.getElementById('postSearch');
    const info = document.getElementById('postSearchInfo');
    const container = document.getElementById('postsContainer');

    if (!container) {
      console.warn('forum.js: #postsContainer not found, aborting search init');
      return;
    }

    // Save initial server-rendered markup so we can restore it
    const initialMarkup = container.innerHTML || '';

    if (info) info.textContent = 'Zobrazené všetky príspevky';

    const DEBOUNCE_MS = 300;

    function debounce(fn, wait) {
      let t = null;
      return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
      };
    }

    //ziadene html tagy iba text
    function escapeHtml(s) {
      return String(s || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    //htmlko pre nase prispevky
    function renderPosts(posts) {
      if (!Array.isArray(posts) || posts.length === 0) {
        container.innerHTML = '<p class="text-muted">Neboli nájdené žiadne príspevky.</p>';
        return;
      }
      const html = posts.map(p => {
        const title = escapeHtml(p.title || '');
        const content = escapeHtml(p.content || '');
        return (
          '<article class="mb-4 p-3 border rounded bg-white shadow-sm">' +
            '<h5 class="mb-1 text-orange">' + title + '</h5>' +
            '<div class="text-muted small mb-2">ID: ' + (p.id ?? '') + '</div>' +
            '<div>' + content.replace(/\n/g, '<br>') + '</div>' +
          '</article>'
        );
      }).join('\n');
      container.innerHTML = html;
    }

    async function searchQuery(q) {
      if (info) info.textContent = 'Hľadám...';
      try {
        const sep = SEARCH_URL.indexOf('?') !== -1 ? '&' : '?';
        const url = SEARCH_URL + sep + 'q=' + encodeURIComponent(q);
        const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!resp.ok) {
          if (info) info.textContent = 'Chyba servera: ' + resp.status;
          renderPosts([]);
          return;
        }
        const json = await resp.json();
        renderPosts(json);
        if (info) info.textContent = 'Zobrazené výsledky pre: "' + q + '"';
      } catch (err) {
        console.error('Search fetch error', err);
        if (info) info.textContent = 'Chyba sieťového spojenia';
        renderPosts([]);
      }
    }

    const debounced = debounce(function () {
      const q = (input && input.value || '').trim();
      if (!q) {
        container.innerHTML = initialMarkup;
        if (info) info.textContent = 'Zobrazené všetky príspevky';
        return;
      }
      searchQuery(q);
    }, DEBOUNCE_MS);

    if (input) {
      input.addEventListener('input', debounced);
    } else {
      console.warn('forum.js: #postSearch input not found');
    }
  });
})();

