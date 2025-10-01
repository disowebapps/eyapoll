<?php

namespace App\Services\Verification;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class KycStatusService
{
    public function syncKycStatus(User $user): void
    {
        DB::transaction(function () use ($user) {
            $hasApprovedDocuments = $user->hasVerifiedDocuments();
            $isApproved = $user->status->value === 'approved';

            $kycCompleted = $hasApprovedDocuments || $isApproved;

            $user->update([
                'verification_data' => array_merge(
                    $user->verification_data ?? [],
                    [
                        'kyc_completed' => $kycCompleted,
                        'kyc_required' => true,
                        'last_sync' => now()->toISOString()
                    ]
                )
            ]);

            // Clear related caches
            \Illuminate\Support\Facades\Cache::forget("user_verified_docs_{$user->id}");
            \Illuminate\Support\Facades\Cache::forget("user_verified_{$user->id}");
        });
    }
    
    public function ensureKycSync(User $user): void
    {
        $lastSync = $user->verification_data['last_sync'] ?? null;
        
        if (!$lastSync || now()->diffInMinutes($lastSync) > 5) {
            $this->syncKycStatus($user);
        }
    }
}
