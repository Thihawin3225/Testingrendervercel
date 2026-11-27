import { http } from "../../../services/http";

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: string;
  status: string;
  created_at: string;

  avatar_url?: string | null;
  bio?: string | null;
}

export interface LoginResponse {
  message: string;
  user: AuthUser;
  token: string;
  token_type: string;
}

export interface MeResponse {
  data: AuthUser;
}

export const authApi = {
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    const response = await http.post<LoginResponse>("/auth/login", credentials);

    return response.data;
  },

  async me(): Promise<AuthUser> {
    const response = await http.get<MeResponse>("/auth/me");

    return response.data.data;
  },

  async logout(): Promise<void> {
    await http.post("/auth/logout");
  },
};
