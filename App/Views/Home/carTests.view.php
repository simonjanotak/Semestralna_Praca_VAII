<?php
/** @var \Framework\Support\LinkGenerator $link */
?>
<header>
    <link rel="stylesheet" href="<?= $link->asset('css/stylTests.css') ?>">
</header>
<div class="container car-tests my-4">
    <!-- Added Home button linking to home.index -->
    <div class="mb-3">
        <a href="<?= $link->url('home.index') ?>" class="btn btn-orange btn-large">Domov</a>
        <!-- Alternatively, use gray: <a href="<?= $link->url('home.index') ?>" class="btn btn-gray btn-large">Domov</a> -->
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm card-orange">
                <div class="card-body">
                    <h2 class="text-orange mb-2">Testy Áut — rýchly prehľad BMW e90 M3</h2>
                    <p class="text-muted">BMW M3 E90 je ikonický športový sedan vyrábaný v rokoch 2007 – 2013.
                        Ide o jedinú generáciu M3 so <strong>8-valcovým atmosférickým motorom</strong>,
                        ktorý je považovaný za jeden z najlepších motorov značky BMW.
                        Kombinuje vysoký výkon, presné riadenie a každodennú použiteľnosť.</p>
                    <img src="<?= $link->asset('images/m3kaBetter.png') ?>" alt="car" class="img-fluid rounded mb-3" style="max-height:280px; object-fit:cover;">

                    <div class="mb-3">
                        <h5 class="mb-2">Zaujímavosť</h5>
                        <p class="small text-muted">
                            Zaujímavosť: Motor S65 V8 má maximálne otáčky až 8 400 ot./min
                            a nevyužíva turbo – všetok výkon je čisto atmosférický.
                        </p>
                    </div>

                    <div>
                        <h5 class="mb-2">Vybrané parametre</h5>
                        <div id="selectedParams" class="d-flex flex-column gap-2">
                        </div>
                        <div class="mt-3">
                            <button id="clearParams" class="btn btn-outline-secondary btn-sm">Vymazať výber</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm card-orange">
                <div class="card-body">
                    <h5 class="mb-3 text-orange">Parametre auta</h5>
                    <div id="paramsList" class="d-flex flex-column gap-2">
                        <button data-param="Výkon" class="btn btn-orange param-btn">Výkon</button>
                        <button data-param="Zrýchlenie" class="btn btn-orange param-btn">Zrýchlenie (0-100)</button>
                        <button data-param="Hmotnosť" class="btn btn-orange param-btn">Hmotnosť</button>
                        <button data-param="Dĺžka" class="btn btn-orange param-btn">Dĺžka</button>
                        <button data-param="Šírka" class="btn btn-orange param-btn">Šírka</button>
                        <button data-param="Výbava" class="btn btn-orange param-btn">Výbava</button>
                    </div>

                    <hr class="my-3">

                    <div>
                        <h6 class="mb-2">Ako to funguje</h6>
                        <p class="small text-muted">Kliknutím na tlačidlo sa daný parameter zobrazí alebo skryje v zozname vľavo. Parametre sa zobrazujú pod sebou v poradí klikov.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= $link->asset('js/carTests.js') ?>?v=1"></script>
