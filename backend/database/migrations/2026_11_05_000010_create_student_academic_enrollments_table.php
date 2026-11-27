<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_academic_enrollments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('year_level_id');
            $table->unsignedBigInteger('major_id');
            $table->unsignedBigInteger('semester_id');
            $table->enum('status', ['active', 'completed', 'withdrawn', 'suspended'])->default('active');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(['user_id', 'academic_year_id', 'semester_id'], 'uq_student_enrollment_semester');
            $table->index(['academic_year_id', 'year_level_id', 'major_id', 'semester_id'], 'idx_student_enrollments_academic_context');
            $table->foreign('user_id', 'fk_student_enrollments_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('academic_year_id', 'fk_student_enrollments_academic_year')->references('id')->on('academic_years')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('year_level_id', 'fk_student_enrollments_year_level')->references('id')->on('year_levels')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('major_id', 'fk_student_enrollments_major')->references('id')->on('majors')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('semester_id', 'fk_student_enrollments_semester')->references('id')->on('semesters')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic_enrollments');
    }
};
