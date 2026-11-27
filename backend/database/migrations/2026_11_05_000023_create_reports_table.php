<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('reporter_id');
            $table->string('reportable_type', 100);
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason', 80);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->index(['reportable_type', 'reportable_id'], 'idx_reports_reportable');
            $table->index(['status', 'created_at'], 'idx_reports_status_created');
            $table->index('reporter_id', 'idx_reports_reporter');
            $table->foreign('reporter_id', 'fk_reports_reporter')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('reviewed_by', 'fk_reports_reviewed_by')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
