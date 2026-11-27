<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peer_learning_matches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('peer_learning_request_id');
            $table->unsignedBigInteger('mentor_profile_id');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(['peer_learning_request_id', 'mentor_profile_id'], 'uq_peer_learning_matches_request_mentor');
            $table->index(['mentor_profile_id', 'status'], 'idx_peer_learning_matches_mentor_status');
            $table->index('status', 'idx_peer_learning_matches_status');
            $table->foreign('peer_learning_request_id', 'fk_peer_learning_matches_request')->references('id')->on('peer_learning_requests')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('mentor_profile_id', 'fk_peer_learning_matches_mentor')->references('id')->on('mentor_profiles')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peer_learning_matches');
    }
};
