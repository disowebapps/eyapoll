<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('threat_scores', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->string('entity_id');
            $table->decimal('score', 5, 2);
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamp('calculated_at');
            $table->json('factors')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'risk_level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('threat_scores');
    }
};