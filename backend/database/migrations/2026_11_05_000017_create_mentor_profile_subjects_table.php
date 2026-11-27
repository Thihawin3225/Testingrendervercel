<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_profile_subjects', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('mentor_profile_id');
            $table->unsignedBigInteger('subject_offering_id');
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(['mentor_profile_id', 'subject_offering_id'], 'uq_mentor_profile_subjects_pair');
            $table->index('subject_offering_id', 'idx_mentor_profile_subjects_offering');
            $table->foreign('mentor_profile_id', 'fk_mentor_profile_subjects_mentor')->references('id')->on('mentor_profiles')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('subject_offering_id', 'fk_mentor_profile_subjects_offering')->references('id')->on('subject_offerings')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_profile_subjects');
    }
};
