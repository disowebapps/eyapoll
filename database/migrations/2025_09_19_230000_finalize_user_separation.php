<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update admins table to remove role column (admins are always admins)
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['role']);
        });

        // Add foreign key constraints to reference admins table
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('approved_by')->references('id')->on('admins')->onDelete('set null');
        });

        // Update vote_tokens to reference both users and candidate_users
        Schema::table('vote_tokens', function (Blueprint $table) {
            $table->string('user_type')->default('voter');
            $table->index(['user_id', 'user_type']);
        });

        // Update audit_logs to handle multiple user types
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('user_type')->default('admin');
            $table->index(['user_id', 'user_type']);
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->enum('role', ['admin', 'observer'])->default('admin');
        });

        Schema::table('vote_tokens', function (Blueprint $table) {
            $table->dropColumn(['user_type']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['user_type']);
        });

        // Restore original foreign keys
        Schema::table('elections', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};