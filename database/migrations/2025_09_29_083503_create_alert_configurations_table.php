<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alert_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type');
            $table->json('conditions');
            $table->boolean('enabled')->default(true);
            $table->json('notification_channels');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['alert_type', 'enabled']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('alert_configurations');
    }
};