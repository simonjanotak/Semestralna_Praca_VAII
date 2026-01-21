
(function () {
    'use strict';

    // --- Detaily parametrov auta ---
    var details = {
        'Výkon': '309 kW (420 koní) pri 8 300 ot./min',
        'Zrýchlenie': '4,6 sekundy (manuál) / 4,5 sekundy (DCT)',
        'Hmotnosť': 'cca 1 655 kg',
        'Dĺžka': 'Dĺžka: 4,720 mm.',
        'Šírka': 'Šírka: 1,850 mm.',
        'Výbava': 'M podvozok, samosvorný diferenciál, M sedačky, karbonová strecha, iDrive, adaptívny podvozok EDC.'
    };

    // AI  --- Funkcia na vytvorenie "karty" parametra ---
    function createParamCard(name) {
        var div = document.createElement('div');
        div.className = 'param-card'; // hlavný kontajner
        div.setAttribute('data-param', name); // identifikátor parametra

        // názov parametra
        var h = document.createElement('strong');
        h.textContent = name;

        // popis parametra (malý text)
        var p = document.createElement('div');
        p.className = 'small text-muted';
        p.textContent = details[name] || '';

        // tlačidlo na odstránenie
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-danger ms-2 float-end remove-param';
        btn.textContent = 'Odstrániť';

        // vložíme do divu: názov, tlačidlo, popis
        div.appendChild(h);
        div.appendChild(btn);
        div.appendChild(p);

        return div;
    }

    // --- Inicializácia skriptu ---
    function init() {
        var params = document.querySelectorAll('.param-btn'); // všetky tlačidlá parametrov
        var selected = document.getElementById('selectedParams'); // kontajner pre vybrané parametre
        var clearBtn = document.getElementById('clearParams'); // tlačidlo "vymazať všetko"

        if (!params || !selected) return;

        // AI pomocná funkcia: nájde existujúcu kartu podľa názvu parametra
        function findCard(param) {
            return selected.querySelector('[data-param="' + CSS.escape(param) + '"]');
        }

        // --- klik na jednotlivé tlačidlá parametrov ---
        params.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var name = btn.getAttribute('data-param') || btn.textContent.trim();
                var existing = findCard(name);

                if (existing) {
                    // ak už je karta zobrazená → odstránime ju (toggle)
                    existing.remove();
                    return;
                }

                // vytvorenie novej karty a vloženie do kontajnera
                var card = createParamCard(name);
                selected.appendChild(card);

                // scroll na novú kartu
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });

        // --- klik na tlačidlo odstrániť (delegácia na kontajner) ---
        selected.addEventListener('click', function (e) {
            var t = e.target;
            if (t && t.classList && t.classList.contains('remove-param')) {
                var card = t.closest('.param-card');
                if (card) card.remove();
            }
        });

        // --- tlačidlo vymazať všetko ---
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                selected.innerHTML = '<p class="text-muted">Zatiaľ žiadne parametre.</p>';
            });

        }
    }

    // --- spustenie init po načítaní DOM ---
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
