<?php
namespace App\Models;

use Framework\Core\Model;

class Post extends Model
{
    protected static ?string $tableName = 'posts';

    protected ?int $id = null;
    // foreign key to users.id
    protected ?int $user_id = null;
    protected string $picture = '';
    protected string $title = '';
    protected string $content = '';
    protected string $category = '';
    protected ?string $created_at = null;

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

    // user_id accessors
    public function getUserId(): ?int { return $this->user_id; }
    public function setUserId(?int $userId): void { $this->user_id = $userId; }

    // Relations
    /**
     * Return owner User of this Post
     * @return \App\Models\User|null
     */
    public function getUser(): ?User
    {
        if ($this->user_id === null) return null;
        return User::getOne($this->user_id);
    }
}
