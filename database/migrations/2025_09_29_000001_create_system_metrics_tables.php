<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name');
            $table->decimal('value', 10, 2);
            $table->string('unit')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['metric_name', 'recorded_at']);
        });

        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('severity')->default('info');
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamps();
            
            $table->index(['event_type', 'severity']);
        });

        Schema::create('compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type');
            $table->json('data');
            $table->string('status')->default('generated');
            $table->timestamp('generated_at');
            $table->timestamps();
        });

        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type');
            $table->string('severity');
            $table->string('title');
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->boolean('acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamps();
            
            $table->index(['alert_type', 'severity']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_alerts');
        Schema::dropIfExists('compliance_reports');
        Schema::dropIfExists('security_events');
        Schema::dropIfExists('system_metrics');
    }
};