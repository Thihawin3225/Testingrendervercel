<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_years')) {
            return;
        }

        Schema::create('academic_years', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->string('name', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique('name', 'uq_academic_years_name');
            $table->index('is_active', 'idx_academic_years_active');
        });
        DB::statement('ALTER TABLE `academic_years` ADD CONSTRAINT `chk_academic_year_dates` CHECK (end_date >= start_date)');
    }

    public function down(): void
    {
        if (DB::table('migrations')->where('migration', '2026_11_10_073358_create_academic_years_table')->exists()) {
            return;
        }

        Schema::dropIfExists('academic_years');
    }
};
