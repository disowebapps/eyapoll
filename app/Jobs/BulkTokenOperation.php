<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Election\Election;
use App\Services\TokenManagement\TokenManagementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkTokenOperation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $operation,
        private array $userIds,
        private int $electionId,
        private int $adminId
    ) {}

    public function handle(TokenManagementService $tokenService): void
    {
        $admin = User::find($this->adminId);
        $election = Election::find($this->electionId);
        $users = User::whereIn('id', $this->userIds)->get();

        $successCount = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                match($this->operation) {
                    'issue' => $tokenService->issueToken($user, $election, $admin),
                    default => throw new \InvalidArgumentException("Unknown operation: {$this->operation}")
                };
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "User {$user->id}: {$e->getMessage()}";
                Log::error("Bulk token operation failed for user {$user->id}", [
                    'operation' => $this->operation,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Bulk token operation completed", [
            'operation' => $this->operation,
            'total_users' => count($this->userIds),
            'successful' => $successCount,
            'errors' => count($errors)
        ]);
    }
}