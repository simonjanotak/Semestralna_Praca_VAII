<?php
namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;

class PostController extends BaseController
{
    // Zobrazenie formulára na pridanie nového príspevku
    public function add(Request $request): Response
    {
        $currentUserId = $this->getCurrentUserId();
        if ($currentUserId === null) {
            try { $this->app->getSession()->set('flash_message', 'Musíte byť prihlásený, aby ste mohli pridávať príspevky.'); } catch (\Throwable $e) {}
            return $this->redirect($this->url('home.forum'));
        }
        $view = $this->preparePostData(null, []);
        // load categories and pass to view
        $categories = $this->loadCategories();
        return $this->html(['post' => $view['post'], 'errors' => $view['errors'], 'categories' => $categories], 'add');
    }

    public function edit(Request $request): Response
    {
        $id = $request->get('id') ?? $request->post('id') ?? null;
        if ($id === null) {
            return $this->redirect($this->url('post.index'));
        }

        $post = Post::getOne($id);
        if ($post === null) {
            return $this->redirect($this->url('post.index'));
        }

        // Authorization: only owner or admin can edit
        $currentUserId = $this->getCurrentUserId();
        $isAdmin = $this->isCurrentUserAdmin();
        if (!$isAdmin && ($currentUserId === null || $post->getUserId() !== $currentUserId)) {
            // set flash message and redirect to forum
            try { $this->app->getSession()->set('flash_message', 'Nemáte oprávnenie upravovať tento príspevok.'); } catch (\Throwable $e) {}
            return $this->redirect($this->url('home.forum'));
        }

        $view = $this->preparePostData($post, []);
        $categories = $this->loadCategories();
        return $this->html(['post' => $view['post'], 'errors' => $view['errors'], 'categories' => $categories], 'edit');
    }

    private function preparePostData($post, array $errors): array
    {
        $postArr = [
            'title' => '',
            // now use category_id (int or null)
            'category_id' => null,
            'content' => '',
            'picture' => '',
            'id' => null,
        ];

        if (is_array($post)) {
            // merge and ensure category_id exists
            $postArr = array_merge($postArr, $post);
            if (array_key_exists('category', $post) && !array_key_exists('category_id', $post)) {
                // keep backward compatibility if controller passes category string
                $postArr['category'] = $post['category'];
            }
        } elseif ($post instanceof \App\Models\Post) {
            $postArr = [
                'title' => $post->getTitle() ?? '',
                'category_id' => $post->getCategoryId() ?? null,
                'content' => $post->getContent() ?? '',
                'picture' => $post->getPicture() ?? '',
                'id' => $post->getId(),
            ];
        }

        return ['post' => $postArr, 'errors' => $errors];
    }

