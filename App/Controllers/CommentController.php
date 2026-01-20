<?php
namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends BaseController
{
    // Return empty JSON array when no post_id provided, otherwise comments for post.
    public function list(Request $request): \Framework\Http\Responses\JsonResponse
    {
        $postId = $request->get('post_id') ?? $request->value('post_id');
        if ($postId === null) {
            return $this->json([]);
        }

        $comments = Comment::getByPost((int)$postId);
        $currentUserId = $this->getCurrentUserId();
        $isAdmin = $this->isCurrentUserAdmin();

        $out = array_map(function($c) use ($currentUserId, $isAdmin) {
            $user = $c->getUser();
            $uid = $c->getUserId();
            $can = $isAdmin || ($currentUserId !== null && $uid === $currentUserId);
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
    }

    // Create comment (POST). Supports AJAX or regular POST.
    public function create(Request $request): Response
    {
        if (!$request->isPost()) {
            return $request->isAjax() ? $this->json(['error' => 'Invalid method']) : $this->redirect($this->url('home.forum'));
        }

        // CSRF protection
        $csrf = $request->post('csrf_token') ?? $request->server('HTTP_X_CSRF_TOKEN') ?? null;
        $sessionCsrf = $this->app->getSession()->get('csrf_token') ?? null;
        if (!$csrf || !$sessionCsrf || !hash_equals((string)$sessionCsrf, (string)$csrf)) {
            return $request->isAjax() ? $this->json(['error' => 'Invalid CSRF token'], 403) : $this->redirect($this->url('home.forum'));
        }

        $currentUserId = $this->getCurrentUserId();
        if ($currentUserId === null) {
            return $request->isAjax() ? $this->json(['error' => 'Unauthorized'], 401) : $this->redirect($this->url('auth.login'));
        }

        $postId = (int)($request->post('post_id') ?? 0);
        $content = trim((string)($request->post('content') ?? ''));
        if ($postId <= 0 || $content === '') {
            return $request->isAjax() ? $this->json(['error' => 'Invalid input'], 400) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $post = Post::getOne($postId);
        if ($post === null) {
            return $request->isAjax() ? $this->json(['error' => 'Post not found'], 404) : $this->redirect($this->url('home.forum'));
        }

        $comment = new Comment();
        $comment->setPostId($postId);
        $comment->setUserId($currentUserId);
        $comment->setContent($content);
        $comment->save();

        $user = $comment->getUser();
        $out = [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'created_at' => $comment->getCreatedAt(),
            'user' => $user ? $user->getUsername() : 'Neznámy',
            'user_id' => $currentUserId,
            'can_delete' => true,
            'can_edit' => true,
        ];

        return $request->isAjax() ? $this->json($out) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
    }

    // Edit: GET shows form, POST updates comment.
    public function edit(Request $request): Response
    {
        $id = $request->isPost() ? (int)($request->post('id') ?? 0) : (int)($request->get('id') ?? $request->value('id'));
        if ($id <= 0) {
            return $request->isAjax() ? $this->json(['error' => 'Invalid id'], 400) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $comment = Comment::getOne($id);
        if ($comment === null) {
            return $request->isAjax() ? $this->json(['error' => 'Not found'], 404) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $currentUserId = $this->getCurrentUserId();
        $isAdmin = $this->isCurrentUserAdmin();
        if (!$isAdmin && ($currentUserId === null || $comment->getUserId() !== $currentUserId)) {
            return $request->isAjax() ? $this->json(['error' => 'Forbidden'], 403) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        if ($request->isPost()) {
            // CSRF protection for edit POST
            $csrf = $request->post('csrf_token') ?? $request->server('HTTP_X_CSRF_TOKEN') ?? null;
            $sessionCsrf = $this->app->getSession()->get('csrf_token') ?? null;
            if (!$csrf || !$sessionCsrf || !hash_equals((string)$sessionCsrf, (string)$csrf)) {
                return $request->isAjax() ? $this->json(['error' => 'Invalid CSRF token'], 403) : $this->redirect($this->url('home.forum'));
            }

            $content = trim((string)($request->post('content') ?? ''));
            $referer = $request->post('referer') ?? $request->server('HTTP_REFERER') ?? $this->url('home.forum');
            if ($content === '') {
                return $request->isAjax() ? $this->json(['error' => 'Invalid content'], 400) : $this->redirect($referer);
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

            return $request->isAjax() ? $this->json($out) : $this->redirect($referer);
        }

        // GET -> show simple edit view
        $cUser = $comment->getUser();
        $commentArr = [
            'id' => (int)$comment->getId(),
            'content' => (string)$comment->getContent(),
            'created_at' => (string)$comment->getCreatedAt(),
            'user' => $cUser ? $cUser->getUsername() : 'Neznámy',
            'user_id' => $comment->getUserId(),
        ];

        return $this->html(['comment' => $commentArr, 'referer' => $this->url('home.forum')], 'edit');
    }

    // Delete comment (POST only)
    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            return $request->isAjax() ? $this->json(['error' => 'Invalid method']) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        // CSRF protection for delete
        $csrf = $request->post('csrf_token') ?? $request->server('HTTP_X_CSRF_TOKEN') ?? null;
        $sessionCsrf = $this->app->getSession()->get('csrf_token') ?? null;
        if (!$csrf || !$sessionCsrf || !hash_equals((string)$sessionCsrf, (string)$csrf)) {
            return $request->isAjax() ? $this->json(['error' => 'Invalid CSRF token'], 403) : $this->redirect($this->url('home.forum'));
        }

        $id = (int)($request->post('id') ?? 0);
        if ($id <= 0) {
            return $request->isAjax() ? $this->json(['error' => 'Invalid id'], 400) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $comment = Comment::getOne($id);
        if ($comment === null) {
            return $request->isAjax() ? $this->json(['error' => 'Not found'], 404) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $currentUserId = $this->getCurrentUserId();
        $isAdmin = $this->isCurrentUserAdmin();
        if (!$isAdmin && ($currentUserId === null || $comment->getUserId() !== $currentUserId)) {
            return $request->isAjax() ? $this->json(['error' => 'Forbidden'], 403) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
        }

        $comment->delete();

        return $request->isAjax() ? $this->json(['ok' => true]) : $this->redirect($request->server('HTTP_REFERER') ?? $this->url('home.forum'));
    }

    // Simple helper to get current user id or null
    private function getCurrentUserId(): ?int
    {
        if (!isset($this->user) || !$this->user->isLoggedIn()) {
            return null;
        }

        $identity = $this->user->getIdentity();
        if (is_object($identity) && method_exists($identity, 'getId')) {
            return (int)$identity->getId();
        }

        if (is_object($identity) && property_exists($identity, 'id')) {
            return (int)$identity->id;
        }

        if (method_exists($this->user, 'getId')) {
            return (int)$this->user->getId();
        }

        return null;
    }

    // Simple role check
    private function isCurrentUserAdmin(): bool
    {
        if (!isset($this->user) || !$this->user->isLoggedIn()) {
            return false;
        }

        $identity = $this->user->getIdentity();
        if (is_object($identity) && method_exists($identity, 'getRole')) {
            $role = $identity->getRole();
            return ($role === 'admin' || $role === 'moderator');
        }

        if (is_object($identity) && property_exists($identity, 'role')) {
            return ($identity->role === 'admin' || $identity->role === 'moderator');
        }

        if (method_exists($this->user, 'getRole')) {
            $role = $this->user->getRole();
            return ($role === 'admin' || $role === 'moderator');
        }

        return false;
    }

    // Minimal index to satisfy BaseController
    public function index(Request $request): Response
    {
        return $this->json([]);
    }
}
