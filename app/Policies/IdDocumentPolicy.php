<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Auth\IdDocument;
use App\Enums\Auth\UserRole;
use App\Enums\Auth\UserStatus;

class IdDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    public function view(User $user, IdDocument $idDocument): bool
    {
        return $user->role === UserRole::ADMIN || $user->id === $idDocument->user_id;
    }

    public function create(User $user): bool
    {
        return $user->status === UserStatus::PENDING && !$user->idDocuments()->exists();
    }

    public function update(User $user, IdDocument $idDocument): bool
    {
        return $user->role === UserRole::ADMIN || 
               ($user->id === $idDocument->user_id && $idDocument->status === 'pending');
    }

    public function approve(User $user, IdDocument $idDocument): bool
    {
        return $user->role === UserRole::ADMIN && $idDocument->status === 'pending';
    }

    public function reject(User $user, IdDocument $idDocument): bool
    {
        return $user->role === UserRole::ADMIN && $idDocument->status === 'pending';
    }

    public function delete(User $user, IdDocument $idDocument): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}