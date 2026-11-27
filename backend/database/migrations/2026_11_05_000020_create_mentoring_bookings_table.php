<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoring_bookings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('peer_learning_match_id');
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes');
            $table->enum('mode', ['online', 'in_person']);
            $table->string('location_or_link', 500)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'rejected', 'no_show'])->default('pending');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->index('peer_learning_match_id', 'idx_mentoring_bookings_match');
            $table->index(['scheduled_at', 'status'], 'idx_mentoring_bookings_schedule_status');
            $table->foreign('peer_learning_match_id', 'fk_mentoring_bookings_match')->references('id')->on('peer_learning_matches')->restrictOnDelete()->cascadeOnUpdate();
        });
        DB::statement('ALTER TABLE `mentoring_bookings` ADD CONSTRAINT `chk_mentoring_booking_duration` CHECK (duration_minutes BETWEEN 1 AND 480)');
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_bookings');
    }
};
