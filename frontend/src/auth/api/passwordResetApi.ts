import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/v1/auth";

export interface ForgotPasswordPayload {
  email: string;
}

export interface VerifyOtpPayload {
  email: string;
  otp: string;
}

export interface ResetPasswordPayload {
  email: string;
  otp: string;
  password: string;
  password_confirmation: string;
}

// Send OTP
export const forgotPasswordApi = async (data: ForgotPasswordPayload) => {
  const response = await axios.post(`${API_URL}/forgot-password`, data);

  return response.data;
};

// Verify OTP
export const verifyOtpApi = async (data: VerifyOtpPayload) => {
  const response = await axios.post(`${API_URL}/verify-otp`, data);

  return response.data;
};

// Reset Password
export const resetPasswordApi = async (data: ResetPasswordPayload) => {
  const response = await axios.post(`${API_URL}/reset-password`, data);

  return response.data;
};
