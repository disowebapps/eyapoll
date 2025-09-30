<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Add user_type column if it doesn't exist
            if (!Schema::hasColumn('audit_logs', 'user_type')) {
                $table->string('user_type')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Re-add the foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Drop user_type column if it exists
            if (Schema::hasColumn('audit_logs', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });
    }
};