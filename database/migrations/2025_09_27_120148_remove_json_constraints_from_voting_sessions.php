<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('voting_sessions', function (Blueprint $table) {
            // Change columns to longtext without JSON constraints
            $table->longText('selections')->nullable()->change();
            $table->longText('progress')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('voting_sessions', function (Blueprint $table) {
            // Restore JSON constraints
            $table->json('selections')->nullable()->change();
            $table->json('progress')->nullable()->change();
        });
    }
};