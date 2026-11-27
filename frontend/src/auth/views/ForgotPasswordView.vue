<script setup lang="ts">
import { ref, computed, nextTick, onUnmounted } from "vue";

import axios from "axios";

import BaseButton from "../../components/ui/BaseButton.vue";

import {
  forgotPasswordApi,
  verifyOtpApi,
  resetPasswordApi,
} from "../api/passwordResetApi";

type ResetStep = "email" | "otp" | "password" | "success";

const currentStep = ref<ResetStep>("email");

const email = ref("");
const otpDigits = ref(["", "", "", "", "", ""]);

const password = ref("");
const passwordConfirmation = ref("");

const isLoading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

const otpInputRefs = ref<HTMLInputElement[]>([]);

const otp = computed(() => otpDigits.value.join(""));

const steps = [
  { key: "email", label: "Email" },
  { key: "otp", label: "Verify" },
  { key: "password", label: "Password" },
];

const stepNumber = computed(() => {
  const index = steps.findIndex((step) => step.key === currentStep.value);

  return index === -1 ? 3 : index + 1;
});

// =====================================
// OTP EXPIRATION TIMER (FROM BACKEND)
// =====================================

const expiresAt = ref<number | null>(null);
const remainingSeconds = ref(0);

let countdownInterval: ReturnType<typeof setInterval> | null = null;

// Display remaining time as MM:SS
const formattedTime = computed(() => {
  const minutes = Math.floor(remainingSeconds.value / 60);
  const seconds = remainingSeconds.value % 60;

  return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
});

// Check OTP expiration
const isOtpExpired = computed(() => {
  return expiresAt.value !== null && remainingSeconds.value <= 0;
});

// Stop countdown
const stopOtpCountdown = () => {
  if (countdownInterval !== null) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
};

// Update countdown from backend timestamp
const updateCountdown = () => {
  if (expiresAt.value === null) return;

  remainingSeconds.value = Math.max(
    0,
    Math.ceil((expiresAt.value - Date.now()) / 1000),
  );

  if (remainingSeconds.value <= 0) {
    stopOtpCountdown();
  }
};

// Start countdown using Laravel expires_at
const startOtpCountdown = (expirationTime: string) => {
  stopOtpCountdown();

  const timestamp = new Date(expirationTime).getTime();

  if (!Number.isFinite(timestamp)) {
    expiresAt.value = null;
    remainingSeconds.value = 0;

    throw new Error("Invalid OTP expiration time.");
  }

  expiresAt.value = timestamp;

  updateCountdown();

  if (remainingSeconds.value > 0) {
    countdownInterval = setInterval(updateCountdown, 1000);
  }
};

// Clear timer when component is destroyed
onUnmounted(() => {
  stopOtpCountdown();
});

// =====================================
// OTP INPUT HELPERS
// =====================================

// Focus OTP input
const focusOtpInput = async (index: number) => {
  await nextTick();
  otpInputRefs.value[index]?.focus();
};

// Reset OTP inputs
const clearOtp = () => {
  otpDigits.value = ["", "", "", "", "", ""];
};

// =====================================
// STEP 1: SEND OTP
// =====================================

const handleSendOtp = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    const response = await forgotPasswordApi({
      email: email.value.trim(),
    });

    // Laravel must return expires_at
    if (!response.expires_at) {
      throw new Error("OTP expiration time was not returned by the server.");
    }

    // Clear previous OTP
    clearOtp();

    // Start countdown using backend expiration
    startOtpCountdown(response.expires_at);

    if (isOtpExpired.value) {
      errorMessage.value =
        "OTP has already expired. Please request a new code.";

      return;
    }

    currentStep.value = "otp";

    successMessage.value = "A verification code has been sent to your email.";

    await focusOtpInput(0);
  } catch (error) {
    errorMessage.value = getErrorMessage(error);
  } finally {
    isLoading.value = false;
  }
};

