
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_requests', function (Blueprint $table) {
            $table->string('seller_phone', 30)->nullable();

            $table->foreignId('campus_location_id')
                ->nullable()
                ->constrained('campus_locations')
                ->nullOnDelete();

            $table->text('seller_message')->nullable();

            $table->dateTime('meeting_at')->nullable();

            $table->index(
                ['listing_id', 'status'],
                'idx_listing_requests_listing_status'
            );
        });
    }

    public function down(): void
    {
        Schema::table('listing_requests', function (Blueprint $table) {
            $table->dropIndex('idx_listing_requests_listing_status');

            $table->dropConstrainedForeignId('campus_location_id');

            $table->dropColumn([
                'seller_phone',
                'seller_message',
                'meeting_at',
            ]);
        });
    }
};
