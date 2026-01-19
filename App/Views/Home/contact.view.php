<?php

/** @var \Framework\Support\LinkGenerator $link */
?>

<header>
    <link rel="stylesheet" href="<?= $link->asset('css/stylForum.css') ?>">
    <link rel="stylesheet" href="<?= $link->asset('css/contact.css') ?>">
</header>

<div class="row mb-3">
    <div class="col-12">
        <div class="contact-card">
            <div class="contact-wrap">
                <!-- Very small: headline + short intro + simple department buttons -->
                <section id="contact-info">
                    <h3 style="color:var(--brand-orange)">PitStop — svet automobilov</h3>
                    <p class="contact-meta">Máte otázku? Vyberte oddelenie nižšie pre rýchly kontakt alebo napíšte na <a href="mailto:info@pitstop.sk">info@pitstop.sk</a>.</p>

                    <h4 style="color:var(--brand-orange); margin-top:1rem;">Oddelenia</h4>
                    <p class="text-muted">Kliknite na oddelenie.</p>

                    <noscript>
                        <div class="alert alert-info">JavaScript je vypnutý — používajte uvedené e‑maily nižšie.</div>
                    </noscript>

                    <div class="list-group" id="departments-list">
                        <button type="button" class="list-group-item" data-contact="redakcia@pitstop.sk" data-phone="+421900111222">Redakcia</button>
                        <button type="button" class="list-group-item" data-contact="partneri@pitstop.sk" data-phone="+421905222333">Reklama & partnerstvá</button>
                        <button type="button" class="list-group-item" data-contact="podpora@pitstop.sk" data-phone="+421907333444">Technická podpora</button>
                        <button type="button" class="list-group-item" data-contact="udalosti@pitstop.sk" data-phone="+421908444555">Komunitné udalosti</button>
                    </div>

                    <div id="department-contact" class="mt-3" style="display:none;">
                        <h6>Kontakt</h6>
                        <p id="department-contact-text"></p>
                    </div>
                </section>

                <div class="row mt-3">
                    <div class="col">
                        <a class="btn btn-orange" href="<?= $link->url("home.index") ?>">Späť na hlavnú stránku</a>
                        <a class="btn btn-outline-orange ms-2" href="mailto:info@pitstop.sk">Poslať e-mail</a>
                    </div>
                </div>

            </div> <!-- .contact-wrap -->
        </div> <!-- .contact-card -->

        <div class="contact-hero mt-3" role="img" aria-label="Obrázok porsak">
            <div class="contact-hero-overlay"></div>
        </div>
    </div>
</div>

<script src="<?= $link->asset('js/contact.js', true) ?>"></script>