// =====================================
// STEP 2: VERIFY OTP
// =====================================

const handleVerifyOtp = async () => {
  if (isOtpExpired.value) {
    errorMessage.value = "OTP has expired. Please request a new code.";

    return;
  }

  try {
    isLoading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    const response = await verifyOtpApi({
      email: email.value.trim(),
      otp: otp.value,
    });

    if (response.verified === true) {
      currentStep.value = "password";
    } else {
      errorMessage.value = "Invalid verification code.";
    }
  } catch (error) {
    errorMessage.value = getErrorMessage(error);
  } finally {
    isLoading.value = false;
  }
};

// =====================================
// STEP 3: RESET PASSWORD
// =====================================

const handleResetPassword = async () => {
  errorMessage.value = "";
  successMessage.value = "";

  if (password.value.length < 8) {
    errorMessage.value = "Password must be at least 8 characters.";

    return;
  }

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = "Passwords do not match.";
    return;
  }

  try {
    isLoading.value = true;

    await resetPasswordApi({
      email: email.value.trim(),
      otp: otp.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });

    stopOtpCountdown();

    currentStep.value = "success";

    successMessage.value = "Your password has been updated successfully.";

    password.value = "";
    passwordConfirmation.value = "";
  } catch (error) {
    errorMessage.value = getErrorMessage(error);
  } finally {
    isLoading.value = false;
  }
};

// =====================================
// OTP INPUT HANDLING
// =====================================

const handleOtpInput = (event: Event, index: number) => {
  const target = event.target as HTMLInputElement;

  const digits = target.value.replace(/\D/g, "");

  if (!digits) {
    otpDigits.value[index] = "";
    return;
  }

  // Handle pasting multiple digits
  if (digits.length > 1) {
    const pastedDigits = digits.slice(0, 6 - index).split("");

    pastedDigits.forEach((digit, offset) => {
      otpDigits.value[index + offset] = digit;
    });

    const nextIndex = Math.min(index + pastedDigits.length, 5);

    focusOtpInput(nextIndex);
    return;
  }

  // Handle single digit
  otpDigits.value[index] = digits[0] || "undefined";

  if (index < 5) {
    focusOtpInput(index + 1);
  }
};

// OTP keyboard navigation
const handleOtpKeydown = (event: KeyboardEvent, index: number) => {
  if (event.key === "Backspace" && !otpDigits.value[index] && index > 0) {
    focusOtpInput(index - 1);
  }

  if (event.key === "ArrowLeft" && index > 0) {
    event.preventDefault();
    focusOtpInput(index - 1);
  }

  if (event.key === "ArrowRight" && index < 5) {
    event.preventDefault();
    focusOtpInput(index + 1);
  }
};

// =====================================
// NAVIGATION
// =====================================

// Navigate back to email
const goToEmail = () => {
  stopOtpCountdown();

  expiresAt.value = null;
  remainingSeconds.value = 0;

  currentStep.value = "email";

  clearOtp();

  errorMessage.value = "";
  successMessage.value = "";
};

// Navigate back to OTP
const goToOtp = () => {
  currentStep.value = "otp";

  password.value = "";
  passwordConfirmation.value = "";

  errorMessage.value = "";
  successMessage.value = "";

  focusOtpInput(0);
};

// =====================================
// API ERROR HANDLING
// =====================================

const getErrorMessage = (error: unknown): string => {
  if (axios.isAxiosError(error)) {
    const errors = error.response?.data?.errors;

    if (errors) {
      const firstError = Object.values(errors).flat()[0];

      if (typeof firstError === "string") {
        return firstError;
      }
    }

    return (
      error.response?.data?.message ?? "Something went wrong. Please try again."
    );
  }

  if (error instanceof Error) {
    return error.message;
  }

  return "An unexpected error occurred.";
};
</script>

