<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            // Drop existing foreign key if exists
            $table->dropForeign(['updated_by']);
            $table->unsignedBigInteger('updated_by')->nullable()->change();
            // Add new foreign key
            $table->foreign('updated_by')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};