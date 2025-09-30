<?php

namespace App\Domains\Administration\ValueObjects;

class UserRole
{
    private string $role;

    private const VALID_ROLES = ['admin', 'moderator', 'observer', 'candidate', 'voter'];

    public function __construct(string $role)
    {
        if (!in_array($role, self::VALID_ROLES)) {
            throw new \InvalidArgumentException('Invalid user role: ' . $role);
        }
        $this->role = $role;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function equals(UserRole $other): bool
    {
        return $this->role === $other->role;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = [
            'admin' => ['*'],
            'moderator' => ['manage_candidates', 'manage_elections', 'view_reports'],
            'observer' => ['view_elections', 'view_reports'],
            'candidate' => ['apply_for_election', 'view_own_applications'],
            'voter' => ['vote', 'view_elections']
        ];

        $rolePermissions = $permissions[$this->role] ?? [];

        return in_array('*', $rolePermissions) || in_array($permission, $rolePermissions);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function __toString(): string
    {
        return $this->role;
    }
}