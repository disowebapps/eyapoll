<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('candidate_application_drafts', function (Blueprint $table) {
            $table->json('uploaded_files')->nullable();
        });
    }

    public function down()
    {
        Schema::table('candidate_application_drafts', function (Blueprint $table) {
            $table->dropColumn('uploaded_files');
        });
    }
};