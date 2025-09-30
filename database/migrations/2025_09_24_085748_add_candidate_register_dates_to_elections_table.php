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
            $table->timestamp('candidate_register_starts')->nullable()->after('voter_register_ends');
            $table->timestamp('candidate_register_ends')->nullable()->after('candidate_register_starts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn(['candidate_register_starts', 'candidate_register_ends']);
        });
    }
};
