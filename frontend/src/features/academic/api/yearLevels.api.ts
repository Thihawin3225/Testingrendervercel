
import { http } from '../../../services/http'

import type {
  YearLevel,
  YearLevelFilters,
  YearLevelPayload,
  ApiCollection,
  ApiMessage,
  ApiResource,
} from '../interfaces/yearLevel.interface'

const ENDPOINT = '/academic/year-levels'

export const yearLevelsApi = {
  // Get all year levels
  async getAll(params: YearLevelFilters = {}) {
    const response = await http.get<ApiCollection<YearLevel>>(
      ENDPOINT,
      { params },
    )

    return response.data
  },

  // Get one year level
  async getById(id: number) {
    const response = await http.get<ApiResource<YearLevel>>(
      `${ENDPOINT}/${id}`,
    )

    return response.data.data
  },

  // Create year level
  async create(data: YearLevelPayload) {
    const response = await http.post<ApiResource<YearLevel>>(
      ENDPOINT,
      data,
    )

    return response.data.data
  },

  // Update year level
  async update(id: number, data: YearLevelPayload) {
    const response = await http.put<ApiResource<YearLevel>>(
      `${ENDPOINT}/${id}`,
      data,
    )

    return response.data.data
  },

  // Delete year level
  async delete(id: number) {
    const response = await http.delete<ApiMessage>(
      `${ENDPOINT}/${id}`,
    )

    return response.data
  },
}
