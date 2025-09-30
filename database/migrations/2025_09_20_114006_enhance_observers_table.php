<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observers', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->unique()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('phone_number');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->enum('type', ['organization', 'independent'])->default('independent')->after('phone_verified_at');
            $table->string('organization_name')->nullable()->after('type');
            $table->text('organization_address')->nullable()->after('organization_name');
            $table->string('certification_number')->nullable()->after('organization_address');
            $table->json('observer_privileges')->nullable()->after('certification_number');
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended', 'revoked'])->default('pending')->change();
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null')->after('approved_at');
            $table->timestamp('suspended_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('suspended_by')->nullable()->after('suspended_at');
            $table->text('suspension_reason')->nullable()->after('suspended_by');
            $table->timestamp('revoked_at')->nullable()->after('suspension_reason');
            $table->unsignedBigInteger('revoked_by')->nullable()->after('revoked_at');
            $table->text('revocation_reason')->nullable()->after('revoked_by');
            $table->rememberToken()->after('revocation_reason');
            $table->softDeletes()->after('updated_at');
            
            $table->foreign('suspended_by')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('revoked_by')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('observers', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropForeign(['revoked_by']);
            $table->dropColumn([
                'type', 'organization_name', 'organization_address',
                'certification_number', 'observer_privileges',
                'suspended_at', 'suspended_by', 'suspension_reason',
                'revoked_at', 'revoked_by', 'revocation_reason'
            ]);
        });
    }
};