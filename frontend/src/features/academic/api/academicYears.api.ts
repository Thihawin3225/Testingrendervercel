import { http } from '../../../services/http'

import type {
  AcademicYear,
  AcademicYearFilters,
  AcademicYearFormData,
  ApiCollection,
  ApiMessage,
  ApiResource,
  LatestAcademicYearStartDate,
} from '../interfaces/academicYear.interface'

const ENDPOINT = '/academic/academic-years'

export const academicYearsApi = {
  // Get all academic years
  async getAll(params: AcademicYearFilters = {}) {
    const response = await http.get<ApiCollection<AcademicYear>>(
      ENDPOINT,
      { params },
    )

    return response.data
  },

  // Get one academic year
  async getById(id: number) {
    const response = await http.get<ApiResource<AcademicYear>>(
      `${ENDPOINT}/${id}`,
    )

    return response.data.data
  },

  // Create academic year
  async create(data: AcademicYearFormData) {
    const response = await http.post<ApiResource<AcademicYear>>(
      ENDPOINT,
      data,
    )

    return response.data.data
  },

  // Update academic year
  async update(id: number, data: AcademicYearFormData) {
    const response = await http.put<ApiResource<AcademicYear>>(
      `${ENDPOINT}/${id}`,
      data,
    )

    return response.data.data
  },

  // Delete academic year
  async delete(id: number) {
    const response = await http.delete<ApiMessage>(
      `${ENDPOINT}/${id}`,
    )

    return response.data
  },

  // Get latest academic year's start date
  async getLatestStartDate() {
    const response = await http.get<{
      data: LatestAcademicYearStartDate
    }>(`${ENDPOINT}/latest-start-date`)

    return response.data.data.start_date
  },
}
