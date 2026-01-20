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
    const categoryLinks = document.querySelectorAll('.list-group-item[data-category]');

    if (!container) {
      return;
    }

    // Save initial server-rendered markup so we can restore it
    const initialMarkup = container.innerHTML || '';

    // Determine active category from HTML (fallback to 'all')
    var activeLink = document.querySelector('.list-group-item[data-category].active');
    var activeCategory = (activeLink && activeLink.getAttribute('data-category')) ? activeLink.getAttribute('data-category') : 'all';

    if (info) info.textContent = 'Zobrazené všetky príspevky';

    const DEBOUNCE_MS = 300;

    function debounce(fn, wait) {
      var t = null;
      return function () {
        var args = arguments;
        clearTimeout(t);
        t = setTimeout(function () { fn.apply(null, args); }, wait);
      };
    }

    // escape html
    function escapeHtml(s) {
      return String(s || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    // helper to normalize category strings for comparison
    function norm(s) {
      try { return String(s || '').trim().toLowerCase(); } catch (e) { return ''; }
    }

    // render posts received from server; include data-category attribute
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
      applyCategoryFilter();
    }

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

    // Apply category filter to current DOM articles (queries every time so it works after AJAX)
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

    var debounced = debounce(function () {
      var q = (input && input.value || '').trim();
      if (!q) {
        container.innerHTML = initialMarkup;
        // restore activeCategory based on current active link (in case it changed)
        var aLink = document.querySelector('.list-group-item[data-category].active');
        activeCategory = (aLink && aLink.getAttribute('data-category')) ? aLink.getAttribute('data-category') : 'all';
        applyCategoryFilter();
        if (info) info.textContent = 'Zobrazené všetky príspevky';
        return;
      }
      searchQuery(q);
    }, DEBOUNCE_MS);

    if (input) {
      input.addEventListener('input', debounced);
    }

    // Category click handling
    if (categoryLinks && categoryLinks.length) {
      Array.prototype.forEach.call(categoryLinks, function (link) {
        link.addEventListener('click', function (e) {
          try { e.preventDefault(); } catch (e) {}
          // update active class
          Array.prototype.forEach.call(categoryLinks, function (l) { l.classList.remove('active'); });
          this.classList.add('active');
          // update activeCategory and filter
          activeCategory = this.getAttribute('data-category') || 'all';
          applyCategoryFilter();
        });
      });
    }

    // Initial application of category (in case a category is marked active in HTML)
    applyCategoryFilter();
  });

})();
