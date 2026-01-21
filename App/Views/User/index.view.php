<?php
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\User[] $users */
?>
<!-- AI responzivny dizajn  -->
<link rel="stylesheet" href="<?= $link->asset('css/users.css') ?>">
<script src="<?= $link->asset('js/users.js') ?>" defer></script>

<section class="users-hero">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between">
        <div>
            <h1>Užívatelia</h1>
            <p class="muted">Zoznam registrovaných používateľov</p>
        </div>
        <div class="d-flex gap-2 align-items-center mt-3 mt-md-0">
            <a class="btn btn-secondary btn-sm" href="<?= $link->url('home.index') ?>">Domov</a>
            <img src="<?= $link->asset('images/krasne.png') ?>" alt="logo" class="small-logo ms-2">
        </div>
    </div>
</section>

<div id="users-root" data-delete-url="<?= htmlspecialchars($link->url('user.delete')) ?>" class="container mt-4">
    <?php if (empty($users)): ?>
        <div class="alert alert-info">Žiadni používatelia.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm users-table">
                <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Registrovaný</th>
                    <?php if ($user->isLoggedIn() && $user->getRole() === 'admin'): ?>
                        <th>Akcie</th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr data-id="<?= (int)$u->getId() ?>">
                        <td><?= htmlspecialchars($u->getUsername()) ?></td>
                        <td><?= htmlspecialchars($u->getRole()) ?></td>
                        <td><?= htmlspecialchars($u->getCreatedAt() ?? '') ?></td>
                        <?php if ($user->isLoggedIn() && $user->getRole() === 'admin'): ?>
                            <td>
                                <?php if ($user->getId() !== $u->getId()): ?>
                                    <button class="btn btn-sm btn-danger delete-user" data-id="<?= (int)$u->getId() ?>">Vymazať</button>
                                <?php else: ?>
                                    <span class="text-muted">nie je možné</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
