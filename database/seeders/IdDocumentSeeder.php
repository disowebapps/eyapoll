<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Auth\IdDocument;
use App\Enums\Auth\UserStatus;

class IdDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $approvedUsers = User::where('status', UserStatus::APPROVED)->get();

        foreach ($approvedUsers as $user) {
            IdDocument::create([
                'user_id' => $user->id,
                'document_type' => 'national_id',
                'file_path' => 'documents/sample_id_' . $user->id . '.jpg',
                'file_hash' => hash('sha256', 'sample_document_' . $user->id),
                'status' => 'approved',
                'reviewed_by' => null,
                'reviewed_at' => now(),
            ]);
        }
    }
}