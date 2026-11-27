export interface AcademicYear {
  id: number
  name: string
  start_date: string
  end_date: string
  is_active: boolean
  created_at: string | null
  updated_at: string | null
}

export interface AcademicYearFormData {
  name: string
  start_date: string
  end_date: string
  is_active: boolean
}

export interface AcademicYearFilters {
  search?: string
  is_active?: boolean
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

export interface LatestAcademicYearStartDate {
  start_date: string | null
}