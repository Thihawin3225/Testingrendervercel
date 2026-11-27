import { http } from '../../../services/http'

import type {
  Semester,
  SemesterFilters,
  SemesterFormData,
  ApiCollection,
  ApiMessage,
  ApiResource,
} from '../interfaces/semester.interface'

const ENDPOINT = '/academic/semesters'

export const semestersApi = {
  async getAll(params: SemesterFilters = {}) {
    const response = await http.get<ApiCollection<Semester>>(
      ENDPOINT,
      { params },
    )

    return response.data
  },

  async getById(id: number) {
    const response = await http.get<ApiResource<Semester>>(
      `${ENDPOINT}/${id}`,
    )

    return response.data.data
  },

  async create(data: SemesterFormData) {
    const response = await http.post<ApiResource<Semester>>(
      ENDPOINT,
      data,
    )

    return response.data.data
  },

  async update(id: number, data: SemesterFormData) {
    const response = await http.put<ApiResource<Semester>>(
      `${ENDPOINT}/${id}`,
      data,
    )

    return response.data.data
  },

  async delete(id: number) {
    const response = await http.delete<ApiMessage>(
      `${ENDPOINT}/${id}`,
    )

    return response.data
  },
}