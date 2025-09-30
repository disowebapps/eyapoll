<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['users', 'candidates', 'admins'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->timestamp('suspended_at')->nullable()->after('approved_by');
                    $table->unsignedBigInteger('suspended_by')->nullable()->after('suspended_at');
                    $table->text('suspension_reason')->nullable()->after('suspended_by');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['users', 'candidates', 'admins'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn(['suspended_at', 'suspended_by', 'suspension_reason']);
                });
            }
        }
    }
};