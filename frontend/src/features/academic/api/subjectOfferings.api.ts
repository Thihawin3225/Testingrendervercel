import { http } from '../../../services/http'

import type {
  SubjectOffering,
  SubjectOfferingFilters,
  SubjectOfferingFormData,
  CreateSubjectWithOfferingPayload,
  ApiCollection,
  ApiMessage,
  ApiResource,
} from '../interfaces/subjectOffering.interface'

const ENDPOINT = '/academic/subject-offerings'

export const subjectOfferingsApi = {
  // Get all subject offerings
  async getAll(params: SubjectOfferingFilters = {}) {
    const response = await http.get<ApiCollection<SubjectOffering>>(
      ENDPOINT,
      { params },
    )

    return response.data
  },

  // Get one subject offering
  async getById(id: number) {
    const response = await http.get<ApiResource<SubjectOffering>>(
      `${ENDPOINT}/${id}`,
    )

    return response.data.data
  },

  // Create subject offering
  async create(data: SubjectOfferingFormData) {
    const response = await http.post<ApiResource<SubjectOffering>>(
      ENDPOINT,
      data,
    )

    return response.data.data
  },

  // Create a new subject and its offering together
  async createWithSubject(data: CreateSubjectWithOfferingPayload) {
    const response = await http.post<ApiResource<SubjectOffering>>(
      ENDPOINT,
      data,
    )

    return response.data.data
  },

  // Update subject offering
  async update(id: number, data: SubjectOfferingFormData) {
    const response = await http.put<ApiResource<SubjectOffering>>(
      `${ENDPOINT}/${id}`,
      data,
    )

    return response.data.data
  },

  // Delete subject offering
  async delete(id: number) {
    const response = await http.delete<ApiMessage>(
      `${ENDPOINT}/${id}`,
    )

    return response.data
  },
  
}