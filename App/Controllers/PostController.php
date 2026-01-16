<?php
namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Post;

class PostController extends BaseController
{
    // Zobrazenie formulára na pridanie nového príspevku
    public function add(Request $request): Response
    {
        $view = $this->preparePostData(null, []);
        return $this->html(['post' => $view['post'], 'errors' => $view['errors']], 'add');
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

        $view = $this->preparePostData($post, []);
        return $this->html(['post' => $view['post'], 'errors' => $view['errors']], 'edit');
    }

    private function preparePostData($post, array $errors): array
    {
        $postArr = [
            'title' => '',
            'category' => '',
            'content' => '',
            'picture' => '',
            'id' => null,
        ];

        if (is_array($post)) {
            $postArr = array_merge($postArr, $post);
        } elseif ($post instanceof \App\Models\Post) {
            $postArr = [
                'title' => $post->getTitle() ?? '',
                'category' => $post->getCategory() ?? '',
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
        $title = trim($request->post('title') ?? '');
        $category = trim($request->post('category') ?? '');
        $content = trim($request->post('content') ?? '');
        $pictureFile = $request->file('picture_file');
        $id = $request->post('id') ?? null;

        // Validácia
        $errors = $this->validateForm($title, $content, $category, $pictureFile);

        if (!empty($errors)) {
            $postData = [
                'id' => $id,
                'title' => $title,
                'category' => $category,
                'content' => $content,
            ];
            return $this->html(['post' => $postData, 'errors' => $errors], 'add');
        }

        // Update alebo nový príspevok
        $post = $id ? Post::getOne($id) : new Post();
        $post->setTitle($title);
        $post->setCategory($category);
        $post->setContent($content);

        // Upload obrázka
        if ($pictureFile && $pictureFile->isOk() && $pictureFile->getSize() > 0) {

            $uploadDir = dirname(__DIR__, 3) . '/public/uploads/';

            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0755, true)) {
                    $postData = [
                        'id' => $id,
                        'title' => $title,
                        'category' => $category,
                        'content' => $content,
                    ];
                    $errors = ['Nepodarilo sa vytvoriť priečinok pre nahrávanie súborov. Skontrolujte práva na serveri.'];
                    return $this->html(['post' => $postData, 'errors' => $errors], 'add');
                }
            }

            $ext = pathinfo($pictureFile->getName(), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'img_' . uniqid() . '.' . $ext;
            $destination = $uploadDir . $filename;

            // store() returns bool - check result and handle failure
            if (!$pictureFile->store($destination)) {
                $postData = [
                    'id' => $id,
                    'title' => $title,
                    'category' => $category,
                    'content' => $content,
                ];
                $errors = ['Nahrávanie súboru sa nepodarilo. Skontrolujte práva na priečinok uploads alebo konfiguráciu PHP.'];
                return $this->html(['post' => $postData, 'errors' => $errors], 'add');
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

        $id = $request->post('id') ?? null;
        if ($id === null) {
            return $this->redirect($this->url('post.index'));
        }

        $post = Post::getOne($id);
        if ($post === null) {
            return $this->redirect($this->url('post.index'));
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
    private function validateForm(string $title, string $content, string $category, $uploaded): array
    {
        $errors = [];

        if (trim($title) === '' || mb_strlen(trim($title)) < 3) {
            $errors[] = 'Názov musí obsahovať aspoň 3 znaky.';
        }
        if (trim($content) === '' || mb_strlen(trim($content)) < 5) {
            $errors[] = 'Text príspevku musí obsahovať aspoň 5 znakov.';
        }
        if (trim($category) === '') {
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



}
