<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Post;
use App\Models\Comment;

/**
 * HomeController
 *
 * Controller pre verejne dostupné stránky:
 * - úvodná stránka
 * - kontakt
 * - fórum
 * - vyhľadávanie
 */
class HomeController extends BaseController
{
    /**
     * Autorizácia akcií controlleru
     *
     * V tomto prípade povoľujeme VŠETKY akcie každému
     * (prihlásenie sa rieši inde – v iných controlleroch)
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Úvodná stránka webu
     */
    public function index(Request $request): Response
    {
        return $this->html();
    }

    /**
     * Kontaktná stránka
     */
    public function contact(Request $request): Response
    {
        return $this->html();
    }

    /**
     * Fórum – zoznam príspevkov + komentáre
     */
    public function forum(Request $request): Response
    {
        // Načítanie všetkých príspevkov
        $posts = Post::getAll();

        // Zistenie prihláseného používateľa a jeho práv
        $currentUserId = null;
        $isPrivileged = false; // admin alebo moderátor

        try {
            if (isset($this->user) && $this->user->isLoggedIn()) {
                $identity = $this->user->getIdentity();

                if (is_object($identity)) {
                    // ID používateľa
                    if (method_exists($identity, 'getId')) {
                        $currentUserId = (int)$identity->getId();
                    } elseif (property_exists($identity, 'id')) {
                        $currentUserId = (int)$identity->id;
                    }

                    // Rola používateľa
                    if (method_exists($identity, 'getRole')) {
                        $role = $identity->getRole();
                    } elseif (property_exists($identity, 'role')) {
                        $role = $identity->role ?? null;
                    }
                }

                // Admin alebo moderátor
                if (isset($role) && ($role === 'admin' || $role === 'moderator')) {
                    $isPrivileged = true;
                }
            }
        } catch (\Throwable $_) {
            // ak sa niečo pokazí, fórum sa zobrazí bez extra práv
        }

        /**
         * Príprava komentárov:
         * pole vo formáte:
         * postId => [ komentár1, komentár2, ... ]
         */
        $commentsMap = [];

        foreach ($posts as $post) {
            $pid = (int)$post->getId();

            try {
                $rawComments = Comment::getByPost($pid);
            } catch (\Throwable $_) {
                $rawComments = [];
            }

            foreach ($rawComments as $c) {
                // Autor komentára
                try {
                    $cUser = $c->getUser();
                } catch (\Throwable $_) {
                    $cUser = null;
                }

                $uid = $c->getUserId();

                // Právo na zmazanie / úpravu
                $canDelete = $isPrivileged || (
                        $currentUserId !== null && $uid === $currentUserId
                    );

                $commentsMap[$pid][] = [
                    'id'         => (int)$c->getId(),
                    'content'    => (string)$c->getContent(),
                    'created_at'=> (string)$c->getCreatedAt(),
                    'user'       => $cUser ? $cUser->getUsername() : 'Neznámy',
                    'user_id'    => $uid,
                    'can_delete' => $canDelete,
                    'can_edit'   => $canDelete,
                ];
            }
        }

        /**
         * Príprava príspevkov pre VIEW
         * (žiadna logika, len čisté dáta)
         */
        $postsView = [];

        foreach ($posts as $post) {
            // Autor príspevku
            try {
                $author = $post->getUser();
                $authorName = $author ? $author->getUsername() : 'Neznámy';
            } catch (\Throwable $_) {
                $authorName = 'Neznámy';
            }

            // Názov kategórie
            try {
                $cat = $post->getCategoryEntity();
                $catName = $cat ? $cat->getName() : $post->getCategory();
            } catch (\Throwable $_) {
                $catName = $post->getCategory();
            }

            $postsView[] = [
                'id'         => (int)$post->getId(),
                'title'      => $post->getTitle(),
                'content'    => $post->getContent(),
                'category'   => $catName,
                'created_at'=> $post->getCreatedAt(),
                'picture'    => $post->getPicture(),
                'author'     => $authorName,
            ];
        }

        // Odoslanie dát do view
        return $this->html([
            'posts'          => $postsView,
            'commentsMap'    => $commentsMap,
            'currentUserId'  => $currentUserId,
            'isPrivileged'   => $isPrivileged,
        ], 'forum');
    }

    /**
     * Stránka s testami áut
     */
    public function carTests(Request $request): Response
    {
        return $this->html([], 'carTests');
    }

    /**
     * AJAX vyhľadávanie príspevkov podľa názvu
     *
     * GET parameter: q
     * Vracia JSON pole príspevkov
     */
    public function searchPosts(Request $request): \Framework\Http\Responses\JsonResponse
    {
        $q = trim((string)$request->value('q'));

        if ($q === '') {
            return $this->json([]);
        }

        try {
            $posts = Post::getAll(
                'title LIKE ?',
                ['%' . $q . '%'],
                'created_at DESC',
                50
            );
        } catch (\Throwable $e) {
            return $this->json([]);
        }

        $out = array_map(function ($p) {
            try {
                $cat = $p->getCategoryEntity();
                $catName = $cat ? $cat->getName() : $p->getCategory();
            } catch (\Throwable $_) {
                $catName = $p->getCategory();
            }

            return [
                'id'         => $p->getId(),
                'title'      => $p->getTitle(),
                'content'    => $p->getContent(),
                'category'   => $catName,
                'created_at'=> $p->getCreatedAt(),
            ];
        }, $posts);

        return $this->json($out);
    }
}
