export interface YearLevel {
  id: number
  name: string
  sort_order: number
  created_at: string | null
  updated_at: string | null
}

export interface YearLevelFormData {
  name: string
  sort_order: number
}

// Data sent to the API when creating or updating a year level
export interface YearLevelPayload {
  name: string
  final_year_confirmed?: boolean
}

export interface YearLevelFilters {
  search?: string
  per_page?: number
  page?: number
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface ApiCollection<T> {
  data: T[]
  meta: PaginationMeta
}

export interface ApiResource<T> {
  data: T
}

export interface ApiMessage {
  message: string
}
