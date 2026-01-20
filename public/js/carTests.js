// carTests.js
// Handles toggling of car parameter buttons and displays selected parameters list.
(function () {
    'use strict';

    var details = {
        'Výkon': '309 kW (420 koní) pri 8 300 ot./min',
        'Zrýchlenie': '4,6 sekundy (manuál) / 4,5 sekundy (DCT)',
        'Hmotnosť': 'cca 1 655 kg',
        'Dĺžka': 'Dĺžka: 4,720 mm.',
        'Šírka': 'Šírka: 1,850 mm.',
        'Výbava': 'M podvozok, samosvorný diferenciál, M sedačky, karbonová strecha, iDrive, adaptívny podvozok EDC.'
    };

    function createParamCard(name) {
        var div = document.createElement('div');
        div.className = 'param-card';
        div.setAttribute('data-param', name);
        var h = document.createElement('strong');
        h.textContent = name;
        var p = document.createElement('div');
        p.className = 'small text-muted';
        p.textContent = details[name] || '';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-danger ms-2 float-end remove-param';
        btn.textContent = 'Odstrániť';
        div.appendChild(h);
        div.appendChild(btn);
        div.appendChild(p);
        return div;
    }

    function init() {
        var params = document.querySelectorAll('.param-btn');
        var selected = document.getElementById('selectedParams');
        var clearBtn = document.getElementById('clearParams');

        if (!params || !selected) return;

        function findCard(param) {
            return selected.querySelector('[data-param="' + CSS.escape(param) + '"]');
        }

        params.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var name = btn.getAttribute('data-param') || btn.textContent.trim();
                var existing = findCard(name);
                if (existing) {
                    existing.remove();
                    return;
                }
                // create and append
                var card = createParamCard(name);
                selected.appendChild(card);
                // scroll into view
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });

        // delegate remove clicks
        selected.addEventListener('click', function (e) {
            var t = e.target;
            if (t && t.classList && t.classList.contains('remove-param')) {
                var card = t.closest('.param-card');
                if (card) card.remove();
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                selected.innerHTML = '<p class="text-muted">Zatiaľ žiadne parametre.</p>';
            });
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();

