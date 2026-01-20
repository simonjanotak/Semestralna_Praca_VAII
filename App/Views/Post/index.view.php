<?php
/** @var array $post  // keys: title, category_id, content, picture, id */
/** @var Framework\Support\LinkGenerator $link */
/** @var string $formAction */
?>


<?php if (!empty($errors)) { ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $err) { ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<header>
    <link rel="stylesheet" href="<?= $link->asset('css/stylForum.css') ?>">
</header>

<div class="container mt-4">
    <h2><?= !empty($post['id']) ? 'Upraviť príspevok' : 'Pridať príspevok' ?></h2>
    <form method="post" action="<?= $formAction ?>" enctype="multipart/form-data">
        <?php if (!empty($post['id'])) { ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars((string)$post['id']) ?>">
        <?php } ?>

        <div class="mb-3">
            <label for="post-title" class="form-label">Názov</label>
            <input id="post-title" name="title" type="text" class="form-control" required
                   value="<?= htmlspecialchars($post['title'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="post-category" class="form-label">Kategória</label>
            <select id="post-category" name="category_id" class="form-select" required>
                <option value="">Vybrať kategóriu</option>
                <?php if (!empty($categories) && is_array($categories)) {
                    foreach ($categories as $cid => $cname) {
                        $selected = ((string)$cid === (string)($post['category_id'] ?? '')) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars((string)$cid) . '" ' . $selected . '>' . htmlspecialchars($cname) . '</option>';
                    }
                } else {
                    // fallback static options if categories not provided
                    ?>
                    <option value="tech" <?= ((string)($post['category_id'] ?? '') === 'tech') ? 'selected' : '' ?>>Technické problémy</option>
                    <option value="autoservisy" <?= ((string)($post['category_id'] ?? '') === 'autoservisy') ? 'selected' : '' ?>>Autoservisy</option>
                    <option value="tuning" <?= ((string)($post['category_id'] ?? '') === 'tuning') ? 'selected' : '' ?>>Tuning a modifikácie</option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="post-content" class="form-label">Text</label>
            <textarea id="post-content" name="content" class="form-control" rows="6" required><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="post-picture" class="form-label">Obrázok (voliteľné)</label>
            <!-- file input name must match controller expectation: picture_file -->
            <input id="post-picture" name="picture_file" type="file" class="form-control">
            <?php if (!empty($post['picture'])) { ?>
                <small>Aktuálny obrázok: <a href="<?= htmlspecialchars($post['picture']) ?>" target="_blank">zobraziť</a></small>
            <?php } ?>
        </div>

        <button type="submit" class="btn btn-orange"><?= !empty($post['id']) ? 'Uložiť zmeny' : 'Vytvoriť príspevok' ?></button>
        <a href="<?= $link->url('home.forum') ?>" class="btn btn-outline-secondary">Zrušiť</a>
    </form>
</div>
