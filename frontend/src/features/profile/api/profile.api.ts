import { http } from '@/services/http'
import type {
  AvatarUploadResponse,
  UpdateBioResponse,
} from '../interfaces/profile.interface'

export const profileApi = {
  async uploadAvatar(file: File): Promise<AvatarUploadResponse['data']> {
    const formData = new FormData()
    formData.append('avatar', file)

    const response = await http.post<AvatarUploadResponse>(
      '/profile/avatar',
      formData,
    )

    return response.data.data
  },

  async updateBio(bio: string): Promise<string | null> {
    const response = await http.put<UpdateBioResponse>('/profile', { bio })

    return response.data.data.bio
  },
}
