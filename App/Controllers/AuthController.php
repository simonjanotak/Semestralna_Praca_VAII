<?php

namespace App\Controllers;

use App\Configuration;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\ViewResponse;
use App\Models\User;
use Framework\Auth\UserIdentity;

/**
 * Controller pre autentifikáciu (login, register, logout)
 */
class AuthController extends BaseController
{
    /**
     * Predvolená akcia – presmeruje na login
     */
    public function index(Request $request): Response
    {
        return $this->redirect(Configuration::LOGIN_URL);
    }

    /**
     * Prihlásenie používateľa
     *
     *
     */
    public function login(Request $request): Response
    {
        $message = null;

        // Ak bol odoslaný formulár
        if ($request->hasValue('submit')) {

            // Minimalistická CSRF validácia
            $posted = (string)$request->value('_csrf');
            $sessionToken = $this->app->getSession()->get(Configuration::CSRF_TOKEN_KEY);
            if (!is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $posted)) {
                $message = 'Neplatný CSRF token.';
                return $this->html(compact('message'));
            }

            // Načítanie a orezanie vstupov
            $email = trim((string)$request->value('email'));
            $password = (string)$request->value('password');

            // Základná kontrola
            if ($email === '' || $password === '') {
                $message = 'Zadajte email a heslo.';
                return $this->html(compact('message'));
            }

            // Vyhľadanie používateľa podľa emailu
            $user = User::findByEmail($email);
            if ($user === null) {
                $message = 'Nesprávny email alebo heslo.';
                return $this->html(compact('message'));
            }

            // Overenie hesla (password_verify)
            if (!$user->verifyPassword($password)) {

                // Fallback pre staré plaintext heslá v DB
                $stored = $user->getPasswordHash();
                $looksHashed = (bool)preg_match('/^\$2[aby]\$[0-9]{2}\$/', $stored);

                if (!$looksHashed && $stored !== '' && hash_equals((string)$stored, $password)) {
                    // Prehashovanie hesla do bezpečnej podoby
                    $user->setPassword($password);
                    try {
                        $user->save();
                    } catch (\Throwable $e) {
                        // chyba uloženia sa ignoruje
                    }
                } else {
                    $message = 'Nesprávny email alebo heslo.';
                    return $this->html(compact('message'));
                }
            }

            // Vytvorenie identity používateľa
            $identity = new UserIdentity(
                (int)$user->getId(),
                $user->getUsername(),
                $user->getRole(),
                $user->getEmail()
            );

            // Uloženie identity do session
            $this->app->getSession()->set(Configuration::IDENTITY_SESSION_KEY, $identity);

            // Regenerovať session id po prihlásení (prevencia session fixation)
            if (function_exists('session_regenerate_id')) {
                @session_regenerate_id(true);
            }

            // Presmerovanie po prihlásení
            return $this->redirect($this->url('home.forum'));
        }

        // Zobrazenie login stránky
        return $this->html(compact('message'));
    }

    /**
     * Registrácia nového používateľa
     */
    public function register(Request $request): Response
    {
        $message = null;

        // Ak bol odoslaný formulár
        if ($request->hasValue('submit')) {

            // Minimalistická CSRF validácia
            $posted = (string)$request->value('_csrf');
            $sessionToken = $this->app->getSession()->get(Configuration::CSRF_TOKEN_KEY);
            if (!is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $posted)) {
                $message = 'Neplatný CSRF token.';
                return $this->html(compact('message'));
            }

            // Načítanie údajov z formulára
            $username = trim((string)$request->value('username'));
            $email = trim((string)$request->value('email'));
            $password = (string)$request->value('password');
            $passwordConfirm = (string)$request->value('passwordConfirm');

            $errors = [];

            // Validácia vstupov
            if ($username === '' || mb_strlen($username) < 3) {
                $errors[] = 'Username musí obsahovať aspoň 3 znaky.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Neplatný e-mail.';
            }

            if ($password === '' || mb_strlen($password) < 6) {
                $errors[] = 'Heslo musí obsahovať aspoň 6 znakov.';
            }

            if ($password !== $passwordConfirm) {
                $errors[] = 'Heslá sa nezhodujú.';
            }

            // Kontrola unikátnosti
            if (User::getCount('username = ?', [$username]) > 0) {
                $errors[] = 'Užívateľské meno už existuje.';
            }

            if (User::getCount('email = ?', [$email]) > 0) {
                $errors[] = 'E-mail už je registrovaný.';
            }

            // Ak nie sú chyby → uložíme používateľa
            if (empty($errors)) {
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setPassword($password); // zahashuje heslo
                $user->setRole('user');
                $user->save();

                // Automatické prihlásenie po registrácii
                $identity = new UserIdentity(
                    (int)$user->getId(),
                    $user->getUsername(),
                    $user->getRole(),
                    $user->getEmail()
                );

                $this->app->getSession()->set(Configuration::IDENTITY_SESSION_KEY, $identity);

                // Regenerovať session id po registrácii/prihlásení (prevencia session fixation)
                if (function_exists('session_regenerate_id')) {
                    @session_regenerate_id(true);
                }

                return $this->redirect($this->url('home.forum'));
            }

            // Spojenie chýb do jednej správy
            $message = implode('<br>', $errors);
        }

        // Zobrazenie registračnej stránky
        return $this->html(compact('message'));
    }

    /**
     * Odhlásenie používateľa
     */
    public function logout(Request $request): Response
    {
        $this->app->getAuthenticator()->logout();
        return $this->redirect($this->url('auth.login'));
    }

    /**
     * AJAX – kontrola dostupnosti používateľského mena
     * GET parameter: q
     */
    public function checkUsernameAvailability(Request $request): \Framework\Http\Responses\JsonResponse
    {
        $q = trim((string)$request->value('q'));

        // Príliš krátke meno
        if ($q === '' || mb_strlen($q) < 2) {
            return new \Framework\Http\Responses\JsonResponse([
                'available' => false,
                'message' => 'Zadajte aspoň 2 znaky'
            ]);
        }

        // Kontrola existencie v databáze
        $exists = User::existsByUsername($q);
        if ($exists) {
            return new \Framework\Http\Responses\JsonResponse([
                'available' => false,
                'message' => 'Používateľské meno je obsadené'
            ]);
        }

        // Meno je voľné
        return new \Framework\Http\Responses\JsonResponse([
            'available' => true,
            'message' => 'Používateľské meno je voľné'
        ]);
    }
}
