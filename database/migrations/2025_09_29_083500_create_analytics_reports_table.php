<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type');
            $table->json('parameters')->nullable();
            $table->longText('data');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['report_type', 'generated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_reports');
    }
};