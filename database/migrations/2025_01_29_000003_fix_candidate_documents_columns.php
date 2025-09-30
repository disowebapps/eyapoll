<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->text('file_path')->change();
        });
    }

    public function down()
    {
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->string('file_path')->change();
        });
    }
};