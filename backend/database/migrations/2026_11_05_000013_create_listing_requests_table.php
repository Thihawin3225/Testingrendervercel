<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_requests', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('requester_id');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(['listing_id', 'requester_id'], 'uq_listing_requests_listing_requester');
            $table->index(['requester_id', 'status'], 'idx_listing_requests_requester_status');
            $table->foreign('listing_id', 'fk_listing_requests_listing')->references('id')->on('marketplace_listings')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('requester_id', 'fk_listing_requests_requester')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_requests');
    }
};
