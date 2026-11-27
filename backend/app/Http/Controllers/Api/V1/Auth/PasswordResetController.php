<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as RoutingController;

class PasswordResetController extends RoutingController
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {}

    /**
     * Send OTP to user's email.
     */
    public function forgotPassword(
        ForgotPasswordRequest $request
    ): JsonResponse {

        $this->passwordResetService->sendOtp(
            $request->validated('email')
        );

        return response()->json([
            'success' => true,
            'message' => 'If the email is registered, an OTP has been sent.',
        ], 200);
    }
    public function verifyOtp(VerifyOtpRequest $request)
    {
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

    /**
     * Verify OTP and reset password.
     */
    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {

        // Get all validated data as an array
        $data = $request->validated();

        // Pass one array to the service
        $this->passwordResetService->resetPassword($data);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
        ], 200);
    }
}
