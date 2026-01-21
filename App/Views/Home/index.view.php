<?php

/** @var \Framework\Support\LinkGenerator $link */
?>


<!-- AI responzivny dizajn domovska stranka -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="badge badge-like mb-3">Najväčšia slovenská auto komunita</span>
                <h1 class="fw-bold">PitStop.sk</h1>
                <p class="lead">Miesto, kde sa stretávajú automobiloví nadšenci</p>
                <p class="mb-4">Zdieľajte skúsenosti, riešte technické problémy, hodnotte servisy a objavujte svet áut.</p>

                <div class="hero-actions d-flex flex-wrap align-items-center">
                    <a class="btn btn-dark btn-lg me-2 my-2" href="<?= $link->url('home.forum') ?>">Vstúpiť do fóra</a>
                    <a class="btn btn-outline-light btn-lg my-2" href="<?= $link->url('auth.register') ?>">Registrovať sa</a>
                    <a class="btn btn-outline-light btn-lg ms-2 my-2" href="<?= $link->url('auth.login') ?>">Prihlásiť sa</a>
                    <a class="btn btn-outline-light btn-lg ms-2 my-2" href="<?= $link->url('home.contact') ?>">Kontakt</a>
                    <a class="btn btn-outline-light btn-lg ms-2 my-2" href="<?= $link->url('user.index') ?>">Užívatelia</a>
                    <a id="settingsLink" class="btn btn-outline-light btn-lg ms-2 my-2" href="<?= $link->url('home.carTests') ?>">Testy aút</a>
                </div>

                <div class="d-flex gap-4 mt-4 stats-box">
                    <div><span>2,500+</span><br>Členov</div>
                    <div><span>15,000+</span><br>Príspevkov</div>
                    <div><span>50,000+</span><br>Komentárov</div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= $link->asset('images/porsak2.png') ?>" alt="auto" class="hero-image">
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4"><div class="feature-box"><h5>Technická podpora</h5><p>Riešte technické problémy s pomocou komunity.</p></div></div>
            <div class="col-md-4"><div class="feature-box"><h5>Živá diskusia</h5><p>Zdieľajte skúsenosti a rady.</p></div></div>
            <div class="col-md-4"><div class="feature-box"><h5>Silná komunita</h5><p>Spájame automobilových nadšencov.</p></div></div>
            <div class="col-md-4"><div class="feature-box"><h5>Hodnotenia servisov</h5><p>Nájdite overené autoservisy.</p></div></div>
            <div class="col-md-4"><div class="feature-box"><h5>Značky a modely</h5><p>Diskutujte o autách a modeloch.</p></div></div>
            <div class="col-md-4"><div class="feature-box"><h5>Bezpečné prostredie</h5><p>Pravidlá pre férovú diskusiu.</p></div></div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container text-center">
        <span class="badge bg-danger mb-3">Kategórie</span>
        <h2 class="fw-bold">Preskúmajte témy</h2>
        <p class="text-muted mb-5">Rozdelené pre jednoduchú orientáciu</p>

        <div class="row g-4">

            <div class="col-md-4"><div class="category-box"><h5>Technické problémy</h5><p class="text-muted">245 príspevkov</p></div></div>
            <div class="col-md-4"><div class="category-box"><h5>Autoservisy</h5><p class="text-muted">178 príspevkov</p></div></div>
            <div class="col-md-4"><div class="category-box"><h5>Tuning a modifikácie</h5><p class="text-muted">312 príspevkov</p></div></div>

        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
