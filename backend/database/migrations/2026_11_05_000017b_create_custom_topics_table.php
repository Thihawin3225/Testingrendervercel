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
        if (Schema::hasTable('custom_topics')) {
            return;
        }

        Schema::create('custom_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();

            $table->string('title');      // eg - Laravel
            $table->string('subtitle')->nullable(); // eg - Error Handling
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('migrations')->where('migration', '2026_11_10_071226_create_custom_topics_table')->exists()) {
            return;
        }

        Schema::dropIfExists('custom_topics');
    }
};
