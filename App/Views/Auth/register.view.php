<?php


/** @var string|null $message */
/** @var \Framework\Support\LinkGenerator $link */
/** @var \Framework\Support\View $view */

$view->setLayout('auth');
?>
<header>
    <link rel="stylesheet" href="css/styleLogin.css">
</header>
<section class="py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-7 col-md-9">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="badge badge-like mb-2">Najväčšia slovenská auto komunita</span>
                            <h2 class="fw-bold mb-1">Registrácia</h2>
                            <p class="text-muted small mb-3">Vytvorte si účet na PitStop.sk</p>
                        </div>

                        <form id="registerForm" class="needs-validation" novalidate method="post" action="<?= $link->url('auth.register') ?>">
                            <input type="hidden" name="submit" value="1">
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-danger"><?= $message ?></div>
                            <?php endif; ?>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="username" class="form-label">Užívateľské meno</label>
                                    <input id="username" name="username" type="text" class="form-control"
                                           placeholder="petr123" required minlength="3">
                                    <div class="invalid-feedback">Zadajte užívateľské meno (aspoň 3 znaky).</div>

                                    <!-- Username availability status (updated by JS) -->
                                    <div id="usernameStatus" class="form-text mt-1"></div>
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input id="email" name="email" type="email" class="form-control"
                                           placeholder="you@example.com" required>
                                    <div class="invalid-feedback">Zadajte platný email.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">Heslo</label>
                                    <input id="password" name="password" type="password" class="form-control"
                                           placeholder="Heslo" required minlength="6">
                                    <div class="invalid-feedback">Heslo musí obsahovať aspoň 6 znakov.</div>
                                    <div id="pwdMeter" class="form-text mt-1"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="passwordConfirm" class="form-label">Potvrďte heslo</label>
                                    <input id="passwordConfirm" name="passwordConfirm" type="password"
                                           class="form-control" placeholder="Znova zadajte heslo" required
                                           minlength="6">
                                    <div class="invalid-feedback" id="confirmError">Heslá sa nezhodujú.</div>
                                </div>
                                <div class="col-12 d-grid">
                                    <button id="submitBtn" type="submit" class="btn btn-dark btn-lg">Registrovať sa
                                    </button>
                                </div>
                                <div class="col-12 text-center mt-3">
                                    <a href="<?= $link->url('auth.login')?>" class="btn btn-outline-secondary me-2">Prihlásiť sa</a>
                                    <a href="<?= $link->url('home.index') ?>" class="btn btn-outline-secondary">Späť</a>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-flex justify-content-center">
                <img src="images/porsak2.png" alt="auto" class="img-fluid" style="max-height:260px; opacity:0.95;">
            </div>
        </div>
    </div>
</section>
<script src="<?= $link->asset('js/registerAjax.js', true) ?>"></script>
<script src="<?= $link->asset('js/register.js', true) ?>" ></script>
