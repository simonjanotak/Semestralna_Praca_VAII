<?php
namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    // explicitly set table name
    protected static ?string $tableName = 'users';

    protected ?int $id = null;
    protected string $username = '';
    protected string $email = '';
    protected string $passwordHash = '';
    protected string $role = 'user';
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    // --- getters / setters ---
    public function getId(): ?int { return $this->id; }

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): void { $this->username = $username; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    // passwordHash is stored in DB as `password_hash` (snake_case) and maps to $passwordHash
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $hash): void { $this->passwordHash = $hash; }

    // convenience: set raw password (will be hashed)
    public function setPassword(string $password): void
    {
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
    }

    // verify raw password against stored hash
    public function verifyPassword(string $password): bool
    {
        if ($this->passwordHash === '') return false;
        return password_verify($password, $this->passwordHash);
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

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function setUpdatedAt(?string $updatedAt): void { $this->updatedAt = $updatedAt; }
}

