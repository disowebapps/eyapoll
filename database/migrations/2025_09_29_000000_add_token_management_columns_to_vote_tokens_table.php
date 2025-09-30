<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vote_tokens', function (Blueprint $table) {
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users');
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users');
            $table->timestamp('reassigned_at')->nullable();
            $table->foreignId('reassigned_by')->nullable()->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('vote_tokens', function (Blueprint $table) {
            $table->dropForeign(['issued_by']);
            $table->dropForeign(['revoked_by']);
            $table->dropForeign(['reassigned_by']);
            $table->dropColumn([
                'issued_at',
                'issued_by',
                'revoked_at',
                'revoked_by',
                'reassigned_at',
                'reassigned_by'
            ]);
        });
    }
};