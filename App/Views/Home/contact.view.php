<?php

/** @var \Framework\Support\LinkGenerator $link */
?>

<header>
    <link rel="stylesheet" href="<?= $link->asset('css/stylForum.css') ?>">
</header>

<div class="row mb-3">
    <div class="col-12">
        <!-- Nav pills act as a simple dynamic subpage controller (hash-based) -->
        <ul class="nav nav-pills" id="contact-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="tab-info" href="#info" role="tab" aria-controls="info" aria-selected="true" tabindex="0">Info</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-departments" href="#departments" role="tab" aria-controls="departments" aria-selected="false" tabindex="0">Departments</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-map" href="#map" role="tab" aria-controls="map" aria-selected="false" tabindex="0">Map</a>
            </li>
        </ul>
    </div>
</div>

<!-- Tab contents: only one visible at a time; we control via JS using hash -->
<div class="tab-content">
    <section id="panel-info" class="tab-pane active">
        <div class="row">
            <div class="col">
                <h3>Faculty of Management Science and Informatics</h3>
                <p>
                    <strong>Address</strong>: Univerzitná 8215/1, 010 26 Žilina, Slovakia<br>
                    <strong>Tel. number</strong>: +421/41 513 4121<br>
                    <strong>Email</strong>: <a href="mailto:info@fsv.si">info@fsv.si</a>
                </p>
                <p>
                    <strong>GPS</strong>: 49°12'6,4"N 18°45'42,6"E
                </p>
            </div>
        </div>
    </section>

    <section id="panel-departments" class="tab-pane" style="display:none;">
        <div class="row">
            <div class="col">
                <h4>Departments</h4>
                <p class="text-muted">Click a department to reveal contact details.</p>
                <div class="list-group" id="departments-list">
                    <button type="button" class="list-group-item list-group-item-action" data-contact="dean@fsv.si">Dean's Office</button>
                    <button type="button" class="list-group-item list-group-item-action" data-contact="admissions@fsv.si">Admissions</button>
                    <button type="button" class="list-group-item list-group-item-action" data-contact="support@fsv.si">Student Support</button>
                    <button type="button" class="list-group-item list-group-item-action" data-contact="research@fsv.si">Research Office</button>
                </div>

                <div id="department-contact" class="mt-3" style="display:none;">
                    <h6>Contact</h6>
                    <p id="department-contact-text"></p>
                </div>
            </div>
        </div>
    </section>

    <section id="panel-map" class="tab-pane" style="display:none;">
        <div class="row mt-1">
            <div class="col">
                <!-- lazy-load map: data-src contains URL, src will be set when user opens Map tab -->
                <iframe id="contact-map-iframe" data-src="https://www.openstreetmap.org/export/embed.html?bbox=18.747396469116214%2C49.193792384417996%2C18.776578903198246%2C49.210336337994846&amp;layer=mapnik&amp;marker=49.202065053033984%2C18.761987686157227" width="100%" height="300" src="" style="border:0;" loading="lazy" title="Map"></iframe>
            </div>
        </div>
    </section>
</div>

<div class="row mt-3">
    <div class="col">
        <a href="<?= $link->url("home.index") ?>">Back to main page</a>
    </div>
</div>

<script>
(function(){
    // Simple hash-based tab controller: show section corresponding to #hash
    const tabs = Array.from(document.querySelectorAll('#contact-tabs .nav-link'));
    const panels = {
        'info': document.getElementById('panel-info'),
        'departments': document.getElementById('panel-departments'),
        'map': document.getElementById('panel-map')
    };
    const mapFrame = document.getElementById('contact-map-iframe');

    function setMapSrcIfNeeded() {
        if (!mapFrame) return;
        const ds = mapFrame.getAttribute('data-src') || '';
        if (ds && (!mapFrame.getAttribute('src') || mapFrame.getAttribute('src') === '')) {
            mapFrame.setAttribute('src', ds);
        }
    }

    function activateTab(name) {
        // activate nav links
        tabs.forEach(a => {
            if (a.getAttribute('href') === '#' + name) {
                a.classList.add('active');
                a.setAttribute('aria-selected', 'true');
                a.setAttribute('tabindex', '0');
                a.focus && a.focus();
            } else {
                a.classList.remove('active');
                a.setAttribute('aria-selected', 'false');
                a.setAttribute('tabindex', '-1');
            }
        });
        // show/hide panels
        Object.keys(panels).forEach(k => {
            const el = panels[k];
            if (!el) return;
            if (k === name) {
                el.style.display = '';
                el.classList.add('active');
            } else {
                el.style.display = 'none';
                el.classList.remove('active');
            }
        });

        // if user opened map, lazy-load it
        if (name === 'map') setMapSrcIfNeeded();
    }

    // click on nav: prevent default scroll and set hash
    tabs.forEach(a => {
        a.addEventListener('click', function(e){
            e.preventDefault();
            const target = this.getAttribute('href').slice(1);
            if (history && history.replaceState) {
                history.replaceState(null, '', '#' + target);
            } else {
                location.hash = target;
            }
            activateTab(target);
        });

        // keyboard navigation: left/right arrows switch tabs, Enter/Space activate
        a.addEventListener('keydown', function(e){
            const idx = tabs.indexOf(this);
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                const next = tabs[(idx + 1) % tabs.length];
                next.focus();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                const prev = tabs[(idx - 1 + tabs.length) % tabs.length];
                prev.focus();
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // handle direct links with hash and back/forward
    function initFromHash(){
        const h = (location.hash || '').replace('#','');
        if (h && panels[h]) {
            activateTab(h);
        } else {
            activateTab('info');
        }
    }
    window.addEventListener('hashchange', initFromHash);
    document.addEventListener('DOMContentLoaded', function(){
        initFromHash();

        // departments list behaviour: show contact inside panel
        const deptList = document.getElementById('departments-list');
        const contactBox = document.getElementById('department-contact');
        const contactText = document.getElementById('department-contact-text');
        if (deptList) {
            deptList.addEventListener('click', function(e){
                const btn = e.target.closest('button');
                if (!btn) return;
                const contact = btn.getAttribute('data-contact') || '';
                contactText.innerHTML = 'Email: <a href="mailto:'+ contact + '">'+ contact + '</a>';
                contactBox.style.display = '';
            });
        }
    });
})();
</script>
