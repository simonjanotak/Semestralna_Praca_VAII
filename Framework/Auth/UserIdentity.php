<?php

namespace Framework\Auth;

use Framework\Core\IIdentity;

/**
 * Serializable identity stored in session after login.
 */
class UserIdentity implements IIdentity
{
    public int $id;
    public string $username;
    public string $role;
    public string $email;

    public function __construct(int $id, string $username, string $role = 'user', string $email = '')
    {
        $this->id = $id;
        $this->username = $username;
        $this->role = $role;
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->username;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    // Ensure safe serialization for storing in session
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
            'email' => $this->email,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->username = (string)($data['username'] ?? '');
        $this->role = (string)($data['role'] ?? 'user');
        $this->email = (string)($data['email'] ?? '');
    }
}

