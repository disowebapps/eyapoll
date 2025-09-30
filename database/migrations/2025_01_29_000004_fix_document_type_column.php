<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->string('document_type', 50)->change();
        });
    }

    public function down()
    {
        Schema::table('candidate_documents', function (Blueprint $table) {
            $table->string('document_type')->change();
        });
    }
};