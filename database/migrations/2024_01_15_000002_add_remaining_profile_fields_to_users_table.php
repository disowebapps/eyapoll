<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add columns that don't exist
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('users', 'achievements')) {
                $table->text('achievements')->nullable();
            }
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable();
            }
            if (!Schema::hasColumn('users', 'current_position')) {
                $table->string('current_position')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_executive')) {
                $table->boolean('is_executive')->default(false);
            }
            if (!Schema::hasColumn('users', 'executive_order')) {
                $table->integer('executive_order')->nullable();
            }
            if (!Schema::hasColumn('users', 'term_start')) {
                $table->date('term_start')->nullable();
            }
            if (!Schema::hasColumn('users', 'term_end')) {
                $table->date('term_end')->nullable();
            }
            if (!Schema::hasColumn('users', 'twitter_handle')) {
                $table->string('twitter_handle')->nullable();
            }
            if (!Schema::hasColumn('users', 'linkedin_handle')) {
                $table->string('linkedin_handle')->nullable();
            }
            if (!Schema::hasColumn('users', 'instagram_handle')) {
                $table->string('instagram_handle')->nullable();
            }
            if (!Schema::hasColumn('users', 'facebook_handle')) {
                $table->string('facebook_handle')->nullable();
            }
            if (!Schema::hasColumn('users', 'email_public')) {
                $table->boolean('email_public')->default(false);
            }
            if (!Schema::hasColumn('users', 'phone_public')) {
                $table->boolean('phone_public')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'bio', 'achievements', 'profile_image', 'current_position',
                'is_executive', 'executive_order', 'term_start', 'term_end',
                'twitter_handle', 'linkedin_handle', 'instagram_handle', 'facebook_handle',
                'email_public', 'phone_public'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};