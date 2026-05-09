<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('label', 9)->unique();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_active')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('Lycée à configurer');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->foreignId('active_school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('level', 20);
            $table->string('icon_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unique(['name', 'level']);
        });

        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->json('language_ids')->nullable();
            $table->timestamps();
            $table->unique(['school_year_id', 'name']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('last_name', 120);
            $table->string('first_name', 120);
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('extra_time')->default(false);
            $table->string('pronote_options')->nullable();
            $table->timestamps();
        });

        Schema::create('student_languages', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->primary(['student_id', 'language_id']);
        });

        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'school_class_id', 'language_id'], 'teacher_scope_unique');
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained();
            $table->enum('type', ['ecrit', 'oral']);
            $table->date('exam_date');
            $table->time('start_time');
            $table->string('room', 120);
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('supervisor_name')->nullable();
            $table->boolean('is_catchup')->default(false);
            $table->foreignId('initial_exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->enum('status', ['prevue', 'terminee'])->default('prevue');
            $table->timestamps();
        });

        Schema::create('exam_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->time('pass_time')->nullable();
            $table->unique(['exam_id', 'student_id']);
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('value', 10)->nullable();
            $table->decimal('numeric_value', 5, 2)->nullable();
            $table->foreignId('catchup_exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });

        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type', 120);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['uploaded_files', 'grades', 'exam_slots', 'exams', 'teacher_assignments', 'student_languages', 'students', 'school_classes', 'languages', 'school_settings', 'school_years'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
