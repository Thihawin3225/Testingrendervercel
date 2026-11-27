<?php

namespace App\Http\Controllers\Api\V1\Auth;



use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService
    ) {}

    public function sendOtp(
        ForgotPasswordRequest $request
    ): JsonResponse {
        $expiresAt = $this->passwordResetService->sendOtp(
            $request->validated('email')
        );

        return response()->json([
            'message' => 'If the email exists, an OTP has been sent.',
            'expires_at' => $expiresAt,
        ], 200);
    }

    public function verifyOtp(
        VerifyOtpRequest $request
    ): JsonResponse {
        $this->passwordResetService->verifyOtp(
            $request->validated('email'),
            $request->validated('otp')
        );

        return response()->json([
            'success' => true,
            'verified' => true,
            'message' => 'OTP verified successfully.',
        ], 200);
    }
}
