<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_offerings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('year_level_id');
            $table->unsignedBigInteger('major_id');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(
                ['academic_year_id', 'year_level_id', 'major_id', 'semester_id', 'subject_id'],
                'uq_subject_offering_context'
            );
            $table->index(['academic_year_id', 'year_level_id', 'major_id'], 'idx_subject_offerings_year_major');
            $table->index('subject_id', 'idx_subject_offerings_subject');
            $table->foreign('academic_year_id', 'fk_subject_offerings_academic_year')->references('id')->on('academic_years')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('year_level_id', 'fk_subject_offerings_year_level')->references('id')->on('year_levels')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('major_id', 'fk_subject_offerings_major')->references('id')->on('majors')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('semester_id', 'fk_subject_offerings_semester')->references('id')->on('semesters')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('subject_id', 'fk_subject_offerings_subject')->references('id')->on('subjects')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_offerings');
    }
};