    // Uloženie príspevku (pridanie alebo editácia)
    public function save(Request $request): Response
    {
        // CSRF protection: accept token in POST body or X-CSRF-Token header
        $csrf = $request->post('csrf_token') ?? $request->server('HTTP_X_CSRF_TOKEN') ?? null;
        $sessionCsrf = $this->app->getSession()->get('csrf_token') ?? null;
        if (!$csrf || !$sessionCsrf || !hash_equals((string)$sessionCsrf, (string)$csrf)) {
            // invalid request - redirect to forum with message
            try { $this->app->getSession()->set('flash_message', 'Neplatný CSRF token.'); } catch (\Throwable $_) {}
            return $this->redirect($this->url('home.forum'));
        }

        $title = trim($request->post('title') ?? '');
        // read category_id instead of category text
        $categoryIdRaw = $request->post('category_id') ?? '';
        $categoryId = ($categoryIdRaw === '' ? null : (int)$categoryIdRaw);
        $content = trim($request->post('content') ?? '');
        $pictureFile = $request->file('picture_file');
        $id = $request->post('id') ?? null;

        // Validácia
        $errors = $this->validateForm($title, $content, $categoryId, $pictureFile);

        if (!empty($errors)) {
            $postData = [
                'id' => $id,
                'title' => $title,
                'category_id' => $categoryId,
                'content' => $content,
            ];
            $categories = $this->loadCategories();
            return $this->html(['post' => $postData, 'errors' => $errors, 'categories' => $categories], 'add');
        }

        // Update alebo nový príspevok
        $post = $id ? Post::getOne($id) : new Post();
        $post->setTitle($title);
        // set normalized category id
        $post->setCategoryId($categoryId);
        $post->setContent($content);

        // Set owner when creating a new post (if user is logged in)
        $currentUserId = $this->getCurrentUserId();
        if (!$id && $currentUserId !== null) {
            $post->setUserId($currentUserId);
        }

        // Upload obrázka
        if ($pictureFile && $pictureFile->isOk() && $pictureFile->getSize() > 0) {

            $uploadDir = dirname(__DIR__, 3) . '/html/public/uploads/';
            $ext = pathinfo($pictureFile->getName(), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'img_' . uniqid() . '.' . $ext;
            $destination = $uploadDir . $filename;

            // store() returns bool - check result and handle failure
            if (!$pictureFile->store($destination)) {
                $postData = [
                    'id' => $id,
                    'title' => $title,
                    'category_id' => $categoryId,
                    'content' => $content,
                ];
                $errors = ['Nahrávanie súboru sa nepodarilo. Skontrolujte práva na priečinok uploads alebo konfiguráciu PHP.'];
                $categories = $this->loadCategories();
                return $this->html(['post' => $postData, 'errors' => $errors, 'categories' => $categories], 'add');
            }

            $post->setPicture('/uploads/' . $filename);
        }


        $post->save();

        return $this->redirect($this->url('home.forum'));
    }

    // Zmazanie príspevku
    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('post.index'));
        }

        // CSRF protection for delete
        $csrf = $request->post('csrf_token') ?? $request->server('HTTP_X_CSRF_TOKEN') ?? null;
        $sessionCsrf = $this->app->getSession()->get('csrf_token') ?? null;
        if (!$csrf || !$sessionCsrf || !hash_equals((string)$sessionCsrf, (string)$csrf)) {
            try { $this->app->getSession()->set('flash_message', 'Neplatný CSRF token.'); } catch (\Throwable $_) {}
            return $this->redirect($this->url('post.index'));
        }

        $id = $request->post('id') ?? null;
        if ($id === null) {
            return $this->redirect($this->url('post.index'));
        }

        $post = Post::getOne($id);
        if ($post === null) {
            return $this->redirect($this->url('post.index'));
        }

        // Authorization: only owner or admin can delete
        $currentUserId = $this->getCurrentUserId();
        $isAdmin = $this->isCurrentUserAdmin();
        if (!$isAdmin && ($currentUserId === null || $post->getUserId() !== $currentUserId)) {
            // set flash message and redirect to forum
            try { $this->app->getSession()->set('flash_message', 'Nemáte oprávnenie zmazať tento príspevok.'); } catch (\Throwable $e) {}
            return $this->redirect($this->url('home.forum'));
        }

        // Odstráni lokálny obrázok, ak existuje
        $pic = $post->getPicture();
        if (!empty($pic) && !str_starts_with($pic, 'http')) {
            $path = dirname(__DIR__, 3) . '/public/' . ltrim($pic, '/');
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $post->delete();
        return $this->redirect($this->url('home.forum'));
    }

    // Zobrazenie všetkých príspevkov
    public function index(Request $request): Response
    {
        $posts = Post::getAll();
        return $this->html(['posts' => $posts]);
    }

    // Pomocná validácia formulára
    private function validateForm(string $title, string $content, $categoryId, $uploaded): array
    {
        $errors = [];

        if (trim($title) === '' || mb_strlen(trim($title)) < 3) {
            $errors[] = 'Názov musí obsahovať aspoň 3 znaky.';
        }
        if (trim($content) === '' || mb_strlen(trim($content)) < 5) {
            $errors[] = 'Text príspevku musí obsahovať aspoň 5 znakov.';
        }
        // categoryId must be numeric and not null
        if ($categoryId === null || !is_int($categoryId) || $categoryId <= 0) {
            $errors[] = 'Vyber kategóriu.';
        }
        // Voliteľný obrázok: kontrolujeme len ak súbor existuje
        // Use isOk() and getSize() from UploadedFile
        if ($uploaded && method_exists($uploaded, 'isOk') && $uploaded->isOk() && $uploaded->getSize() > 0) {
            if ($uploaded->getError() !== UPLOAD_ERR_OK) {
                $errors[] = $this->translateUploadError($uploaded) ?? 'Chyba pri nahrávaní súboru.';
            }
        }

        return $errors;
    }



    private function translateUploadError($uploaded)
    {
        if (!is_object($uploaded)) return null;

        if (method_exists($uploaded, 'getErrorMessage')) {
            return $uploaded->getErrorMessage();
        }

        if (method_exists($uploaded, 'getError')) {
            $err = $uploaded->getError();
            return is_string($err) ? $err : null;
        }

        return null;
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
                // fallback to forwarded call (AppUser->__call)
                if (method_exists($this->user, 'getId')) {
                    return (int)$this->user->getId();
                }
            }
        } catch (\Throwable $e) {
            // ignore and return null
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
                        return ($identity->getRole() === 'admin');
                    }
                    if (property_exists($identity, 'role')) {
                        return ($identity->role === 'admin');
                    }
                }
                if (method_exists($this->user, 'getRole')) {
                    return ($this->user->getRole() === 'admin');
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return false;
    }

    // Load categories as id => name map for views
    private function loadCategories(): array
    {
        $items = Category::getAll();
        $map = [];
        foreach ($items as $c) {
            if (is_object($c) && method_exists($c, 'getId')) {
                $map[$c->getId()] = $c->getName();
            }
        }
        return $map;
    }

}
