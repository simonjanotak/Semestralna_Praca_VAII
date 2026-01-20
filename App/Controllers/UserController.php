<?php

namespace App\Controllers;

use App\Configuration;
use App\Models\User;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class UserController extends BaseController
{
    // Zobrazí zoznam používateľov – iba pre prihlásených
    public function index(Request $request): Response
    {
        // Vyžaduje prihlásenie
        if (!$this->user || !$this->user->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        try {
            // Načíta všetkých používateľov (najnovší prví)
            $users = User::getAll(null, [], 'created_at DESC');
        } catch (Exception $e) {
            // Pri chybe zobrazí prázdny zoznam
            $users = [];
        }

        return $this->html(compact('users'));
    }

    // Zmaže používateľa (iba POST). Iba admin.
    // Pri AJAX volaní vracia JSON, inak presmeruje
    public function delete(Request $request): Response
    {
        // Povolená je iba POST metóda
        if (!$request->isPost()) {
            return $this->redirect($this->url('user.index'));
        }

        // Vyžaduje prihlásenie
        if (!$this->user || !$this->user->isLoggedIn()) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'unauthenticated']);
            }
            return $this->redirect(Configuration::LOGIN_URL);
        }

        // Kontrola CSRF tokenu je riešená globálnym middleware

        // Vyžaduje rolu admin
        $role = '';
        try {
            $role = $this->user->getRole();
        } catch (Exception $e) {
            // ignorujeme chybu
        }

        if ($role !== 'admin') {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'forbidden']);
            }
            return $this->redirect($this->url('user.index'));
        }

        // ID používateľa na zmazanie
        $id = (int) ($request->post('id') ?? 0);
        if ($id <= 0) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'invalid_id']);
            }
            return $this->redirect($this->url('user.index'));
        }

        // Zabráni zmazaniu práve prihláseného administrátora
        $currentId = null;
        try {
            $currentId = $this->user->getId();
        } catch (Exception $e) {
            // ignorujeme chybu
        }

        if ($currentId !== null && (int)$currentId === $id) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'cannot_delete_self']);
            }
            return $this->redirect($this->url('user.index'));
        }

        try {
            $target = User::getOne($id);
            if (!$target) {
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'error' => 'not_found']);
                }
                return $this->redirect($this->url('user.index'));
            }

            // Zabráni zmazaniu posledného administrátora
            if ($target->getRole() === 'admin') {
                $admins = User::getCount('role = ?', ['admin']);
                if ($admins <= 1) {
                    if ($request->isAjax()) {
                        return $this->json(['success' => false, 'error' => 'cannot_delete_last_admin']);
                    }
                    return $this->redirect($this->url('user.index'));
                }
            }

            // Audit log – zaznamená kto a koho zmazal
            try {
                $actorId = (int)($this->user->getId() ?? 0);
                $logDir = dirname(__DIR__, 3) . '/storage/logs';

                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0755, true);
                }

                $line = sprintf(
                    "[%s] actor=%d action=delete_user target=%d\n",
                    date('c'),
                    $actorId,
                    (int)$target->getId()
                );

                file_put_contents(
                    $logDir . '/admin-actions.log',
                    $line,
                    FILE_APPEND | LOCK_EX
                );
            } catch (Exception $e) {
                // chyby logovania ignorujeme
            }

            // Zmazanie používateľa
            $target->delete();

            if ($request->isAjax()) {
                return $this->json(['success' => true]);
            }

            return $this->redirect($this->url('user.index'));

        } catch (Exception $e) {
            if ($request->isAjax()) {
                return $this->json([
                    'success' => false,
                    'error' => 'exception',
                    'message' => $e->getMessage()
                ]);
            }

            return $this->redirect($this->url('user.index'));
        }
    }
}
