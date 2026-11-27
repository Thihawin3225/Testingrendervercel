import { http } from '../../../services/http'

import type {
  Subject,
  SubjectFormData,
  SubjectFilters,
  ApiCollection,
  ApiResource,
  ApiMessage,
} from '../interfaces/subject.interface'

const endpoint = '/academic/subjects'

export const subjectsApi = {
  async getAll(filters: SubjectFilters = {}) {
    const response = await http.get<ApiCollection<Subject>>(
      endpoint,
      {
        params: filters,
      },
    )

    return response.data
  },

  async getById(id: number) {
    const response = await http.get<ApiResource<Subject>>(
      `${endpoint}/${id}`,
    )

    return response.data.data
  },

  async create(data: SubjectFormData) {
    const response = await http.post<ApiResource<Subject>>(
      endpoint,
      data,
    )

    return response.data.data
  },

  async update(id: number, data: SubjectFormData) {
    const response = await http.put<ApiResource<Subject>>(
      `${endpoint}/${id}`,
      data,
    )

    return response.data.data
  },

  async delete(id: number) {
    const response = await http.delete<ApiMessage>(
      `${endpoint}/${id}`,
    )

    return response.data
  },
}