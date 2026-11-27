<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('introduction')->nullable();
            $table->text('experience')->nullable();
            $table->text('availability')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique('user_id', 'uq_mentor_profiles_user');
            $table->index('is_active', 'idx_mentor_profiles_active');
            $table->foreign('user_id', 'fk_mentor_profiles_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('campus_location_id')->nullable()->constrained('campus_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_profiles');
    }
};
