<?php
namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Post;
use App\Models\Category;

class PostController extends BaseController
{
    /**
     * Zamedzí prístup neprihláseným používateľom
     * Používa sa pri add / edit / delete
     */
    private function denyIfCannotAdd(): ?Response
    {
        $currentUserId = $this->getCurrentUserId();

        if ($currentUserId === null) {
            try {
                // Flash správa pre layout
                $this->app->getSession()->set(
                    'flash_message',
                    'Pre túto akciu musíte byť prihlásený.'
                );
            } catch (\Throwable $e) {
                // ignorujeme chyby session
            }

            return $this->redirect($this->url('home.forum'));
        }

        return null;
    }

    /**
     * Zobrazenie formulára na pridanie nového príspevku
     */
    public function add(Request $request): Response
    {
        if ($resp = $this->denyIfCannotAdd()) {
            return $resp;
        }

        // Prázdne dáta formulára
        $view = $this->preparePostData(null, []);
        $categories = $this->loadCategories();

        return $this->html([
            'post'       => $view['post'],
            'errors'     => $view['errors'],
            'categories' => $categories,
            'formAction' => $this->url('post.save'),
        ], 'add');
    }

    /**
     * Zobrazenie formulára na úpravu príspevku
     */
    public function edit(Request $request): Response
    {
        if ($resp = $this->denyIfCannotAdd()) {
            return $resp;
        }

        $id = $request->get('id') ?? $request->post('id');
        if ($id === null) {
            return $this->redirect($this->url('post.index'));
        }

        $post = Post::getOne($id);
        if ($post === null) {
            return $this->redirect($this->url('post.index'));
        }

        // Autorizácia: iba autor alebo admin
        $currentUserId = $this->getCurrentUserId();
        $isAdmin = $this->isCurrentUserAdmin();

        if (!$isAdmin && $post->getUserId() !== $currentUserId) {
            try {
                $this->app->getSession()->set(
                    'flash_message',
                    'Nemáte oprávnenie upravovať tento príspevok.'
                );
            } catch (\Throwable $e) {}

            return $this->redirect($this->url('home.forum'));
        }

        $view = $this->preparePostData($post, []);
        $categories = $this->loadCategories();

        return $this->html([
            'post'       => $view['post'],
            'errors'     => $view['errors'],
            'categories' => $categories,
            'formAction' => $this->url('post.save'),
        ], 'edit');
    }

    /**
     * Pripraví dáta pre view (add/edit)
     */
    private function preparePostData($post, array $errors): array
    {
        $postArr = [
            'title'       => '',
            'category_id' => null,
            'content'     => '',
            'picture'     => '',
            'id'          => null,
        ];

        if ($post instanceof Post) {
            $postArr = [
                'title'       => $post->getTitle() ?? '',
                'category_id' => $post->getCategoryId(),
                'content'     => $post->getContent() ?? '',
                'picture'     => $post->getPicture() ?? '',
                'id'          => $post->getId(),
            ];
        }

        return [
            'post'   => $postArr,
            'errors' => $errors
        ];
    }

    /**
     * Uloženie príspevku (nový alebo editovaný)
     */
    public function save(Request $request): Response
    {
        $id = $request->post('id');

        // Pri vytváraní vyžadujeme prihlásenie
        if (!$id && ($resp = $this->denyIfCannotAdd())) {
            return $resp;
        }

        $title      = trim($request->post('title') ?? '');
        $content    = trim($request->post('content') ?? '');
        $categoryId = (int)($request->post('category_id') ?? 0);
        $file       = $request->file('picture_file');

        // Validácia formulára
        $errors = $this->validateForm($title, $content, $categoryId, $file);

        if (!empty($errors)) {
            return $this->html([
                'post' => [
                    'id'          => $id,
                    'title'       => $title,
                    'category_id' => $categoryId,
                    'content'     => $content,
                ],
                'errors'     => $errors,
                'categories' => $this->loadCategories(),
                'formAction' => $this->url('post.save'),
            ], 'add');
        }

        // Vytvorenie alebo načítanie príspevku
        $post = $id ? Post::getOne($id) : new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setCategoryId($categoryId);

        // Nastavenie autora pri novom príspevku
        if (!$id) {
            $post->setUserId($this->getCurrentUserId());
        }

        // Upload obrázka (ak existuje)
        if ($file && $file->isOk() && $file->getSize() > 0) {
            $dir = dirname(__DIR__, 3) . '/html/public/uploads/';
            $name = 'img_' . uniqid() . '.' . pathinfo($file->getName(), PATHINFO_EXTENSION);

            if ($file->store($dir . $name)) {
                $post->setPicture('/uploads/' . $name);
            }
        }

        $post->save();

        return $this->redirect($this->url('home.forum'));
    }

    /**
     * Zmazanie príspevku
     */
    public function delete(Request $request): Response
    {
        if ($resp = $this->denyIfCannotAdd()) {
            return $resp;
        }

        if (!$request->isPost()) {
            return $this->redirect($this->url('home.forum'));
        }

        $id = $request->post('id');
        $post = Post::getOne($id);

        if (!$post) {
            return $this->redirect($this->url('home.forum'));
        }

        // Autorizácia: autor alebo admin
        if (
            !$this->isCurrentUserAdmin() &&
            $post->getUserId() !== $this->getCurrentUserId()
        ) {
            return $this->redirect($this->url('home.forum'));
        }

        // Odstránenie obrázka zo servera
        if ($post->getPicture()) {
            @unlink(dirname(__DIR__, 3) . '/public/' . ltrim($post->getPicture(), '/'));
        }

        $post->delete();

        return $this->redirect($this->url('home.forum'));
    }

    /**
     * Zoznam príspevkov
     */
    public function index(Request $request): Response
    {
        return $this->html([
            'posts' => Post::getAll()
        ]);
    }

    /**
     * Validácia formulára
     */
    private function validateForm(
        string $title,
        string $content,
        int $categoryId,
               $file
    ): array {
        $errors = [];

        if (mb_strlen($title) < 3) {
            $errors[] = 'Názov musí mať aspoň 3 znaky.';
        }

        if (mb_strlen($content) < 5) {
            $errors[] = 'Text musí mať aspoň 5 znakov.';
        }

        if ($categoryId <= 0) {
            $errors[] = 'Vyber kategóriu.';
        }

        return $errors;
    }

    /**
     * Získa ID aktuálne prihláseného používateľa
     */
    private function getCurrentUserId(): ?int
    {
        try {
            return $this->user->isLoggedIn()
                ? (int)$this->user->getIdentity()->getId()
                : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Zistí, či je používateľ admin
     */
    private function isCurrentUserAdmin(): bool
    {
        try {
            return $this->user->isLoggedIn()
                && $this->user->getIdentity()->getRole() === 'admin';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Načíta kategórie vo formáte id => názov
     */
    private function loadCategories(): array
    {
        $map = [];
        foreach (Category::getAll() as $c) {
            $map[$c->getId()] = $c->getName();
        }
        return $map;
    }
}
