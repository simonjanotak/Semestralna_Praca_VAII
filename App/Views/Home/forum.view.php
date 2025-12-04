<?php /** @var \Framework\Support\LinkGenerator $link */ ?>

<header>
    <link rel="stylesheet" href="<?= $link->asset('css/stylForum.css') ?>">
</header>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Všetky príspevky</h5>

    <div>
        <a href="<?= $link->url('home.index') ?>" class="btn btn-outline-secondary me-2" title="Domov" aria-label="Domov">
            Domov
        </a>

        <a href="<?= $link->url("post.index") ?>"class="btn btn-orange" title="Pridaj prispevok" aria-label="Pridaj prispevok">
            + Pridať príspevok
        </a>
    </div>
</div>

<div class="row">
    <aside class="col-md-4 mb-4 sidebar">
        <div class="card card-orange shadow-sm">
            <div class="card-body p-2">
                <h6 class="mb-3">Kategórie</h6>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action active" href="#">Všetky príspevky <span class="badge badge-orange float-end">...</span></a>
                    <a class="list-group-item list-group-item-action" href="#"><span class="me-2">🔧</span>Technické problémy <span class="badge bg-light text-dark float-end">...</span></a>
                    <a class="list-group-item list-group-item-action" href="#"><span class="me-2">🛠️</span>Autoservisy <span class="badge bg-light text-dark float-end">...</span></a>
                    <a class="list-group-item list-group-item-action" href="#"><span class="me-2">⚙️</span>Tuning a modifikácie <span class="badge bg-light text-dark float-end">...</span></a>
                </div>
            </div>
        </div>
    </aside>
    <main class="col-md-8">
        <div class="card card-orange shadow-sm">
            <div class="card-body">

            </div>
        </div>
    </main>
</div>


