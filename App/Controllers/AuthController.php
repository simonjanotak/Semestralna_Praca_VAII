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
 * Class AuthController
 *
 * This controller handles authentication actions such as login, logout, and redirection to the login page. It manages
 * user sessions and interactions with the authentication system.
 *
 * @package App\Controllers
 */
class AuthController extends BaseController
{
    /**
     * Redirects to the login page.
     *
     * This action serves as the default landing point for the authentication section of the application, directing
     * users to the login URL specified in the configuration.
     *
     * @return Response The response object for the redirection to the login page.
     */
    public function index(Request $request): Response
    {
        return $this->redirect(Configuration::LOGIN_URL);
    }

    /**
     * Authenticates a user and processes the login request.
     *
     * This action handles user login attempts. If the login form is submitted, it attempts to authenticate the user
     * with the provided credentials. Upon successful login, the user is redirected to the admin dashboard.
     * If authentication fails, an error message is displayed on the login page.
     *
     * @return Response The response object which can either redirect on success or render the login view with
     *                  an error message on failure.
     * @throws Exception If the parameter for the URL generator is invalid throws an exception.
     */
    public function login(Request $request): Response
    {
        $message = null;
        if ($request->hasValue('submit')) {
            $email = trim((string)$request->value('email'));
            $password = (string)$request->value('password');

            if ($email === '' || $password === '') {
                $message = 'Zadajte email a heslo.';
                return $this->html(compact('message'));
            }

            $user = User::findByEmail($email);
            if ($user === null) {
                $message = 'Nesprávny email alebo heslo.';
                return $this->html(compact('message'));
            }

            // Normal check: verify hashed password
            if ($user->verifyPassword($password)) {
                // ok
            } else {
                // Fallback for legacy/hand-inserted plaintext passwords in DB:
                // Detect common bcrypt prefix. If stored value does NOT look like a bcrypt hash,
                // and equals the provided password, upgrade it to a proper hash and allow login.
                $stored = $user->getPasswordHash();
                $looksHashed = (bool)preg_match('/^\$2[aby]\$[0-9]{2}\$/', $stored);
                if (!$looksHashed && $stored !== '' && hash_equals((string)$stored, $password)) {
                    // Re-hash and save securely so next login uses password_verify
                    $user->setPassword($password);
                    try { $user->save(); } catch (\Throwable $e) { /* ignore save error, continue to login */ }
                } else {
                    $message = 'Nesprávny email alebo heslo.';
                    return $this->html(compact('message'));
                }
            }
            // Successful login: create identity and store in session
            $identity = new UserIdentity((int)$user->getId(), $user->getUsername(), $user->getRole(), $user->getEmail());
            $this->app->getSession()->set(Configuration::IDENTITY_SESSION_KEY, $identity);

            // redirect to forum/home
            return $this->redirect($this->url('home.forum'));
        }

        return $this->html(compact('message'));
    }
    public function register(Request $request): Response
    {
        $message = null;
        if ($request->hasValue('submit')) {
            $username = trim((string)$request->value('username'));
            $email = trim((string)$request->value('email'));
            $password = (string)$request->value('password');
            $passwordConfirm = (string)$request->value('passwordConfirm');

            $errors = [];
            // basic validation
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

            // Unique checks
            if (User::getCount('username = ?', [$username]) > 0) {
                $errors[] = 'Užívateľské meno už existuje.';
            }
            if (User::getCount('email = ?', [$email]) > 0) {
                $errors[] = 'E-mail už je registrovaný.';
            }

            if (empty($errors)) {
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setPassword($password); // hashes with password_hash
                $user->setRole('user');
                $user->save();

                // auto-login: create identity and store in session
                $identity = new UserIdentity((int)$user->getId(), $user->getUsername(), $user->getRole(), $user->getEmail());
                $this->app->getSession()->set(Configuration::IDENTITY_SESSION_KEY, $identity);

                return $this->redirect($this->url('home.forum'));
            }

            $message = implode('<br>', $errors);
        }

        return $this->html(compact('message'));
    }
    /**
     * Logs out the current user.
     *
     * This action terminates the user's session and redirects them to a view. It effectively clears any authentication
     * tokens or session data associated with the user.
     *
     * @return ViewResponse The response object that renders the logout view.
     */
    public function logout(Request $request): Response
    {
        $this->app->getAuthenticator()->logout();
        return $this->redirect($this->url('home.index'));
    }
}
