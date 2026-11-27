<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('student_number', 50);
            $table->string('avatar_cloudinary_public_id', 255)->charset('utf8mb4')->collation('utf8mb4_bin')->nullable();
            $table->string('avatar_cloudinary_asset_id', 64)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique('user_id', 'uq_student_profiles_user');
            $table->unique('student_number', 'uq_student_profiles_student_number');
            $table->unique('avatar_cloudinary_asset_id', 'uq_student_profiles_avatar_asset');
            $table->foreign('user_id', 'fk_student_profiles_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });
        DB::statement('ALTER TABLE `student_profiles` ADD CONSTRAINT `chk_student_avatar_pair` CHECK ((avatar_cloudinary_public_id IS NULL AND avatar_cloudinary_asset_id IS NULL) OR (avatar_cloudinary_public_id IS NOT NULL AND avatar_cloudinary_asset_id IS NOT NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
