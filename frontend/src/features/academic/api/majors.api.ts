import { http } from '../../../services/http'

import type {
  Major,
  MajorFormData,
  MajorFilters,
  ApiCollection,
  ApiResource,
  ApiMessage,
} from '../interfaces/major.interface'

const endpoint = '/academic/majors'

export const majorsApi = {
  async getAll(
    filters: MajorFilters = {},
  ): Promise<ApiCollection<Major>> {
    const response = await http.get<ApiCollection<Major>>(endpoint, {
      params: filters,
    })

    return response.data
  },

  async getById(id: number): Promise<ApiResource<Major>> {
    const response = await http.get<ApiResource<Major>>(
      `${endpoint}/${id}`,
    )

    return response.data
  },

  async create(data: MajorFormData): Promise<ApiResource<Major>> {
    const response = await http.post<ApiResource<Major>>(
      endpoint,
      data,
    )

    return response.data
  },

  async update(
    id: number,
    data: MajorFormData,
  ): Promise<ApiResource<Major>> {
    const response = await http.put<ApiResource<Major>>(
      `${endpoint}/${id}`,
      data,
    )

    return response.data
  },

  async delete(id: number): Promise<ApiMessage> {
    const response = await http.delete<ApiMessage>(
      `${endpoint}/${id}`,
    )

    return response.data
  },
}