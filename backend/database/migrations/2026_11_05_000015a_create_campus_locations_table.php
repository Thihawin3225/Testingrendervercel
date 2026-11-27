<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('campus_locations')) {
            return;
        }

        Schema::create('campus_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ဥပမာ - Room 301, Main Library, Lab A စသည်ဖြင့်
            $table->string('building')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('migrations')->where('migration', '2026_11_10_064053_create_campus_locations_table')->exists()) {
            return;
        }

        Schema::dropIfExists('campus_locations');
    }
};
