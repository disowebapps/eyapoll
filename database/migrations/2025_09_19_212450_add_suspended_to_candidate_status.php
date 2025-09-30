<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            DB::statement("ALTER TABLE candidates MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'withdrawn', 'suspended') NOT NULL DEFAULT 'pending'");
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            DB::statement("ALTER TABLE candidates MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending'");
        });
    }
};