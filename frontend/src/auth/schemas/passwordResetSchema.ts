import { z } from "zod";
import { toTypedSchema } from "@vee-validate/zod";

export const forgotPasswordSchema = toTypedSchema(
  z.object({
    email: z
      .string()
      .min(1, "Email is required.")
      .email("Please enter a valid email.")
      .max(255),
  }),
);

export const resetPasswordSchema = toTypedSchema(
  z
    .object({
      otp: z
        .string()
        .length(6, "OTP must contain exactly 6 digits.")
        .regex(/^\d{6}$/, "OTP must contain only numbers."),

      password: z.string().min(8, "Password must be at least 8 characters."),

      password_confirmation: z.string().min(1, "Please confirm your password."),
    })
    .refine((values) => values.password === values.password_confirmation, {
      message: "Passwords do not match.",
      path: ["password_confirmation"],
    }),
);
