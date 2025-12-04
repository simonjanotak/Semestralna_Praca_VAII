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

        <button class="btn btn-orange" type="button" data-bs-toggle="modal" data-bs-target="#addPostModal">
            + Pridať príspevok
        </button>
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
<div class="modal fade" id="addPostModal" tabindex="-1" aria-labelledby="addPostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= $link->url('home.post.create') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPostModalLabel">Nový príspevok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavrieť"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="post-title" class="form-label">Názov</label>
                        <input id="post-title" name="title" type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="post-category" class="form-label">Kategória</label>
                        <select id="post-category" name="category" class="form-select" required>
                            <option value="">Vybrať kategóriu</option>
                            <option value="tech">Technické problémy</option>
                            <option value="autoservisy">Autoservisy</option>
                            <option value="tuning">Tuning a modifikácie</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="post-content" class="form-label">Text</label>
                        <textarea id="post-content" name="content" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Zavrieť</button>
                        <button type="submit" class="btn btn-orange">Vytvoriť príspevok</button>
                    </div>
            </form>
        </div>
    </div>
</div>

