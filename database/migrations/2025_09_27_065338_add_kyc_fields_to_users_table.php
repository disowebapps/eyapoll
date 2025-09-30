<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable();
            $table->string('highest_qualification')->nullable();
            $table->enum('location_type', ['home', 'abroad'])->nullable();
            $table->string('abroad_city')->nullable();
            $table->string('current_occupation')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('field_of_study')->nullable();
            $table->enum('student_status', ['current_student', 'graduate', 'dropout'])->nullable();
            $table->enum('employment_status', ['employed', 'unemployed', 'self_employed', 'student'])->nullable();
            $table->text('skills')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'highest_qualification', 
                'location_type',
                'abroad_city',
                'current_occupation',
                'marital_status',
                'field_of_study',
                'student_status',
                'employment_status',
                'skills'
            ]);
        });
    }
};