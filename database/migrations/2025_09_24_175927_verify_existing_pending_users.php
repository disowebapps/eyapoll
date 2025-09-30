<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Mark all pending users as email/phone verified since they completed registration
        User::where('status', 'pending')
            ->whereNull('email_verified_at')
            ->update([
                'email_verified_at' => now(),
                'phone_verified_at' => now()
            ]);
    }

    public function down(): void
    {
        // Reverse the verification for pending users
        User::where('status', 'pending')
            ->update([
                'email_verified_at' => null,
                'phone_verified_at' => null
            ]);
    }
};