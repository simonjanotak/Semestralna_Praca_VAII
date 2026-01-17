<?php /** @var \Framework\Support\LinkGenerator $link */ ?>

<header>
    <link rel="stylesheet" href="<?= $link->asset('css/stylForum.css') ?>">
</header>



<div class="row align-items-center mb-3">
    <div class="col-12 col-md-6">
        <h5 class="mb-0">Všetky príspevky</h5>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
        <!-- small-screen toggle to show/hide sidebar categories -->
        <button class="btn btn-outline-secondary d-md-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#forumSidebar" aria-expanded="false" aria-controls="forumSidebar">
            Kategórie
        </button>

        <a href="<?= $link->url('home.index') ?>" class="btn btn-outline-secondary me-2 d-none d-md-inline" title="Domov" aria-label="Domov">
            Domov
        </a>

        <a href="<?= $link->url('post.add') ?>" class="btn btn-orange" title="Pridaj prispevok" aria-label="Pridaj prispevok">
            + Pridať príspevok
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="collapse d-md-block" id="forumSidebar">
            <aside class="sidebar">
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
        </div>
    </div>
    <main class="col-md-8">
        <div class="card card-orange shadow-sm">
            <div class="card-body">

                <?php /** @var \App\Models\Post[] $posts */ ?>
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="mb-4 p-3 border rounded bg-white shadow-sm">
                            <div class="row g-2 align-items-start">
                                <div class="col-12 col-md">
                                    <h5 class="mb-1 text-orange"><?= htmlspecialchars($post->getTitle()) ?></h5>
                                </div>
                                <div class="col-12 col-md-auto text-md-end">
                                    <div class="btn-group btn-group-sm me-3" role="group" aria-label="Actions">
                                        <a href="<?= $link->url('post.edit', ['id' => $post->getId()]) ?>" class="btn btn-success me-1 rounded" title="Upraviť">Upraviť</a>
                                        <form method="post" action="<?= $link->url('post.delete') ?>" style="display:inline;margin:0;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string)$post->getId()) ?>">
                                            <button type="submit" class="btn btn-danger rounded" onclick="return confirm('Naozaj zmazať tento príspevok?');">Zmazať</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="text-muted small mb-2">
                                <?= htmlspecialchars($post->getCategory()) ?> • <?= htmlspecialchars($post->getCreatedAt() ? date('j.n.Y', strtotime($post->getCreatedAt())) : date('j.n.Y')) ?>
                                <?php
                                // Prefer using preloaded $userMap (avoids N+1). Fallback to $post->getUser().
                                $author = null;
                                if (isset($userMap) && is_array($userMap) && $post->getUserId() !== null) {
                                    $uid = $post->getUserId();
                                    $author = $userMap[$uid] ?? null;
                                }
                                if ($author === null) {
                                    try { $author = $post->getUser(); } catch (\Throwable $e) { $author = null; }
                                }
                                if ($author !== null) : ?>
                                    • Autor: <?= htmlspecialchars($author->getUsername()) ?>
                                <?php else: ?>
                                    • Autor: <span class="text-muted">Neznámy</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($post->getPicture()): ?>
                                <div class="mb-2">
                                    <img src="<?= htmlspecialchars($post->getPicture()) ?>" alt="" class="img-fluid">
                                </div>
                            <?php endif; ?>

                            <p><?= nl2br(htmlspecialchars($post->getContent())) ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Zatiaľ tu nie sú žiadne príspevky.</p>
                <?php endif; ?>

            </div>
        </div>
    </main>
</div>
