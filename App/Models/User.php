<?php
namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    // explicitly set table name
    protected static ?string $tableName = 'users';

    // properties must match DB column names (DefaultConventions)
    protected ?int $id = null;
    protected string $username = '';
    protected string $email = '';
    protected string $password_hash = '';
    protected string $role = 'user';
    protected ?string $created_at = null;
    protected ?string $updated_at = null;

    // --- getters / setters ---
    public function getId(): ?int { return $this->id; }

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): void { $this->username = $username; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }


    public function getPasswordHash(): string { return $this->password_hash; }
    public function setPasswordHash(string $hash): void { $this->password_hash = $hash; }

    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $password): bool
    {
        if ($this->password_hash === '') return false;
        return password_verify($password, $this->password_hash);
    }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): void
    {
        $allowed = ['user', 'moderator', 'admin'];
        if (!in_array($role, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid role: ' . $role);
        }
        $this->role = $role;
    }

    public function getCreatedAt(): ?string { return $this->created_at; }
    public function setCreatedAt(?string $createdAt): void { $this->created_at = $createdAt; }

    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function setUpdatedAt(?string $updatedAt): void { $this->updated_at = $updatedAt; }

    // Relations
    /**
     * Return posts belonging to this user (1:N)
     * @return \App\Models\Post[]
     */
    public function getPosts(): array
    {
        if ($this->id === null) return [];
        return Post::getAll('user_id = ?', [$this->id]);
    }

    /**
     * Find user by email. Returns User instance or null.
     * @param string $email
     * @return static|null
     */
    public static function findByEmail(string $email): ?static
    {
        $items = static::getAll('email = ?', [$email], null, 1);
        return $items[0] ?? null;
    }

    /**
     * Find user by username. Returns User instance or null.
     * @param string $username
     * @return static|null
     */
    public static function findByUsername(string $username): ?static
    {
        $items = static::getAll('username = ?', [$username], null, 1);
        return $items[0] ?? null;
    }

    /**
     * Check whether a username exists (case-insensitive).
     * @param string $username
     * @return bool
     */
    public static function existsByUsername(string $username): bool
    {
        // Use LOWER(...) to ensure case-insensitive comparison regardless of DB collation
        $count = static::getCount('LOWER(username) = LOWER(?)', [$username]);
        return $count > 0;
    }
}
