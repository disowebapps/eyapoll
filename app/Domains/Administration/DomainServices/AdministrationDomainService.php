<?php

namespace App\Domains\Administration\DomainServices;

use App\Domains\Administration\Aggregates\AdministrationAggregate;
use App\Domains\Administration\ValueObjects\UserRole;
use App\Domains\Administration\ValueObjects\SettingType;
use Illuminate\Support\Collection;

class AdministrationDomainService
{
    private AdministrationAggregate $aggregate;

    public function __construct(AdministrationAggregate $aggregate)
    {
        $this->aggregate = $aggregate;
    }

    public function createUser(
        string $email,
        string $name,
        string $role,
        int $createdBy
    ): array {
        $userRole = new UserRole($role);
        $user = $this->aggregate->createUser($email, $name, $userRole, $createdBy);

        return [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'role' => $user->getRole()->getRole(),
            'is_active' => $user->isActive()
        ];
    }

    public function updateUserRole(int $userId, string $newRole, int $updatedBy): void
    {
        $user = $this->aggregate->getUserById($userId);
        if (!$user) {
            throw new \DomainException('User not found');
        }

        $userRole = new UserRole($newRole);
        $this->aggregate->updateUserRole($user, $userRole, $updatedBy);
    }

    public function deactivateUser(int $userId): void
    {
        $user = $this->aggregate->getUserById($userId);
        if (!$user) {
            throw new \DomainException('User not found');
        }

        $this->aggregate->deactivateUser($user);
    }

    public function activateUser(int $userId): void
    {
        $user = $this->aggregate->getUserById($userId);
        if (!$user) {
            throw new \DomainException('User not found');
        }

        $this->aggregate->activateUser($user);
    }

    public function createSystemSetting(
        string $key,
        $value,
        string $type,
        string $description,
        bool $isPublic,
        int $createdBy
    ): array {
        $settingType = new SettingType($type);
        $setting = $this->aggregate->createSetting($key, $value, $settingType, $description, $isPublic, $createdBy);

        return [
            'setting_id' => $setting->getId(),
            'key' => $setting->getKey(),
            'value' => $setting->getValue(),
            'type' => $setting->getType()->getType(),
            'is_public' => $setting->isPublic()
        ];
    }

    public function updateSystemSetting(string $key, $value, int $updatedBy): void
    {
        $setting = $this->aggregate->getSettingByKey($key);
        if (!$setting) {
            throw new \DomainException('Setting not found');
        }

        $this->aggregate->updateSetting($setting, $value, $updatedBy);
    }

    public function getSystemConfiguration(): array
    {
        $publicSettings = $this->aggregate->getPublicSettings();
        $activeUsers = $this->aggregate->getActiveUsers();

        return [
            'public_settings' => $publicSettings->map(function ($setting) {
                return [
                    'key' => $setting->getKey(),
                    'value' => $setting->getFormattedValue(),
                    'description' => $setting->getDescription()
                ];
            }),
            'user_statistics' => [
                'total_active_users' => $activeUsers->count(),
                'users_by_role' => $this->getUsersCountByRole()
            ]
        ];
    }

    public function validateUserPermissions(int $userId, string $permission): bool
    {
        $user = $this->aggregate->getUserById($userId);
        if (!$user) {
            return false;
        }

        return $user->hasPermission($permission);
    }

    public function getUserManagementOverview(): array
    {
        $activeUsers = $this->aggregate->getActiveUsers();

        return [
            'total_users' => $activeUsers->count(),
            'users_by_role' => $this->getUsersCountByRole(),
            'recent_users' => $activeUsers->sortByDesc('createdAt')->take(5)->map(function ($user) {
                return [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()->getRole(),
                    'created_at' => $user->getCreatedAt()->toDateTimeString()
                ];
            })
        ];
    }

    private function getUsersCountByRole(): array
    {
        $counts = [];
        $roles = ['admin', 'moderator', 'observer', 'candidate', 'voter'];

        foreach ($roles as $role) {
            $users = $this->aggregate->getUsersByRole(new UserRole($role));
            $counts[$role] = $users->count();
        }

        return $counts;
    }
}