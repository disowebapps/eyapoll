<?php

namespace App\Domains\Administration\Repository;

use App\Domains\Administration\Entities\SystemUser;
use App\Domains\Administration\Entities\SystemSetting;
use App\Domains\Administration\ValueObjects\UserRole;
use Illuminate\Support\Collection;

interface AdministrationRepositoryInterface
{
    public function saveUser(SystemUser $user): void;
    public function findUserById(int $id): ?SystemUser;
    public function findUserByEmail(string $email): ?SystemUser;
    public function getUsersByRole(UserRole $role): Collection;
    public function getActiveUsers(): Collection;
    public function updateUserStatus(SystemUser $user): void;

    public function saveSetting(SystemSetting $setting): void;
    public function findSettingById(int $id): ?SystemSetting;
    public function findSettingByKey(string $key): ?SystemSetting;
    public function getPublicSettings(): Collection;
    public function getAllSettings(): Collection;
    public function updateSetting(SystemSetting $setting): void;

    public function getUserStatistics(): array;
    public function getSettingStatistics(): array;
}