<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_based_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('metric_name');
            $table->decimal('value', 10, 2);
            $table->timestamp('recorded_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['role', 'metric_name', 'recorded_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_based_metrics');
    }
};