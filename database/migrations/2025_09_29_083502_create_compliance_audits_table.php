<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('compliance_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_type');
            $table->json('scope');
            $table->text('findings');
            $table->enum('status', ['passed', 'failed', 'pending'])->default('pending');
            $table->unsignedBigInteger('audited_by')->nullable();
            $table->timestamp('audited_at');
            $table->timestamp('next_audit_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['audit_type', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('compliance_audits');
    }
};