<template>
  <main class="auth-page">
    <!-- Background -->
    <div class="auth-background">
      <div class="background-orb orb-one"></div>
      <div class="background-orb orb-two"></div>
      <div class="background-grid"></div>
    </div>

    <!-- Main Card -->
    <section class="auth-card">
      <!-- Brand -->
      <header class="brand-header">
        <RouterLink to="/login" class="brand-logo">
          <span class="logo-icon">C</span>

          <span class="brand-name"> Campus<span>Bridge</span> </span>
        </RouterLink>

        <div class="security-badge">
          <svg viewBox="0 0 24 24" fill="none">
            <path
              d="M12 3L19 6V11C19 15.5 16 19 12 21C8 19 5 15.5 5 11V6L12 3Z"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linejoin="round"
            />
            <path
              d="M9 12L11 14L15 10"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>

          <span>Secure Recovery</span>
        </div>
      </header>

      <!-- Progress -->
      <div v-if="currentStep !== 'success'" class="progress-section">
        <div class="progress-info">
          <span>Account recovery</span>
          <span>Step {{ stepNumber }} of 3</span>
        </div>

        <div class="progress-track">
          <div
            class="progress-value"
            :style="{
              width: `${(stepNumber / 3) * 100}%`,
            }"
          ></div>
        </div>

        <div class="step-labels">
          <div
            v-for="(step, index) in steps"
            :key="step.key"
            class="step-label"
            :class="{
              active: index + 1 <= stepNumber,
              current: index + 1 === stepNumber,
            }"
          >
            <span class="step-dot">
              <svg
                v-if="index + 1 < stepNumber"
                viewBox="0 0 24 24"
                fill="none"
              >
                <path
                  d="M5 12L10 17L19 7"
                  stroke="currentColor"
                  stroke-width="2.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>

              <template v-else>
                {{ index + 1 }}
              </template>
            </span>

            <span>{{ step.label }}</span>
          </div>
        </div>
      </div>

      <!-- STEP 1: EMAIL -->
      <form
        v-if="currentStep === 'email'"
        class="form-content"
        @submit.prevent="handleSendOtp"
      >
        <!-- Icon and heading in ONE ROW -->
        <div class="email-heading-row">
          <div class="illustration email-icon">
            <svg viewBox="0 0 24 24" fill="none">
              <rect
                x="3"
                y="5"
                width="18"
                height="14"
                rx="3"
                stroke="currentColor"
                stroke-width="1.7"
              />
              <path
                d="M4 7L12 13L20 7"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
              />
            </svg>
          </div>

          <div class="form-heading">
            <h1>Forgot your password?</h1>
          </div>
        </div>

        <p class="email-description">
          Don't worry! Enter your registered email address. We'll send you a
          verification code to reset your password.
        </p>

        <div class="form-field">
          <label for="email">Email address</label>

          <div class="input-wrapper">
            <svg viewBox="0 0 24 24" fill="none">
              <rect
                x="3"
                y="5"
                width="18"
                height="14"
                rx="3"
                stroke="currentColor"
                stroke-width="1.7"
              />
              <path
                d="M4 7L12 13L20 7"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
              />
            </svg>

            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="name@example.com"
              autocomplete="email"
              required
            />
          </div>
        </div>

        <div v-if="errorMessage" class="status-message error">
          <span>!</span>
          {{ errorMessage }}
        </div>

        <div v-if="successMessage" class="status-message success">
          <span>✓</span>
          {{ successMessage }}
        </div>

        <BaseButton
          type="submit"
          class="submit-button"
          :disabled="isLoading || !email.trim()"
        >
          {{
            isLoading
              ? "Sending verification code..."
              : "Send verification code"
          }}

          <span v-if="!isLoading">→</span>
        </BaseButton>

        <RouterLink to="/login" class="back-link">
          <span>←</span>
          Back to login
        </RouterLink>
      </form>

      <!-- STEP 2: OTP -->
      <form
        v-else-if="currentStep === 'otp'"
        class="form-content"
        @submit.prevent="handleVerifyOtp"
      >
        <div class="illustration otp-icon">
          <svg viewBox="0 0 24 24" fill="none">
            <rect
              x="6"
              y="3"
              width="12"
              height="18"
              rx="3"
              stroke="currentColor"
              stroke-width="1.7"
            />
            <path
              d="M10 17H14"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linecap="round"
            />
            <circle cx="12" cy="8" r="1" fill="currentColor" />
          </svg>
        </div>

        <div class="form-heading">
          <h1>Check your email</h1>

          <p>
            We've sent a verification code to
            <strong>{{ email }}</strong
            >. Enter the 6-digit code below.
          </p>
        </div>

        <div class="form-field">
          <label>Verification code</label>

          <div class="otp-input-container">
            <input
              v-for="(_, index) in otpDigits"
              :key="index"
              :ref="
                (el) => {
                  if (el) {
                    otpInputRefs[index] = el as HTMLInputElement;
                  }
                }
              "
              :value="otpDigits[index]"
              type="text"
              inputmode="numeric"
              maxlength="6"
              :autocomplete="index === 0 ? 'one-time-code' : 'off'"
              class="otp-digit"
              :aria-label="`OTP digit ${index + 1}`"
              required
              @input="handleOtpInput($event, index)"
              @keydown="handleOtpKeydown($event, index)"
            />
          </div>

          <!-- OTP EXPIRATION TIMER -->
          <div class="otp-timer">
            <template v-if="!isOtpExpired">
              <span class="timer-icon">◷</span>

              <span>
                Code expires in
                <strong>{{ formattedTime }}</strong>
              </span>
            </template>

            <template v-else>
              <span class="expired-text">
                OTP has expired. Please request a new code.
              </span>
            </template>
          </div>

          <small class="field-hint">
            Enter the 6-digit code sent to your email.
          </small>
        </div>

        <div v-if="errorMessage" class="status-message error">
          <span>!</span>
          {{ errorMessage }}
        </div>

        <div v-if="successMessage" class="status-message success">
          <span>✓</span>
          {{ successMessage }}
        </div>

        <BaseButton
          type="submit"
          class="submit-button"
          :disabled="isLoading || otp.length !== 6 || isOtpExpired"
        >
          {{ isLoading ? "Verifying..." : "Verify code" }}

          <span v-if="!isLoading">→</span>
        </BaseButton>

        <button
          type="button"
          class="text-button"
          :disabled="isLoading"
          @click="handleSendOtp"
        >
          Didn't receive the code?

          <strong>
            {{ isOtpExpired ? "Request new code" : "Resend code" }}
          </strong>
        </button>

        <button type="button" class="back-link back-button" @click="goToEmail">
          ← Change email address
        </button>
      </form>

      <!-- STEP 3: NEW PASSWORD -->
      <form
        v-else-if="currentStep === 'password'"
        class="form-content"
        @submit.prevent="handleResetPassword"
      >
        <div class="illustration password-icon">
          <svg viewBox="0 0 24 24" fill="none">
            <rect
              x="4"
              y="10"
              width="16"
              height="11"
              rx="2.5"
              stroke="currentColor"
              stroke-width="1.7"
            />
            <path
              d="M8 10V7C8 4.8 9.8 3 12 3C14.2 3 16 4.8 16 7V10"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linecap="round"
            />
            <circle cx="12" cy="15" r="1.2" fill="currentColor" />
          </svg>
        </div>

        <div class="form-heading">
          <h1>Create new password</h1>

          <p>
            Your email has been verified. Create a strong new password for your
            account.
          </p>
        </div>

        <div class="form-field">
          <label for="password">New password</label>

          <div class="input-wrapper">
            <svg viewBox="0 0 24 24" fill="none">
              <rect
                x="4"
                y="10"
                width="16"
                height="11"
                rx="2.5"
                stroke="currentColor"
                stroke-width="1.7"
              />
              <path
                d="M8 10V7C8 4.8 9.8 3 12 3C14.2 3 16 4.8 16 7V10"
                stroke="currentColor"
                stroke-width="1.7"
              />
            </svg>

            <input
              id="password"
              v-model="password"
              type="password"
              placeholder="Enter new password"
              autocomplete="new-password"
              minlength="8"
              required
            />
          </div>

          <small class="field-hint"> Use at least 8 characters. </small>
        </div>

        <div class="form-field">
          <label for="passwordConfirmation"> Confirm new password </label>

          <div class="input-wrapper">
            <svg viewBox="0 0 24 24" fill="none">
              <rect
                x="4"
                y="10"
                width="16"
                height="11"
                rx="2.5"
                stroke="currentColor"
                stroke-width="1.7"
              />
              <path
                d="M8 10V7C8 4.8 9.8 3 12 3C14.2 3 16 4.8 16 7V10"
                stroke="currentColor"
                stroke-width="1.7"
              />
            </svg>

            <input
              id="passwordConfirmation"
              v-model="passwordConfirmation"
              type="password"
              placeholder="Confirm your password"
              autocomplete="new-password"
              minlength="8"
              required
            />
          </div>
        </div>

        <div v-if="errorMessage" class="status-message error">
          <span>!</span>
          {{ errorMessage }}
        </div>

        <BaseButton
          type="submit"
          class="submit-button"
          :disabled="isLoading || !password || !passwordConfirmation"
        >
          {{ isLoading ? "Updating password..." : "Reset password" }}

          <span v-if="!isLoading">→</span>
        </BaseButton>

        <button
          type="button"
          class="back-link back-button"
          :disabled="isLoading"
          @click="goToOtp"
        >
          ← Back to verification
        </button>
      </form>

      <!-- STEP 4: SUCCESS -->
      <div v-else-if="currentStep === 'success'" class="success-content">
        <div class="success-animation">
          <svg viewBox="0 0 24 24" fill="none">
            <path
              d="M5 12L10 17L20 7"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>

        <span class="success-label"> PASSWORD UPDATED </span>

        <h1>You're all set!</h1>

        <p>
          {{ successMessage }}
          You can now sign in using your new password.
        </p>

        <RouterLink to="/login" class="login-button">
          Continue to login
          <span>→</span>
        </RouterLink>
      </div>

      <!-- Footer -->
      <footer class="auth-footer">
        <span>© {{ new Date().getFullYear() }} CampusBridge</span>

        <span class="footer-divider">•</span>

        <span>Secure account recovery</span>
      </footer>
    </section>
  </main>
