<?php

namespace App\Domains\Administration\Aggregates;

use App\Domains\Administration\Entities\SystemUser;
use App\Domains\Administration\Entities\SystemSetting;
use App\Domains\Administration\ValueObjects\UserRole;
use App\Domains\Administration\ValueObjects\SettingType;
use App\Domains\Administration\DomainEvents\UserCreatedEvent;
use App\Domains\Administration\DomainEvents\UserRoleChangedEvent;
use App\Domains\Administration\DomainEvents\SettingUpdatedEvent;
use Illuminate\Support\Collection;

class AdministrationAggregate
{
    private Collection $users;
    private Collection $settings;
    private int $maxUsers = 10000;

    public function __construct()
    {
        $this->users = collect();
        $this->settings = collect();
    }

    public function createUser(
        string $email,
        string $name,
        UserRole $role,
        int $createdBy
    ): SystemUser {
        $this->ensureCanCreateUser();
        $this->ensureEmailNotExists($email);

        $user = new SystemUser($email, $name, $role, $createdBy);

        $this->users->push($user);

        // Raise domain event
        event(new UserCreatedEvent($user));

        return $user;
    }

    public function updateUserRole(SystemUser $user, UserRole $newRole, int $updatedBy): void
    {
        $oldRole = $user->getRole();
        $user->updateRole($newRole, $updatedBy);

        // Raise domain event if role changed
        if (!$oldRole->equals($newRole)) {
            event(new UserRoleChangedEvent($user, $oldRole, $newRole));
        }
    }

    public function deactivateUser(SystemUser $user): void
    {
        $user->deactivate();
    }

    public function activateUser(SystemUser $user): void
    {
        $user->activate();
    }

    public function recordUserLogin(SystemUser $user): void
    {
        $user->recordLogin();
    }

    public function createSetting(
        string $key,
        $value,
        SettingType $type,
        string $description,
        bool $isPublic,
        int $createdBy
    ): SystemSetting {
        $this->ensureSettingKeyNotExists($key);

        $setting = new SystemSetting($key, $value, $type, $description, $isPublic, $createdBy);

        $this->settings->push($setting);

        return $setting;
    }

    public function updateSetting(SystemSetting $setting, $value, int $updatedBy): void
    {
        $setting->updateValue($value, $updatedBy);

        // Raise domain event
        event(new SettingUpdatedEvent($setting));
    }

    public function getUsersByRole(UserRole $role): Collection
    {
        return $this->users->filter(fn(SystemUser $user) =>
            $user->getRole()->equals($role) && $user->isActive()
        );
    }

    public function getActiveUsers(): Collection
    {
        return $this->users->filter(fn(SystemUser $user) => $user->isActive());
    }

    public function getPublicSettings(): Collection
    {
        return $this->settings->filter(fn(SystemSetting $setting) => $setting->isPublic());
    }

    public function getSettingByKey(string $key): ?SystemSetting
    {
        return $this->settings->first(fn(SystemSetting $setting) => $setting->getKey() === $key);
    }

    public function getUserById(int $userId): ?SystemUser
    {
        return $this->users->first(fn(SystemUser $user) => $user->getId() === $userId);
    }

    public function getUserByEmail(string $email): ?SystemUser
    {
        return $this->users->first(fn(SystemUser $user) => $user->getEmail() === $email);
    }

    private function ensureCanCreateUser(): void
    {
        $activeUsersCount = $this->getActiveUsers()->count();
        if ($activeUsersCount >= $this->maxUsers) {
            throw new \DomainException('Maximum number of users reached');
        }
    }

    private function ensureEmailNotExists(string $email): void
    {
        $existingUser = $this->getUserByEmail($email);
        if ($existingUser) {
            throw new \DomainException('User with this email already exists');
        }
    }

    private function ensureSettingKeyNotExists(string $key): void
    {
        $existingSetting = $this->getSettingByKey($key);
        if ($existingSetting) {
            throw new \DomainException('Setting with this key already exists');
        }
    }
}