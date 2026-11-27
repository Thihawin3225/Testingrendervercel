
import { http } from '@/services/http'

import type {
  CampusLocation,
  CampusLocationFormData,
  CampusLocationFilters,
  ApiCollection,
  ApiResource,
  ApiMessage,
} from '../interfaces/campusLocation.interface'

const endpoint = '/admin/campus/locations'

export const campusLocationsApi = {
  // GET: All campus locations
  async getAll(
    filters: CampusLocationFilters = {},
  ): Promise<ApiCollection<CampusLocation>> {
    const response = await http.get<ApiCollection<CampusLocation>>(
      endpoint,
      {
        params: filters,
      },
    )

    return response.data
  },

  // GET: Single campus location
  async getById(
    id: number,
  ): Promise<ApiResource<CampusLocation>> {
    const response = await http.get<ApiResource<CampusLocation>>(
      `${endpoint}/${id}`,
    )

    return response.data
  },

  // GET: Active campus locations
  async getActive(): Promise<ApiCollection<CampusLocation>> {
    const response = await http.get<ApiCollection<CampusLocation>>(
      '/campus/locations/active',
    )

    return response.data
  },

  // POST: Create campus location
  async create(
    data: CampusLocationFormData,
  ): Promise<ApiResource<CampusLocation>> {
    const response = await http.post<ApiResource<CampusLocation>>(
      endpoint,
      data,
    )

    return response.data
  },

  // PUT: Update campus location
  async update(
    id: number,
    data: CampusLocationFormData,
  ): Promise<ApiResource<CampusLocation>> {
    const response = await http.put<ApiResource<CampusLocation>>(
      `${endpoint}/${id}`,
      data,
    )

    return response.data
  },

  // DELETE: Delete campus location
  async delete(id: number): Promise<ApiMessage> {
    const response = await http.delete<ApiMessage>(
      `${endpoint}/${id}`,
    )

    return response.data
  },

  // PATCH: Deactivate campus location
  async deactivate(id: number): Promise<ApiMessage> {
    const response = await http.patch<ApiMessage>(
      `${endpoint}/${id}/deactivate`,
    )

    return response.data
  },
}