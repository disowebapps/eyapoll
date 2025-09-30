<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('email')->unique()->after('uuid');
            $table->string('phone_number', 20)->nullable()->after('email');
            $table->string('password')->after('phone_number');
            $table->string('first_name', 100)->after('password');
            $table->string('last_name', 100)->after('first_name');
            $table->string('id_number_hash')->unique()->after('last_name');
            $table->string('id_salt')->after('id_number_hash');
            $table->timestamp('email_verified_at')->nullable()->after('id_salt');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->json('verification_data')->nullable()->after('phone_verified_at');
            $table->rememberToken()->after('verification_data');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'uuid', 'email', 'phone_number', 'password', 'first_name', 'last_name',
                'id_number_hash', 'id_salt', 'email_verified_at', 'phone_verified_at',
                'verification_data', 'remember_token'
            ]);
        });
    }
};