import { http } from '@/services/http'

import type {
  MarketplaceCategory,
  MarketplaceCategoryFormData,
  MarketplaceCategoryFilters,
  ApiCollection,
  ApiResource,
  ApiMessage,
} from '../interfaces/marketplaceCategory.interface'

const endpoint = '/marketplace/categories'

export const marketplaceCategoriesApi = {
  // Get all marketplace categories
  async getAll(
    filters: MarketplaceCategoryFilters = {},
  ): Promise<ApiCollection<MarketplaceCategory>> {
    const response = await http.get<ApiCollection<MarketplaceCategory>>(
      endpoint,
      {
        params: filters,
      },
    )

    return response.data
  },

  // Get a single marketplace category
  async getById(
    id: number,
  ): Promise<ApiResource<MarketplaceCategory>> {
    const response = await http.get<ApiResource<MarketplaceCategory>>(
      `${endpoint}/${id}`,
    )

    return response.data
  },

  // Create a new marketplace category
  async create(
    data: MarketplaceCategoryFormData,
  ): Promise<ApiResource<MarketplaceCategory>> {
    const response = await http.post<ApiResource<MarketplaceCategory>>(
      endpoint,
      data,
    )

    return response.data
  },

  // Update an existing marketplace category
  async update(
    id: number,
    data: MarketplaceCategoryFormData,
  ): Promise<ApiResource<MarketplaceCategory>> {
    const response = await http.put<ApiResource<MarketplaceCategory>>(
      `${endpoint}/${id}`,
      data,
    )

    return response.data
  },

  // Delete a marketplace category
  async delete(id: number): Promise<ApiMessage> {
    const response = await http.delete<ApiMessage>(
      `${endpoint}/${id}`,
    )

    return response.data
  },
}
