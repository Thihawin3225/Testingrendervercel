export interface MarketplaceCategory {
  id: number
  name: string
  description: string | null
  listings_count: number
  created_at: string | null
  updated_at: string | null
}

export interface MarketplaceCategoryFormData {
  name: string
  description: string
}

export interface MarketplaceCategoryFilters {
  search?: string
  page?: number
  per_page?: number
}

export interface ApiCollection<T> {
  data: T[]
}

export interface ApiResource<T> {
  data: T
}

export interface ApiMessage {
  message: string
  data?: MarketplaceCategory
}
