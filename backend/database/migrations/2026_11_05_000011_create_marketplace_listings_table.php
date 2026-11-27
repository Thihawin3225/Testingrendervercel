<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->enum('listing_type', ['sale', 'donation', 'wanted']);
            $table->decimal('price', 12, 2)->nullable();
            $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'poor'])->nullable();
            $table->enum('status', ['pending', 'active', 'reserved', 'sold', 'closed', 'rejected'])->default('pending');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->index(['user_id', 'created_at'], 'idx_marketplace_listings_user_created');
            $table->index(['category_id', 'status'], 'idx_marketplace_listings_category_status');
            $table->index(['status', 'created_at'], 'idx_marketplace_listings_status_created');
            $table->index(['listing_type', 'status'], 'idx_marketplace_listings_type_status');
            $table->foreign('user_id', 'fk_marketplace_listings_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('category_id', 'fk_marketplace_listings_category')->references('id')->on('marketplace_categories')->restrictOnDelete()->cascadeOnUpdate();
        });
        DB::statement('ALTER TABLE `marketplace_listings` ADD CONSTRAINT `chk_marketplace_listing_price` CHECK (price IS NULL OR price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};
