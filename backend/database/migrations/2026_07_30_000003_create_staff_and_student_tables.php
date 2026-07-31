<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_id')->unique();
            $table->string('qualification')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // Now that the teachers table exists, add the deferred foreign key
        // on classes.form_teacher_id that was left unlinked in the previous
        // migration to respect foreign-key creation order.
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('form_teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });

        Schema::create('teacher_class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_subject_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['teacher_id', 'class_subject_id']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('admission_no')->unique();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('house')->nullable();
            $table->string('gender')->nullable();
            $table->string('state_of_origin')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('occupation')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['parent_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teacher_class_subjects');

        // Drop the deferred foreign key before dropping the teachers table.
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['form_teacher_id']);
        });

        Schema::dropIfExists('teachers');
    }
};
