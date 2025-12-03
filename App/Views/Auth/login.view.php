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
            <div class="col-lg-6 col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="badge badge-like mb-2">Najväčšia slovenská auto komunita</span>
                            <h2 class="fw-bold mb-1">Prihlásenie</h2>
                            <p class="text-muted small mb-3">Prihláste sa do svojho účtu na PitStop.sk</p>
                        </div>

                        <form class="needs-validation" novalidate method="post" action="#">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" name="email" type="email" class="form-control" placeholder="you@example.com" required>
                                <div class="invalid-feedback">Zadajte platný email.</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Heslo</label>
                                <input id="password" name="password" type="password" class="form-control" placeholder="Heslo" required minlength="6">
                                <div class="invalid-feedback">Zadajte heslo (minimálne 6 znakov).</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input id="remember" class="form-check-input" type="checkbox">
                                    <label class="form-check-label" for="remember">Zapamätať</label>
                                </div>
                                <a href="#" class="small">Zabudnuté heslo?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark btn-lg">Prihlásiť sa</button>
                            </div>

                            <div class="text-center mt-3">
                                <a href="<?= $link->url('home.index') ?>" class="btn btn-outline-secondary me-2">Späť</a>
                                <a href="<?= $link->url('auth.register') ?>" class="btn btn-outline-secondary me-2">Registrovať sa</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                <img src="images/porsak.png" alt="auto" class="img-fluid" style="max-height:260px; opacity:0.95;">
            </div>

        </div>
    </div>
</section>
