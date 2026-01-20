<?php /** @var \Framework\Support\LinkGenerator $link */ ?>

<header>
    <!-- Pripojenie CSS pre fórum -->
    <link rel="stylesheet" href="<?= $link->asset('css/stylForum.css') ?>">
</header>

<?php
// Pomocná funkcia na vytvorenie "slug" kategórie (malé písmená, bezpečné pre HTML)
if (!function_exists('cat_slug')) {
    function cat_slug($s) {
        // trim: odstráni medzery na začiatku/konci
        // mb_strtolower: zmení na malé písmená UTF-8
        // htmlspecialchars: bezpečné pre HTML
        return htmlspecialchars(mb_strtolower(trim((string)$s), 'UTF-8'));
    }
}
//správy ked nieje prihláseny použivateľ
$flashError = null;
if (!empty($_SESSION['flash_message'])) {
    $flashError = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
} elseif (!empty($_SESSION['flash_error'])) {
    $flashError = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flashError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;
?>

<div class="row align-items-center mb-3">
    <div class="col-12 col-md-6">
        <!-- Nadpis sekcie príspevkov -->
        <h5 class="mb-0">Všetky príspevky</h5>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
        <!-- Tlačidlo pre zobrazenie/skrývanie bočného menu na malých obrazovkách -->
        <button class="btn btn-outline-secondary d-md-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#forumSidebar" aria-expanded="false" aria-controls="forumSidebar">
            Kategórie
        </button>

        <!-- Odkaz na domovskú stránku (iba pre desktop) -->
        <a href="<?= $link->url('home.index') ?>" class="btn btn-outline-secondary me-2 d-none d-md-inline" title="Domov" aria-label="Domov">
            Domov
        </a>

        <!-- Tlačidlo na pridanie príspevku -->
        <a href="<?= $link->url('post.add') ?>" class="btn btn-orange" title="Pridaj prispevok" aria-label="Pridaj prispevok">
            + Pridať príspevok
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <!-- Bočný panel / sidebar -->
        <div class="collapse d-md-block" id="forumSidebar">
            <aside class="sidebar">
                <div class="card card-orange shadow-sm">
                    <div class="card-body p-2">
                        <h6 class="mb-3">Kategórie</h6>
                        <div class="list-group">
                            <!-- Všetky príspevky -->
                            <a class="list-group-item list-group-item-action active" href="#" data-category="all">Všetky príspevky <span class="badge badge-orange float-end">...</span></a>
                            <!-- Jednotlivé kategórie s "slug" pre JS filter -->
                            <a class="list-group-item list-group-item-action" href="#" data-category="<?= cat_slug('tech') ?>"><span class="me-2">🔧</span>Technické problémy <span class="badge bg-light text-dark float-end">...</span></a>
                            <a class="list-group-item list-group-item-action" href="#" data-category="<?= cat_slug('Autoservisy') ?>"><span class="me-2">🛠️</span>Autoservisy <span class="badge bg-light text-dark float-end">...</span></a>
                            <a class="list-group-item list-group-item-action" href="#" data-category="<?= cat_slug('tuning') ?>"><span class="me-2">⚙️</span>Tuning a modifikácie <span class="badge bg-light text-dark float-end">...</span></a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <main class="col-md-8">
        <div class="card card-orange shadow-sm">
            <div class="card-body">

                <!-- Vyhľadávacie pole: filtruje príspevky podľa názvu cez AJAX -->
                <div class="mb-3">
                    <label for="postSearch" class="form-label">Hľadať podľa názvu:</label>
                    <input id="postSearch" class="form-control" type="search" placeholder="Zadajte hľadaný text..." aria-label="Hľadať podľa názvu">
                    <div id="postSearchInfo" class="form-text text-muted"></div>
                </div>

                <!-- Kontajner pre príspevky (renderované serverom) -->
                <div id="postsContainer">

                    <?php /** @var array[] $posts (id, title, content, category, created_at, picture, author) */ ?>
                    <?php if (!empty($posts) && is_array($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <!-- Každý príspevok s data-category a data-post-id pre JS filtrovanie -->
                            <article class="mb-4 p-3 border rounded bg-white shadow-sm" data-category="<?= htmlspecialchars($post['category']) ?>" data-post-id="<?= (int)$post['id'] ?>">
                                <div class="row g-2 align-items-start">
                                    <div class="col-12 col-md">
                                        <h5 class="mb-1 text-orange"><?= htmlspecialchars($post['title']) ?></h5>
                                    </div>
                                    <div class="col-12 col-md-auto text-md-end">
                                        <!-- Akcie: upraviť / zmazať -->
                                        <div class="btn-group btn-group-sm me-3" role="group" aria-label="Actions">
                                            <a href="<?= $link->url('post.edit', ['id' => $post['id']]) ?>" class="btn btn-success me-1 rounded" title="Upraviť">Upraviť</a>
                                            <form method="post" action="<?= $link->url('post.delete') ?>" style="display:inline;margin:0;">
                                                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                                <button type="submit" class="btn btn-danger rounded" onclick="return confirm('Naozaj zmazať tento príspevok?');">Zmazať</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informácie o kategórii, dátume a autorovi -->
                                <div class="text-muted small mb-2">
                                    <?= htmlspecialchars($post['category']) ?> • <?= htmlspecialchars($post['created_at'] ? date('j.n.Y', strtotime($post['created_at'])) : date('j.n.Y')) ?>
                                    • Autor: <?= htmlspecialchars($post['author'] ?? 'Neznámy') ?>
                                </div>

                                <!-- Obrázok príspevku, ak existuje -->
                                <?php if (!empty($post['picture'])): ?>
                                    <div class="mb-2">
                                        <img src="<?= htmlspecialchars($post['picture']) ?>" alt="" class="img-fluid">
                                    </div>
                                <?php endif; ?>

                                <!-- Obsah príspevku -->
                                <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                                <!-- Komentáre k príspevku -->
                                <div class="comments mt-3" data-post-id="<?= (int)$post['id'] ?>">
                                    <h6 class="mb-2">Komentáre</h6>
                                    <div id="comments-list-<?= (int)$post['id'] ?>" class="comments-list mb-2">
                                        <?php
                                        // Controller musí dodať $commentsMap (postId => pole komentárov)
                                        $postId = (int)$post['id'];
                                        $commentsForPost = isset($commentsMap[$postId]) && is_array($commentsMap[$postId]) ? $commentsMap[$postId] : [];
                                        if (!empty($commentsForPost)) {
                                            foreach ($commentsForPost as $c) {
                                                ?>
                                                <div class="comment mb-2 p-2 border rounded" data-id="<?= (int)$c['id'] ?>">
                                                    <div class="small text-muted mb-1">
                                                        <?= htmlspecialchars($c['user']) ?> • <?= htmlspecialchars((string)$c['created_at']) ?>
                                                        <?php if (!empty($c['can_edit'])): ?>
                                                            <a href="<?= $link->url('comment.edit', ['id' => $c['id']]) ?>" class="btn btn-sm btn-outline-primary ms-2">Upraviť</a>
                                                        <?php endif; ?>
                                                        <?php if (!empty($c['can_delete'])): ?>
                                                            <form method="post" action="<?= $link->url('comment.delete') ?>" style="display:inline;margin:0;" onsubmit="return confirm('Naozaj zmazať tento komentár?');">
                                                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger ms-2">Zmazať</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="comment-body"><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                                                </div>
                                            <?php }
                                        } else {
                                            ?>
                                            <p class="text-muted">Žiadne komentáre.</p>
                                        <?php } ?>
                                    </div>
                                    <!-- Formulár na pridanie komentára -->
                                    <form class="comment-form" method="post" action="<?= $link->url('comment.create') ?>">
                                        <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                        <div class="mb-2">
                                            <label for="comment-content-<?= (int)$post['id'] ?>" class="visually-hidden">Komentár</label>
                                            <textarea id="comment-content-<?= (int)$post['id'] ?>" name="content" class="form-control" rows="2" placeholder="Napíšte komentár..."></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Pridať komentár</button>
                                        </div>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Zatiaľ tu nie sú žiadne príspevky.</p>
                    <?php endif; ?>

                </div> <!-- /#postsContainer -->

                <!-- configuration for forum JS (no inline JS) -->
                <div id="app-urls" style="display:none;"
                     data-search-url="<?= htmlspecialchars($link->url('home.searchPosts', [], true)) ?>"
                     data-comment-list="<?= htmlspecialchars($link->url('comment.list', [], true)) ?>"
                     data-comment-create="<?= htmlspecialchars($link->url('comment.create', [], true)) ?>"
                     data-comment-delete="<?= htmlspecialchars($link->url('comment.delete', [], true)) ?>"
                     data-comment-edit="<?= htmlspecialchars($link->url('comment.edit', [], true)) ?>"
                ></div>
                <script src="<?= $link->asset('js/forum.boot.js', true) ?>"></script>
                <script src="<?= $link->asset('js/forum.js', true) ?>"></script>
                <script src="<?= $link->asset('js/comments.js', true) ?>"></script>

            </div>
        </div>
    </main>
</div>
