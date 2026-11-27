<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();

            $table->string('email')->index();

            // Hashed OTP
            $table->string('otp');

            // OTP expires after 5 minutes
            $table->timestamp('expires_at');

            // Incorrect OTP attempts
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};
