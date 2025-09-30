<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->timestamp('voter_register_starts')->nullable()->after('voting_closed');
            $table->timestamp('voter_register_ends')->nullable()->after('voter_register_starts');
            $table->timestamp('voter_register_published')->nullable()->after('voter_register_ends');
        });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn(['voter_register_starts', 'voter_register_ends', 'voter_register_published']);
        });
    }
};