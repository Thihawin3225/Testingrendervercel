import { useMutation } from "@tanstack/vue-query";

import { authApi } from "../api/authApi";
import { saveToken } from "../authToken";

export function useLogin() {
  return useMutation({
    mutationFn: authApi.login,

    onSuccess: (response) => {
      saveToken(response.token);
    },
  });
}
