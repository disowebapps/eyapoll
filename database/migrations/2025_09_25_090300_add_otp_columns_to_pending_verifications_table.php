<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('pending_verifications', 'email_otp')) {
                $table->string('email_otp', 6)->after('phone_number');
            }
            if (!Schema::hasColumn('pending_verifications', 'phone_otp')) {
                $table->string('phone_otp', 6)->after('email_otp');
            }
            if (!Schema::hasColumn('pending_verifications', 'email_verified')) {
                $table->boolean('email_verified')->default(false)->after('phone_otp');
            }
            if (!Schema::hasColumn('pending_verifications', 'phone_verified')) {
                $table->boolean('phone_verified')->default(false)->after('email_verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pending_verifications', function (Blueprint $table) {
            $columns = ['email_otp', 'phone_otp', 'email_verified', 'phone_verified'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('pending_verifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
