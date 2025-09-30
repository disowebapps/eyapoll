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
        Schema::table('elections', function (Blueprint $table) {
            $table->timestamp('certified_at')->nullable();
            $table->foreignId('certified_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('certification_hash')->nullable();
            $table->json('certification_data')->nullable();

            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('finalization_hash')->nullable();
            $table->json('finalization_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropForeign(['certified_by']);
            $table->dropForeign(['finalized_by']);
            $table->dropColumn([
                'certified_at',
                'certified_by',
                'certification_hash',
                'certification_data',
                'finalized_at',
                'finalized_by',
                'finalization_hash',
                'finalization_data',
            ]);
        });
    }
};
