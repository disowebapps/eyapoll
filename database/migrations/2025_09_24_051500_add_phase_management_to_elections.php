<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->string('phase')->default('registration')->after('status');
            $table->boolean('voter_register_locked')->default(false)->after('results_published');
            $table->boolean('voting_closed')->default(false)->after('voter_register_locked');
        });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn(['phase', 'voter_register_locked', 'voting_closed']);
        });
    }
};