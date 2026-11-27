import { http } from '@/services/http'

import type {
  StudentEnrollment,
  StudentEnrollmentFormData,
  StudentEnrollmentFilters,
  ApiCollection,
  ApiResource,
  ApiMessage,
} from '../interfaces/studentEnrollment.interface'

// Change this path if your Laravel route is different.
const BASE_URL = '/academic/studentacademicenrollments'

export const studentEnrollmentApi = {
  async getAll(
    params: StudentEnrollmentFilters = {},
  ): Promise<ApiCollection<StudentEnrollment>> {
    const response = await http.get<
      ApiCollection<StudentEnrollment>
    >(BASE_URL, { params })

    return response.data
  },

  async getById(id: number): Promise<StudentEnrollment> {
    const response = await http.get<
      ApiResource<StudentEnrollment>
    >(`${BASE_URL}/${id}`)

    return response.data.data
  },

  async create(
    payload: StudentEnrollmentFormData,
  ): Promise<StudentEnrollment> {
    const response = await http.post<
      ApiResource<StudentEnrollment>
    >(BASE_URL, payload)

    return response.data.data
  },

  async update(
    id: number,
    payload: Partial<StudentEnrollmentFormData>,
  ): Promise<StudentEnrollment> {
    const response = await http.put<
      ApiResource<StudentEnrollment>
    >(`${BASE_URL}/${id}`, payload)

    return response.data.data
  },

  async delete(id: number): Promise<ApiMessage> {
    const response = await http.delete<ApiMessage>(
      `${BASE_URL}/${id}`,
    )

    return response.data
  },
}
