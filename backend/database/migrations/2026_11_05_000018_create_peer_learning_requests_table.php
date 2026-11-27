<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peer_learning_requests', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('learner_id');
            // A request must target either a catalog offering or a custom topic.
            $table->unsignedBigInteger('subject_offering_id')->nullable();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->text('learning_goal')->nullable();
            $table->enum('preferred_mode', ['online', 'in_person', 'either'])->default('either');
            $table->text('preferred_availability')->nullable();
            $table->enum('status', ['open', 'matched', 'closed', 'cancelled'])->default('open');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->index(['learner_id', 'status'], 'idx_peer_learning_requests_learner_status');
            $table->index(['subject_offering_id', 'status'], 'idx_peer_learning_requests_offering_status');
            $table->foreign('learner_id', 'fk_peer_learning_requests_learner')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('subject_offering_id', 'fk_peer_learning_requests_offering')->references('id')->on('subject_offerings')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('availability_id')->nullable()->constrained('availability')->nullOnDelete();
            $table->foreignId('campus_location_id')->nullable()->constrained('campus_locations')->nullOnDelete();
            $table->foreignId('custom_topic_id')->nullable()->constrained('custom_topics')->restrictOnDelete();
        });

        DB::statement('ALTER TABLE `peer_learning_requests` ADD CONSTRAINT `chk_peer_learning_request_topic` CHECK ((subject_offering_id IS NOT NULL AND custom_topic_id IS NULL) OR (subject_offering_id IS NULL AND custom_topic_id IS NOT NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('peer_learning_requests');
    }
};
