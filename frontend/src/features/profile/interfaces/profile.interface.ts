import type { AuthUser } from '@/modules/auth/api/authApi'

export type ProfileUser = AuthUser

export interface AvatarUploadResponse {
  message: string
  data: {
    avatar_url: string
    avatar_cloudinary_public_id: string
  }
}

export interface UpdateBioResponse {
  message: string
  data: {
    bio: string | null
  }
}
