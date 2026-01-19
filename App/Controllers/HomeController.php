<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Post;
use App\Models\Comment;

/**
 * Class HomeController
 * Handles actions related to the home page and other public actions.
 *
 * This controller includes actions that are accessible to all users, including a default landing page and a contact
 * page. It provides a mechanism for authorizing actions based on user permissions.
 *
 * @package App\Controllers
 */
class HomeController extends BaseController
{
    /**
     * Authorizes controller actions based on the specified action name.
     *
     * In this implementation, all actions are authorized unconditionally.
     *
     * @param string $action The action name to authorize.
     * @return bool Returns true, allowing all actions.
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Displays the default home page.
     *
     * This action serves the main HTML view of the home page.
     *
     * @return Response The response object containing the rendered HTML for the home page.
     */
    public function index(Request $request): Response
    {
        return $this->html();
    }
    /**
     * Displays the contact page.
     *
     * This action serves the HTML view for the contact page, which is accessible to all users without any
     * authorization.
     *
     * @return Response The response object containing the rendered HTML for the contact page.
     */
    public function contact(Request $request): Response
    {
        return $this->html();
    }
    public function forum(Request $request): Response
    {
        // fetch all posts (models)
        $posts = Post::getAll();

        // compute current user id and privileged flag (admin/moderator)
        $currentUserId = null;
        $isPrivileged = false;
        try {
            if (isset($this->user) && $this->user->isLoggedIn()) {
                $identity = $this->user->getIdentity();
                if (is_object($identity)) {
                    if (method_exists($identity, 'getId')) {
                        $currentUserId = (int)$identity->getId();
                    } elseif (property_exists($identity, 'id')) {
                        $currentUserId = (int)$identity->id;
                    }
                    if (method_exists($identity, 'getRole')) {
                        $role = $identity->getRole();
                    } elseif (property_exists($identity, 'role')) {
                        $role = $identity->role ?? null;
                    }
                } else {
                    if (method_exists($this->user, 'getId')) {
                        $currentUserId = (int)$this->user->getId();
                    }
                    if (method_exists($this->user, 'getRole')) {
                        $role = $this->user->getRole();
                    }
                }

                if (isset($role) && ($role === 'admin' || $role === 'moderator')) {
                    $isPrivileged = true;
                }
            }
        } catch (\Throwable $_) {
            // swallow - view will render without privileged features
        }

        // prepare comments map: postId => [ {id, content, created_at, user, user_id, can_delete}, ... ]
        $commentsMap = [];
        foreach ($posts as $post) {
            $pid = (int)$post->getId();
            try {
                $rawComments = Comment::getByPost($pid);
            } catch (\Throwable $_) {
                $rawComments = [];
            }

            foreach ($rawComments as $c) {
                $cUser = null;
                try { $cUser = $c->getUser(); } catch (\Throwable $_) { $cUser = null; }
                $uid = $c->getUserId();
                $canDelete = $isPrivileged || ($currentUserId !== null && $uid === $currentUserId);

                $commentsMap[$pid][] = [
                    'id' => (int)$c->getId(),
                    'content' => (string)$c->getContent(),
                    'created_at' => (string)$c->getCreatedAt(),
                    'user' => $cUser ? $cUser->getUsername() : 'Neznámy',
                    'user_id' => $uid,
                    'can_delete' => $canDelete,
                    'can_edit' => $canDelete,
                ];
            }
        }

        // prepare presentation-only posts array to avoid any model/logic in the view
        $postsView = [];
        foreach ($posts as $post) {
            $authorName = null;
            try { $author = $post->getUser(); $authorName = $author ? $author->getUsername() : 'Neznámy'; } catch (\Throwable $_) { $authorName = 'Neznámy'; }

            // derive category name from relation if available, otherwise fallback to legacy string
            $catName = '';
            try {
                $cat = $post->getCategoryEntity();
                if ($cat !== null) {
                    $catName = $cat->getName();
                } else {
                    $catName = $post->getCategory();
                }
            } catch (\Throwable $_) {
                $catName = $post->getCategory();
            }

            $postsView[] = [
                'id' => (int)$post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'category' => $catName,
                'created_at' => $post->getCreatedAt(),
                'picture' => $post->getPicture(),
                'author' => $authorName,
            ];
        }

        return $this->html([
            'posts' => $postsView,
            'commentsMap' => $commentsMap,
            'currentUserId' => $currentUserId,
            'isPrivileged' => $isPrivileged,
        ], 'forum');
    }

    /**
     * AJAX: search posts by title (GET param q)
     * Returns JSON array: [{id,title,content,category,created_at},...]
     */
    public function searchPosts(Request $request): \Framework\Http\Responses\JsonResponse
    {
        $q = trim((string)$request->value('q'));
        if ($q === '') {
            return new \Framework\Http\Responses\JsonResponse([]);
        }
        $like = '%' . $q . '%';
        try {
            $posts = Post::getAll('title LIKE ?', [$like], 'created_at DESC', 50);
        } catch (\Throwable $e) {
            return new \Framework\Http\Responses\JsonResponse([]);
        }

        $out = array_map(function($p) {
            $catName = '';
            try {
                $cat = $p->getCategoryEntity();
                if ($cat !== null) {
                    $catName = $cat->getName();
                } else {
                    $catName = $p->getCategory();
                }
            } catch (\Throwable $_) {
                $catName = $p->getCategory();
            }

            return [
                'id' => $p->getId(),
                'title' => $p->getTitle(),
                'content' => $p->getContent(),
                'category' => $catName,
                'created_at' => $p->getCreatedAt(),
            ];
        }, $posts);

        return new \Framework\Http\Responses\JsonResponse($out);
    }

}
