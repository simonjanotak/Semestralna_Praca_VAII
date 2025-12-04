<?php
/** @var Framework\Support\LinkGenerator $link */
?>
<header>
    <link rel="stylesheet" href="<?= $link->asset('css/stylForum.css') ?>">
<div class="container mt-4">
    <h2>Nový príspevok</h2>
    <form method="post" action="<?= $link->url('home.post.create') ?>" enctype="multipart/form-data">
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

        <div class="mb-3">
            <label for="post-picture" class="form-label">Obrázok (voliteľné)</label>
            <input id="post-picture" name="picture" type="file" class="form-control">
        </div>

        <button type="submit" class="btn btn-orange">Vytvoriť príspevok</button>
        <a href="<?= $link->url('home.index') ?>" class="btn btn-outline-secondary">Zrušiť</a>
    </form>
</div>
