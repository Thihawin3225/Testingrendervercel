<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_bookmarks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('study_material_id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(['user_id', 'study_material_id'], 'uq_material_bookmarks_user_material');
            $table->index('study_material_id', 'idx_material_bookmarks_material');
            $table->foreign('user_id', 'fk_material_bookmarks_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('study_material_id', 'fk_material_bookmarks_study_material')->references('id')->on('study_materials')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_bookmarks');
    }
};
