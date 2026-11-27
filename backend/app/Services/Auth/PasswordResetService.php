<?php

namespace App\Services\Auth;


use App\Models\User;
use App\Models\PasswordResetOtp;
use App\Mail\PasswordOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{


    public function sendOtp(string $email): string
    {
        $user = User::where('email', $email)->first();

        // Generate expiry time (5 minutes)
        $expiresAt = now()->addMinutes(5);

        // Do not reveal whether the email exists.
        // Return the same response structure for unknown emails.
        if (!$user) {
            return $expiresAt->toISOString();
        }

        // Generate a 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Store hashed OTP in database
        $resetOtp = PasswordResetOtp::updateOrCreate(
            ['email' => $email],
            [
                'otp' => Hash::make($otp),
                'expires_at' => $expiresAt,
                'attempts' => 0,
            ]
        );

        // Debug: verify generated OTP matches stored hash
        Log::info(
            'OTP hash test: ' .
                (Hash::check($otp, $resetOtp->otp) ? 'PASS' : 'FAIL')
        );

        // Send OTP email
        Mail::to($email)->send(
            new PasswordOtpMail($otp)
        );

        // Return expiry time to controller
        return $expiresAt->toISOString();
    }

    public function verifyOtp(string $email, string $otp): void
    {
        $status = DB::transaction(function () use ($email, $otp) {
            $record = PasswordResetOtp::where('email', $email)
                ->lockForUpdate()
                ->first();

            if (!$record) {
                return 'invalid';
            }

            if ($record->expires_at->isPast()) {
                return 'expired';
            }

            if ($record->attempts >= 5) {
                return 'too_many_attempts';
            }

            if (!Hash::check($otp, $record->otp)) {
                $record->increment('attempts');

                return 'invalid';
            }

            return 'valid';
        });

        if ($status === 'expired') {
            throw ValidationException::withMessages([
                'otp' => ['OTP has expired. Please request a new OTP.'],
            ]);
        }

        if ($status === 'too_many_attempts') {
            throw ValidationException::withMessages([
                'otp' => ['Too many attempts. Please request a new OTP.'],
            ]);
        }

        if ($status !== 'valid') {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP.'],
            ]);
        }
    }

    /**
     * Verify OTP and reset user's password.
     */
    public function resetPassword(array $data): void
    {
        $resetOtp = PasswordResetOtp::where(
            'email',
            $data['email']
        )->first();

        // 1. Check whether OTP exists
        if (!$resetOtp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        // 2. Check OTP expiration
        if ($resetOtp->expires_at->isPast()) {
            $resetOtp->delete();

            throw ValidationException::withMessages([
                'otp' => ['OTP has expired. Please request a new OTP.'],
            ]);
        }

        // 3. Check maximum attempts
        if ($resetOtp->attempts >= 5) {
            $resetOtp->delete();

            throw ValidationException::withMessages([
                'otp' => ['Too many attempts. Please request a new OTP.'],
            ]);
        }

        // 4. Verify OTP
        $inputOtp = trim((string) $data['otp']);

        $isValid = Hash::check(
            $inputOtp,
            $resetOtp->otp
        );

        // Debug: log verification result only
        Log::info(
            'OTP verification: ' . ($isValid ? 'PASS' : 'FAIL')
        );

        if (!$isValid) {
            $resetOtp->increment('attempts');

            if ($resetOtp->fresh()->attempts >= 5) {
                $resetOtp->delete();
            }

            throw ValidationException::withMessages([
                'otp' => ['Incorrect OTP.'],
            ]);
        }

        // 5. Find user
        $user = User::where(
            'email',
            $data['email']
        )->first();

        if (!$user) {
            $resetOtp->delete();

            throw ValidationException::withMessages([
                'email' => ['Unable to reset password.'],
            ]);
        }

        // 6. Update password and delete OTP together
        DB::transaction(function () use (
            $user,
            $data,
            $resetOtp
        ) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);

            // Revoke all existing Sanctum tokens
            $user->tokens()->delete();

            // Delete used OTP
            $resetOtp->delete();
        });
    }
}
