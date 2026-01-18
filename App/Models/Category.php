<?php
namespace App\Models;

use Framework\Core\Model;

class Category extends Model
{
    protected static ?string $tableName = 'categories';

    protected ?int $id = null;
    protected string $name = '';
    protected string $slug = '';
    protected ?string $created_at = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $n): void { $this->name = $n; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $s): void { $this->slug = $s; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    /**
     * Return posts in this category
     * @return \App\Models\Post[]
     */
    public function getPosts(): array
    {
        if ($this->id === null) return [];
        return Post::getAll('category_id = ?', [$this->id]);
    }

    /**
     * Find category by slug
     */
    public static function findBySlug(string $slug): ?static
    {
        $items = static::getAll('slug = ?', [$slug], null, 1);
        return $items[0] ?? null;
    }
}

