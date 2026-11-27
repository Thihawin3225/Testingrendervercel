<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_listing_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->string('cloudinary_public_id', 255)->charset('utf8mb4')->collation('utf8mb4_bin');
            $table->string('cloudinary_asset_id', 64)->charset('ascii')->collation('ascii_bin');
            $table->enum('cloudinary_resource_type', ['image'])->default('image');
            $table->enum('cloudinary_delivery_type', ['upload', 'private', 'authenticated'])->default('upload');
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique('cloudinary_asset_id', 'uq_listing_images_asset_id');
            $table->unique(['cloudinary_resource_type', 'cloudinary_delivery_type', 'cloudinary_public_id'], 'uq_listing_images_cloudinary_public');
            $table->unique(['listing_id', 'sort_order'], 'uq_listing_images_sort_order');
            $table->foreign('listing_id', 'fk_listing_images_listing')->references('id')->on('marketplace_listings')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listing_images');
    }
};
