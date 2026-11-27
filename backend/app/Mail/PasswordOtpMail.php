<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp
    ) {}

    public function build(): static
    {
        return $this->subject('Campus Bridge - Password Reset OTP')
            ->html("
                <h2>Password Reset</h2>
                <p>Your OTP code is:</p>
                <h1>{$this->otp}</h1>
                <p>This OTP will expire in 5 minutes.</p>
                <p>If you did not request this, please ignore this email.</p>
            ");
    }
}
