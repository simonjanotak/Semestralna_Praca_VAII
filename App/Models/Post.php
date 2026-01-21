<?php
namespace App\Models;

use Framework\Core\Model;

class Post extends Model
{
    protected static ?string $tableName = 'posts';

    protected ?int $id = null;
    protected ?int $user_id = null;
    protected string $picture = '';
    protected string $title = '';
    protected string $content = '';

    protected string $category = '';


    protected ?int $category_id = null;
    protected ?string $created_at = null;
    protected ?string $updated_at = null;

    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): void { $this->category = $category; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): void { $this->content = $content; }
    public function getId(): ?int { return $this->id; }
    public function getPicture(): string { return $this->picture; }
    public function setPicture(string $picture): void { $this->picture = $picture; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function setUpdatedAt(?string $dt): void { $this->updated_at = $dt; }

    public function getCategoryId(): ?int { return $this->category_id; }
    public function setCategoryId(?int $id): void { $this->category_id = $id; }

    public function getUserId(): ?int { return $this->user_id; }
    public function setUserId(?int $userId): void { $this->user_id = $userId; }

    // AI pomocne funkcie
    /**
     * Return owner User of this Post
     * @return \App\Models\User|null
     */
    public function getUser(): ?User
    {
        if ($this->user_id === null) return null;
        return User::getOne($this->user_id);
    }

    /**
     * Return Category entity (if category_id is set)
     * Falls back to null if category_id is not available.
     * @return \App\Models\Category|null
     */
    public function getCategoryEntity(): ?Category
    {
        if ($this->category_id === null) return null;
        return Category::getOne($this->category_id);
    }

    /**
     * Return comments for this post (1:N)
     * @return \App\Models\Comment[]
     */
    public function getComments(): array
    {
        if ($this->id === null) return [];
        return Comment::getByPost($this->id);
    }
}
