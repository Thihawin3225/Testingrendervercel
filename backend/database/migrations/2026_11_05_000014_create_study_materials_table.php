<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_materials', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subject_offering_id');
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('original_filename', 255);
            $table->string('cloudinary_public_id', 255)->charset('utf8mb4')->collation('utf8mb4_bin');
            $table->string('cloudinary_asset_id', 64)->charset('ascii')->collation('ascii_bin');
            $table->enum('cloudinary_resource_type', ['image', 'raw'])->default('raw');
            $table->enum('cloudinary_delivery_type', ['upload', 'private', 'authenticated'])->default('authenticated');
            $table->enum('file_type', ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'other']);
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('file_size');
            $table->char('file_hash', 64)->nullable();
            $table->enum('status', ['pending', 'active', 'rejected', 'archived'])->default('pending');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique('cloudinary_asset_id', 'uq_study_materials_asset_id');
            $table->unique(['cloudinary_resource_type', 'cloudinary_delivery_type', 'cloudinary_public_id'], 'uq_study_materials_cloudinary_public');
            $table->index(['subject_offering_id', 'status', 'created_at'], 'idx_study_materials_offering_status_created');
            $table->index(['user_id', 'created_at'], 'idx_study_materials_user_created');
            $table->index('file_hash', 'idx_study_materials_file_hash');
            $table->foreign('user_id', 'fk_study_materials_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('subject_offering_id', 'fk_study_materials_subject_offering')->references('id')->on('subject_offerings')->restrictOnDelete()->cascadeOnUpdate();
        });
        DB::statement('ALTER TABLE `study_materials` ADD CONSTRAINT `chk_study_materials_file_size` CHECK (file_size > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('study_materials');
    }
};
