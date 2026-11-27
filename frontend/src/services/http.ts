import axios from 'axios'
import { clearToken, getToken } from '../modules/auth/authToken'

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',

  headers: {
    Accept: 'application/json',
  },
})

http.interceptors.request.use((config) => {
  const token = getToken()

  if (token) {
    config.headers.set('Authorization', `Bearer ${token}`)
  }

  return config
})

http.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (
      axios.isAxiosError(error) &&
      error.response?.status === 401 &&
      !error.config?.url?.includes('/auth/login')
    ) {
      clearToken()

      const { default: router } = await import('../router')
      const currentRoute = router.currentRoute.value

      if (currentRoute.name !== 'login') {
        await router.replace({
          name: 'login',
          query: { redirect: currentRoute.fullPath },
        })
      }
    }

    return Promise.reject(error)
  },
)
