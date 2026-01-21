<?php /** @var array $comment */ /** @var string $referer */ /** @var \Framework\Support\LinkGenerator $link */ ?>
<!--AI - copilot práca -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Upraviť komentár</h5>
        <p class="text-muted small">Autor: <?= htmlspecialchars($comment['user']) ?> • <?= htmlspecialchars($comment['created_at']) ?></p>

        <form method="post" action="<?= $link->url('comment.edit') ?>">
            <input type="hidden" name="id" value="<?= (int)$comment['id'] ?>">
            <!-- Always send back the forum URL as referer so we reliably return to the forum page -->
            <input type="hidden" name="referer" value="<?= htmlspecialchars($link->url('home.forum')) ?>">
            <div class="mb-3">
                <label for="comment-content-edit" class="form-label">Komentár</label>
                <textarea id="comment-content-edit" name="content" rows="5" class="form-control"><?= htmlspecialchars($comment['content']) ?></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <a href="<?= $link->url('home.forum') ?>" class="btn btn-secondary me-2">Zrušiť</a>
                <button type="submit" class="btn btn-primary">Uložiť zmeny</button>
            </div>
        </form>
    </div>
</div>