</template>

<style scoped>
/* =====================================
   GLOBAL / PAGE
===================================== */

:global(*) {
  box-sizing: border-box;
}

.auth-page {
  position: fixed;
  inset: 0;
  z-index: 10;

  width: 100%;
  height: 100vh;
  height: 100svh;

  display: grid;
  place-items: center;

  overflow: hidden;
  padding: 16px;

  background: #f5f6fb;

  font-family:
    Inter,
    -apple-system,
    BlinkMacSystemFont,
    "Segoe UI",
    sans-serif;
}

/* =====================================
   BACKGROUND
===================================== */

.auth-background {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.background-orb {
  position: absolute;
  border-radius: 50%;
}

.orb-one {
  width: 450px;
  height: 450px;

  top: -260px;
  left: -130px;

  background: radial-gradient(circle, #ddd4ff 0%, transparent 70%);
}

.orb-two {
  width: 500px;
  height: 500px;

  right: -250px;
  bottom: -280px;

  background: radial-gradient(circle, #dbe7ff 0%, transparent 70%);
}

.background-grid {
  position: absolute;
  inset: 0;

  background-image:
    linear-gradient(rgba(112, 91, 180, 0.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(112, 91, 180, 0.025) 1px, transparent 1px);

  background-size: 35px 35px;
}

/* =====================================
   MAIN CARD
===================================== */

.auth-card {
  position: relative;
  z-index: 1;

  width: 100%;
  max-width: 450px;

  max-height: calc(100svh - 32px);

  overflow-y: auto;
  overscroll-behavior: contain;

  padding: 25px 34px 18px;

  border: 1px solid #eaebf2;
  border-radius: 19px;

  background: #ffffff;

  box-shadow: 0 20px 65px rgba(35, 39, 75, 0.08);
}

/* =====================================
   BRAND HEADER
===================================== */

.brand-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;

  margin-bottom: 22px;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 9px;

  text-decoration: none;
}

.logo-icon {
  width: 35px;
  height: 35px;

  display: grid;
  place-items: center;

  border-radius: 10px;

  background: linear-gradient(135deg, #7954e8, #5235bc);

  color: white;
  font-size: 20px;
  font-weight: 800;
}

.brand-name {
  color: #25243b;
  font-size: 17px;
  font-weight: 800;
  letter-spacing: -0.7px;
}

.brand-name span {
  color: #7654dc;
}

.security-badge {
  display: flex;
  align-items: center;
  gap: 5px;

  color: #77778d;
  font-size: 10px;
  font-weight: 600;
  white-space: nowrap;
}

.security-badge svg {
  width: 17px;
  height: 17px;
  color: #7654dc;
}

/* =====================================
   PROGRESS INDICATOR
===================================== */

.progress-section {
  margin-bottom: 23px;
}

.progress-info {
  display: flex;
  justify-content: space-between;

  margin-bottom: 9px;

  color: #88889c;
  font-size: 11px;
  font-weight: 600;
}

.progress-track {
  height: 5px;

  overflow: hidden;

  border-radius: 10px;
  background: #eeedf5;
}

.progress-value {
  height: 100%;

  border-radius: 10px;

  background: linear-gradient(90deg, #8a68ed, #5b3dc5);

  transition: width 0.3s ease;
}

.step-labels {
  display: flex;
  justify-content: space-between;

  margin-top: 14px;
}

.step-label {
  display: flex;
  align-items: center;
  gap: 6px;

  color: #aaa9b9;
  font-size: 10px;
  font-weight: 600;
}

.step-dot {
  width: 21px;
  height: 21px;

  display: grid;
  place-items: center;

  border: 1px solid #e3e2ec;
  border-radius: 50%;

  background: white;
  font-size: 10px;
}

.step-dot svg {
  width: 13px;
  height: 13px;
}

.step-label.active {
  color: #6244cb;
}

.step-label.active .step-dot {
  border-color: #7654dc;
  background: #7654dc;
  color: white;
}

.step-label.current {
  font-weight: 800;
}

/* =====================================
   FORM
===================================== */

.form-content {
  display: flex;
  flex-direction: column;
}

/* Email icon + heading in one row */
.email-heading-row {
  display: flex;
  align-items: center;
  gap: 13px;

  margin-bottom: 8px;
}

.illustration {
  width: 52px;
  height: 52px;

  display: grid;
  place-items: center;

  margin-bottom: 15px;

  border-radius: 15px;
  flex-shrink: 0;
}

.email-heading-row .illustration {
  margin-bottom: 0;
}

.illustration svg {
  width: 26px;
  height: 26px;
}

.email-icon,
.otp-icon,
.password-icon {
  background: #f0ebff;
  color: #7654dc;
}

.form-heading h1,
.success-content h1 {
  margin: 0;

  color: #26253b;

  font-size: 23px;
  font-weight: 800;
  letter-spacing: -0.9px;
}

.form-heading p,
.success-content p {
  margin: 8px 0 19px;

  color: #858498;

  font-size: 12px;
  line-height: 1.75;
}

.form-heading strong {
  color: #6548c9;
  overflow-wrap: anywhere;
}

.email-heading-row .form-heading {
  min-width: 0;
}

.email-heading-row .form-heading h1 {
  font-size: 21px;
}

.email-description {
  margin: 0 0 19px;

  color: #858498;

  font-size: 12px;
  line-height: 1.75;
}

/* =====================================
   INPUT FIELDS
===================================== */

.form-field {
  margin-bottom: 14px;
}

.form-field label {
  display: block;

  margin-bottom: 8px;

  color: #39384e;
  font-size: 12px;
  font-weight: 700;
}

.input-wrapper {
  min-height: 46px;

  display: flex;
  align-items: center;
  gap: 11px;

  padding: 0 13px;

  border: 1px solid #e4e3ed;
  border-radius: 10px;

  background: white;

  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.input-wrapper:focus-within {
  border-color: #8a6be5;

  box-shadow: 0 0 0 3px rgba(118, 84, 220, 0.1);
}

.input-wrapper > svg {
  width: 18px;
  height: 18px;

  flex: none;

  color: #9997ae;
}

.input-wrapper input {
  width: 100%;
  min-width: 0;

  padding: 11px 0;

  border: none;
  outline: none;

  background: transparent;

  color: #302f44;
  font-size: 13px;
}

.input-wrapper input::placeholder {
  color: #b0afbf;
}

.field-hint {
  display: block;

  margin-top: 7px;

  color: #9998aa;
  font-size: 10px;
}

/* =====================================
   OTP DIGIT INPUT
===================================== */

.otp-input-container {
  width: 100%;

  display: flex;
  justify-content: center;
  gap: 10px;

  margin: 9px 0 10px;
}

.otp-digit {
  width: 49px;
  height: 53px;

  flex: 1;
  min-width: 0;

  border: 1px solid #e4e3ed;
  border-radius: 10px;

  outline: none;

  background: #ffffff;
  color: #6548c9;

  text-align: center;
  font-size: 21px;
  font-weight: 700;

  transition:
    border-color 0.2s,
    box-shadow 0.2s,
    background 0.2s;
}

.otp-digit:focus {
  border-color: #7654dc;

  background: #fbfaff;

  box-shadow: 0 0 0 3px rgba(118, 84, 220, 0.12);
}

/* =====================================
   OTP EXPIRATION TIMER
===================================== */

.otp-timer {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 7px;

  margin: 10px 0 15px;

  color: #858498;
  font-size: 12px;
}

.timer-icon {
  color: #7654dc;
  font-size: 19px;
}

.otp-timer strong {
  color: #7654dc;
  font-variant-numeric: tabular-nums;
}

.expired-text {
  color: #dc3545;
  font-weight: 600;
}

/* =====================================
   BUTTONS
===================================== */

.submit-button {
  width: 100%;
  min-height: 46px;

  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;

  border-radius: 10px;

  background: linear-gradient(100deg, #7654dc, #5b3bbd);

  color: white;

  font-size: 12px;
  font-weight: 700;

  box-shadow: 0 5px 14px rgba(103, 72, 200, 0.2);

  transition:
    transform 0.2s,
    box-shadow 0.2s;
}

.submit-button:not(:disabled):hover {
  transform: translateY(-2px);

  box-shadow: 0 8px 20px rgba(103, 72, 200, 0.28);
}

.submit-button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.submit-button span {
  font-size: 17px;
}

.back-link {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 7px;

  margin-top: 15px;

  color: #77758d;

  font-size: 11px;
  font-weight: 600;

  text-decoration: none;

  transition: color 0.2s;
}

.back-link:hover {
  color: #6847cf;
}

.back-link span {
  font-size: 16px;
}

.back-button,
.text-button {
  border: none;
  background: transparent;
  cursor: pointer;
}

.back-button:disabled,
.text-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* =====================================
   STATUS MESSAGES
===================================== */

.status-message {
  display: flex;
  align-items: flex-start;
  gap: 8px;

  padding: 10px 12px;
  margin-bottom: 13px;

  border-radius: 9px;

  font-size: 11px;
  line-height: 1.6;

  overflow-wrap: anywhere;
}

.status-message span {
  flex: none;
  font-weight: 800;
}

.status-message.error {
  background: #fff0f0;
  color: #c43b45;
}

.status-message.success {
  background: #edfaf2;
  color: #25814c;
}

/* =====================================
   RESEND OTP
===================================== */

.text-button {
  margin-top: 16px;

  color: #89879b;
  font-size: 11px;
}

.text-button strong {
  color: #6b4bcf;
}

.text-button:hover:not(:disabled) strong {
  text-decoration: underline;
}

/* =====================================
   SUCCESS SCREEN
===================================== */

.success-content {
  display: flex;
  flex-direction: column;
  align-items: center;

  padding: 15px 0;

  text-align: center;
}

.success-animation {
  width: 76px;
  height: 76px;

  display: grid;
  place-items: center;

  margin-bottom: 19px;

  border-radius: 50%;

  background: #e8f8ef;
  color: #22965b;
}

.success-animation svg {
  width: 37px;
  height: 37px;
}

.success-label {
  margin-bottom: 10px;

  color: #29935d;

  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.5px;
}

.success-content h1 {
  font-size: 27px;
}

.success-content p {
  max-width: 320px;
  margin: 10px 0 22px;
}

.login-button {
  width: 100%;
  min-height: 46px;

  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;

  border-radius: 10px;

  background: linear-gradient(100deg, #7654dc, #5b3bbd);

  color: white;

  font-size: 12px;
  font-weight: 700;

  text-decoration: none;

  transition: transform 0.2s;
}

.login-button:hover {
  transform: translateY(-2px);
}

.login-button span {
  font-size: 17px;
}

/* =====================================
   FOOTER
===================================== */

.auth-footer {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;

  margin-top: 23px;
  padding-top: 14px;

  border-top: 1px solid #f0eff5;

  color: #aaa9b8;
  font-size: 10px;
}

.footer-divider {
  color: #d1cfdd;
}

/* =====================================
   RESPONSIVE
===================================== */

@media (max-width: 520px) {
  .auth-page {
    padding: 12px;
  }

  .auth-card {
    max-width: 420px;
    padding: 23px 22px 16px;
    border-radius: 16px;
  }

  .brand-header {
    margin-bottom: 19px;
  }

  .form-heading h1 {
    font-size: 21px;
  }

  .email-heading-row .form-heading h1 {
    font-size: 19px;
  }

  .step-label {
    gap: 4px;
    font-size: 9px;
  }

  .otp-input-container {
    gap: 8px;
  }

  .otp-digit {
    height: 50px;
    font-size: 20px;
  }
}

/* Short desktop and mobile screens */
@media (max-height: 700px) {
  .auth-card {
    padding-top: 15px;
    padding-bottom: 12px;
  }

  .brand-header {
    margin-bottom: 14px;
  }

  .progress-section {
    margin-bottom: 15px;
  }

  .illustration {
    width: 42px;
    height: 42px;
    margin-bottom: 9px;
  }

  .email-heading-row .illustration {
    margin-bottom: 0;
  }

  .illustration svg {
    width: 22px;
    height: 22px;
  }

  .form-heading p {
    margin-bottom: 12px;
  }

  .email-description {
    margin-bottom: 12px;
  }

  .form-field {
    margin-bottom: 10px;
  }

  .auth-footer {
    margin-top: 13px;
    padding-top: 9px;
  }

  .success-content {
    padding: 8px 0;
  }
}

/* Very narrow mobile */
@media (max-width: 360px) {
  .auth-card {
    padding-right: 16px;
    padding-left: 16px;
  }

  .brand-name {
    font-size: 15px;
  }

  .security-badge {
    font-size: 9px;
  }

  .email-heading-row {
    gap: 9px;
  }

  .email-heading-row .form-heading h1 {
    font-size: 17px;
  }

  .otp-input-container {
    gap: 6px;
  }

  .otp-digit {
    height: 46px;
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
  }
}
</style>
