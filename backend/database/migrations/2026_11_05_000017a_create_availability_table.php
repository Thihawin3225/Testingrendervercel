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
        if (Schema::hasTable('availability')) {
            return;
        }

        Schema::create('availability', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mentor_profile_id')->constrained('mentor_profiles')->onDelete('cascade');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->boolean('is_default')->default(false);

            $table->string('day_of_week')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('migrations')->where('migration', '2026_11_10_044106_create_availability_table')->exists()) {
            return;
        }

        Schema::dropIfExists('availability');
    }
};
