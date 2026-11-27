export interface Semester {
  id: number
  name: string
  sort_order: number
  created_at: string | null
  updated_at: string | null
}

export interface SemesterFormData {
  name: string
  sort_order: number
}

export interface SemesterFilters {
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