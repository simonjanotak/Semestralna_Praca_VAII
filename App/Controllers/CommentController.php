<?php
namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentController extends BaseController
{
    // List comments for a post (AJAX)
    public function list(Request $request): \Framework\Http\Responses\JsonResponse
    {
        try {
            $postId = $request->get('post_id') ?? $request->value('post_id');
            if ($postId === null) {
                return $this->json([]);
            }
            $postId = (int)$postId;
            $comments = Comment::getByPost($postId);

            // determine current user to mark deletable/editable comments
            $currentUserId = $this->getCurrentUserId();
            $isAdmin = $this->isCurrentUserAdmin();

            $out = array_map(function($c) use ($currentUserId, $isAdmin) {
                $user = $c->getUser();
                $uid = $c->getUserId();
                $can = ($isAdmin || ($currentUserId !== null && $uid === $currentUserId));
                return [
                    'id' => $c->getId(),
                    'content' => $c->getContent(),
                    'created_at' => $c->getCreatedAt(),
                    'user' => $user ? $user->getUsername() : 'Neznámy',
                    'user_id' => $uid,
                    'can_delete' => $can,
                    'can_edit' => $can,
                ];
            }, $comments);

            return $this->json($out);
        } catch (\Throwable $e) {
            // Return structured JSON error so frontend can diagnose
            return $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // Create comment (supports AJAX or regular form POST)
    public function create(Request $request): Response
    {
        try {
            if (!$request->isPost()) {
                if ($request->isAjax()) {
                    return $this->json(['error' => 'Invalid method']);
                }
                return $this->redirect($this->url('home.forum'));
            }

            $currentUserId = $this->getCurrentUserId();
            if ($currentUserId === null) {
                if ($request->isAjax()) {
                    return $this->json(['error' => 'Unauthorized'], 401);
                }
                try { $this->app->getSession()->set('flash_message', 'Musíte byť prihlásený, aby ste mohli písať komentáre.'); } catch (\Throwable $e) {}
                return $this->redirect($this->url('auth.login'));
            }

            $postId = (int)($request->post('post_id') ?? 0);
            $content = trim((string)($request->post('content') ?? ''));

            if ($postId <= 0 || $content === '') {
                if ($request->isAjax()) {
                    return $this->json(['error' => 'Invalid input'], 400);
                }
                try { $this->app->getSession()->set('flash_message', 'Neplatný komentár.'); } catch (\Throwable $e) {}
                $referer = $request->server('HTTP_REFERER') ?? $this->url('home.forum');
                return $this->redirect($referer);
            }

            $post = Post::getOne($postId);
            if ($post === null) {
                if ($request->isAjax()) {
                    return $this->json(['error' => 'Post not found'], 404);
                }
                try { $this->app->getSession()->set('flash_message', 'Príspevok nebol nájdený.'); } catch (\Throwable $e) {}
                return $this->redirect($this->url('home.forum'));
            }

            $comment = new Comment();
            $comment->setPostId($postId);
            $comment->setUserId($currentUserId);
            $comment->setContent($content);
            $comment->save();

            $user = $comment->getUser();
            $can = true; // author can edit/delete
            $out = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'created_at' => $comment->getCreatedAt(),
                'user' => $user ? $user->getUsername() : 'Neznámy',
                'user_id' => $currentUserId,
                'can_delete' => $can,
                'can_edit' => $can,
            ];

            if ($request->isAjax()) {
                return $this->json($out);
            }

            // regular form submit -> redirect back to referer (or forum)
            $referer = $request->server('HTTP_REFERER') ?? $this->url('home.forum');
            return $this->redirect($referer);
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
            }
            try { $this->app->getSession()->set('flash_message', 'Chyba pri uložení komentára.'); } catch (\Throwable $ee) {}
            return $this->redirect($this->url('home.forum'));
        }
    }

    // Edit comment (GET shows form, POST updates)
    public function edit(Request $request): Response
    {
        try {
            // detect id from GET or POST
            $id = $request->isPost() ? (int)($request->post('id') ?? 0) : (int)($request->get('id') ?? $request->value('id'));
            if ($id <= 0) {
                if ($request->isAjax()) {
                    return $this->json(['error' => 'Invalid id'], 400);
                }
                try { $this->app->getSession()->set('flash_message', 'Neplatné id komentára.'); } catch (\Throwable $e) {}
                return $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
            }

            $comment = Comment::getOne($id);
            if ($comment === null) {
                if ($request->isAjax()) {
                    return $this->json(['error' => 'Not found'], 404);
                }
                try { $this->app->getSession()->set('flash_message', 'Komentár nebol nájdený.'); } catch (\Throwable $e) {}
                return $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
            }

            $currentUserId = $this->getCurrentUserId();
            $isAdmin = $this->isCurrentUserAdmin();
            if (!$isAdmin && ($currentUserId === null || $comment->getUserId() !== $currentUserId)) {
                if ($request->isAjax()) {
                    return $this->json(['error' => 'Forbidden'], 403);
                }
                try { $this->app->getSession()->set('flash_message', 'Nemáte oprávnenie upraviť tento komentár.'); } catch (\Throwable $e) {}
                return $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
            }

            if ($request->isPost()) {
                // perform update
                $content = trim((string)($request->post('content') ?? ''));
                // determine redirect target: prefer posted referer, then server referer, then forum
                $referer = $request->post('referer') ?? $request->server('HTTP_REFERER') ?? $this->url('home.forum');
                if ($content === '') {
                    if ($request->isAjax()) {
                        return $this->json(['error' => 'Invalid content'], 400);
                    }
                    try { $this->app->getSession()->set('flash_message', 'Komentár nesmie byť prázdny.'); } catch (\Throwable $e) {}
                    return $this->redirect($referer);
                }

                $comment->setContent($content);
                $comment->save();

                $user = $comment->getUser();
                $out = [
                    'id' => $comment->getId(),
                    'content' => $comment->getContent(),
                    'created_at' => $comment->getCreatedAt(),
                    'user' => $user ? $user->getUsername() : 'Neznámy',
                    'user_id' => $comment->getUserId(),
                    'can_edit' => true,
                    'can_delete' => $isAdmin || ($currentUserId !== null && $comment->getUserId() === $currentUserId),
                ];

                if ($request->isAjax()) {
                    return $this->json($out);
                }

                try { $this->app->getSession()->set('flash_message', 'Komentár bol upravený.'); } catch (\Throwable $e) {}
                return $this->redirect($referer);
            }

            // GET -> show edit form (view expects 'comment' array)
            $cUser = null;
            try { $cUser = $comment->getUser(); } catch (\Throwable $_) { $cUser = null; }
            $commentArr = [
                'id' => (int)$comment->getId(),
                'content' => (string)$comment->getContent(),
                'created_at' => (string)$comment->getCreatedAt(),
                'user' => $cUser ? $cUser->getUsername() : 'Neznámy',
                'user_id' => $comment->getUserId(),
            ];

            return $this->html(['comment' => $commentArr, 'referer' => $this->url('home.forum')], 'edit');

        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
            }
            try { $this->app->getSession()->set('flash_message', 'Chyba pri úprave komentára.'); } catch (\Throwable $ee) {}
            return $this->redirect($this->url('home.forum'));
        }
    }

    // Delete comment (AJAX POST or regular POST)
    public function delete(Request $request): Response
    {
        // Only allow POST - for non-POST redirect back or return JSON for AJAX
        if (!$request->isPost()) {
            if ($request->isAjax()) {
                return $this->json(['error' => 'Invalid method']);
            }
            return $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $id = (int)($request->post('id') ?? 0);
        if ($id <= 0) {
            if ($request->isAjax()) {
                return $this->json(['error' => 'Invalid id'], 400);
            }
            try { $this->app->getSession()->set('flash_message', 'Neplatné id komentára.'); } catch (\Throwable $e) {}
            return $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $comment = Comment::getOne($id);
        if ($comment === null) {
            if ($request->isAjax()) {
                return $this->json(['error' => 'Not found'], 404);
            }
            try { $this->app->getSession()->set('flash_message', 'Komentár nebol nájdený.'); } catch (\Throwable $e) {}
            return $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $currentUserId = $this->getCurrentUserId();
        $isAdmin = $this->isCurrentUserAdmin();
        if (!$isAdmin && ($currentUserId === null || $comment->getUserId() !== $currentUserId)) {
            if ($request->isAjax()) {
                return $this->json(['error' => 'Forbidden'], 403);
            }
            try { $this->app->getSession()->set('flash_message', 'Nemáte oprávnenie zmazať tento komentár.'); } catch (\Throwable $e) {}
            return $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $comment->delete();

        if ($request->isAjax()) {
            return $this->json(['ok' => true]);
        }

        try { $this->app->getSession()->set('flash_message', 'Komentár bol zmazaný.'); } catch (\Throwable $e) {}
        return $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
    }

    // Helper: try to read current user id from AppUser/identity
    private function getCurrentUserId(): ?int
    {
        try {
            if ($this->user->isLoggedIn()) {
                $identity = $this->user->getIdentity();
                if (is_object($identity)) {
                    if (method_exists($identity, 'getId')) {
                        return (int)$identity->getId();
                    }
                    if (property_exists($identity, 'id')) {
                        return (int)$identity->id;
                    }
                }
                if (method_exists($this->user, 'getId')) {
                    return (int)$this->user->getId();
                }
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    // Helper: check if current user has role 'admin'
    private function isCurrentUserAdmin(): bool
    {
        try {
            if ($this->user->isLoggedIn()) {
                $identity = $this->user->getIdentity();
                if (is_object($identity)) {
                    if (method_exists($identity, 'getRole')) {
                        $role = $identity->getRole();
                        return ($role === 'admin' || $role === 'moderator');
                    }
                    if (property_exists($identity, 'role')) {
                        return ($identity->role === 'admin' || $identity->role === 'moderator');
                    }
                }
                if (method_exists($this->user, 'getRole')) {
                    $role = $this->user->getRole();
                    return ($role === 'admin' || $role === 'moderator');
                }
            }
        } catch (\Throwable $e) {
        }
        return false;
    }

    // For compatibility with BaseController abstract signature
    public function index(Request $request): Response
    {
        return $this->json([]);
    }
}
