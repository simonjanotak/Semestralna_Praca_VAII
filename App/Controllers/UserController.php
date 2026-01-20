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
    // show users list - only for logged-in users
    public function index(Request $request): Response
    {
        // require login
        if (! $this->user || ! $this->user->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        try {
            // load all users, newest first
            $users = User::getAll(null, [], 'created_at DESC');
        } catch (Exception $e) {
            // fail gracefully - show empty list and message
            $users = [];
        }

        return $this->html(compact('users'));
    }

    // delete user (expects POST). Admin only. Returns JSON for AJAX or redirects on non-AJAX.
    public function delete(Request $request): Response
    {
        // must be POST
        if (! $request->isPost()) {
            return $this->redirect($this->url('user.index'));
        }

        // require login
        if (! $this->user || ! $this->user->isLoggedIn()) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'unauthenticated']);
            }
            return $this->redirect(Configuration::LOGIN_URL);
        }

        // CSRF check: token may be sent in POST body or X-CSRF-Token header
        $csrf = $request->post('csrf_token') ?? $request->server('HTTP_X_CSRF_TOKEN') ?? null;
        $sessionCsrf = $this->app->getSession()->get('csrf_token') ?? null;
        if (!$csrf || !$sessionCsrf || !hash_equals((string)$sessionCsrf, (string)$csrf)) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'csrf']);
            }
            return $this->redirect($this->url('user.index'));
        }

        // require admin
        $role = '';
        try {
            $role = $this->user->getRole();
        } catch (Exception $e) {
            // ignore
        }
        if ($role !== 'admin') {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'forbidden']);
            }
            return $this->redirect($this->url('user.index'));
        }

        $id = (int) ($request->post('id') ?? 0);
        if ($id <= 0) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'invalid_id']);
            }
            return $this->redirect($this->url('user.index'));
        }

        // Prevent deleting currently logged-in admin
        $currentId = null;
        try {
            $currentId = $this->user->getId();
        } catch (Exception $e) {
            // ignore
        }
        if ($currentId !== null && (int) $currentId === $id) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'cannot_delete_self']);
            }
            return $this->redirect($this->url('user.index'));
        }

        try {
            $target = User::getOne($id);
            if (! $target) {
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'error' => 'not_found']);
                }
                return $this->redirect($this->url('user.index'));
            }

            // Prevent deleting the last remaining admin
            if ($target->getRole() === 'admin') {
                $admins = User::getCount('role = ?', ['admin']);
                if ($admins <= 1) {
                    if ($request->isAjax()) {
                        return $this->json(['success' => false, 'error' => 'cannot_delete_last_admin']);
                    }
                    return $this->redirect($this->url('user.index'));
                }
            }

            // Audit log: record actor and target
            try {
                $actorId = (int)($this->user->getId() ?? 0);
                $logDir = dirname(__DIR__, 3) . '/storage/logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                $line = sprintf("[%s] actor=%d action=delete_user target=%d\n", date('c'), $actorId, (int)$target->getId());
                file_put_contents($logDir . '/admin-actions.log', $line, FILE_APPEND | LOCK_EX);
            } catch (Exception $e) {
                // ignore logging errors
            }

            // delete and respond
            $target->delete();

            if ($request->isAjax()) {
                return $this->json(['success' => true]);
            }

            return $this->redirect($this->url('user.index'));

        } catch (Exception $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'error' => 'exception', 'message' => $e->getMessage()]);
            }
            return $this->redirect($this->url('user.index'));
        }
    }
}
