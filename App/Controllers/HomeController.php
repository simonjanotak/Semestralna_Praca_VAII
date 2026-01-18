<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Post;

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
        // fetch all posts and pass them to the forum view
        $posts = Post::getAll();
        return $this->html(['posts' => $posts], 'forum');
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
            return [
                'id' => $p->getId(),
                'title' => $p->getTitle(),
                'content' => $p->getContent(),
                'category' => $p->getCategory(),
                'created_at' => $p->getCreatedAt(),
            ];
        }, $posts);

        return new \Framework\Http\Responses\JsonResponse($out);
    }

}
