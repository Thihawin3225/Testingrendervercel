import { useMutation, useQueryClient } from "@tanstack/vue-query";
import { useRouter } from "vue-router";
import axios from "axios";

import { authApi } from "../api/authApi";
import { clearToken } from "../authToken";

export function useLogout() {
  const router = useRouter();
  const queryClient = useQueryClient();

  async function finishLogout() {
    // Remove Bearer Token from localStorage
    clearToken();

    // Remove previously cached user and server data
    queryClient.clear();

    // Redirect to Login Page
    await router.replace({ name: "login" });
  }

  return useMutation({
    // Call Laravel Logout API
    mutationFn: () => authApi.logout(),

    // Logout successful
    onSuccess: async () => {
      await finishLogout();
    },

    // Handle logout errors
    onError: async (error) => {
      if (axios.isAxiosError(error) && error.response?.status === 401) {
        // Token is already invalid
        await finishLogout();
      }

      // Other errors:
      // Keep the token so the user can retry logout.
    },
  });
}
