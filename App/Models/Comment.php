<?php
namespace App\Models;

use Framework\Core\Model;

class Comment extends Model
{

    protected static ?string $tableName = 'comments';

    protected static array $columnsMap = [
        'postId'    => 'post_id',
        'userId'    => 'user_id',
        'createdAt' => 'created_at',
        'created'   => 'created_at',
    ];

    protected ?int $id = null;
    protected ?int $post_id = null;
    protected ?int $user_id = null;
    protected string $content = '';
    protected ?string $created_at = null;

    // AI helper constructor
    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getPostId(): ?int { return $this->post_id; }
    public function setPostId(?int $id): void { $this->post_id = $id; }
    public function getUserId(): ?int { return $this->user_id; }
    public function setUserId(?int $id): void { $this->user_id = $id; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $c): void { $this->content = $c; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    /**  AI pomocne funkcie
     * Return owner User of this Comment
     * @return \App\Models\User|null
     */
    public function getUser(): ?User
    {
        if ($this->user_id === null) return null;
        return User::getOne($this->user_id);
    }

    /**
     * Return the Post this comment belongs to
     * @return \App\Models\Post|null
     */
    public function getPost(): ?Post
    {
        if ($this->post_id === null) return null;
        return Post::getOne($this->post_id);
    }

    /**
     * Convenience: load comments for a post
     * @param int $postId
     * @return static[]
     */
    public static function getByPost(int $postId): array
    {
        return static::getAll('post_id = ?', [$postId], 'created_at DESC');
    }
}
