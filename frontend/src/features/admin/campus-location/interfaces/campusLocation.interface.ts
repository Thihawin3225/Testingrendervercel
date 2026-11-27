export interface CampusLocation {
  id: number
  name: string
  building: string | null
  display_name: string
  description: string | null
  is_active: boolean
  created_at: string | null
  updated_at: string | null
}

export interface CampusLocationFormData {
  name: string
  building: string
  description: string
  is_active: boolean
}

export interface CampusLocationFilters {
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
  data?: CampusLocation
}