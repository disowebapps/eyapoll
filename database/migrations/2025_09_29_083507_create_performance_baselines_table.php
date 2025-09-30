<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('performance_baselines', function (Blueprint $table) {
            $table->id();
            $table->string('component');
            $table->string('metric_name');
            $table->decimal('baseline_value', 10, 2);
            $table->decimal('threshold_high', 10, 2)->nullable();
            $table->decimal('threshold_low', 10, 2)->nullable();
            $table->timestamp('measured_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['component', 'metric_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('performance_baselines');
    }
};