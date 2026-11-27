import { useMutation } from "@tanstack/vue-query";

import { forgotPasswordApi, resetPasswordApi } from "../api/passwordResetApi";

export const useForgotPassword = () => {
  return useMutation({
    mutationFn: forgotPasswordApi,
  });
};

export const useResetPassword = () => {
  return useMutation({
    mutationFn: resetPasswordApi,
  });
};
