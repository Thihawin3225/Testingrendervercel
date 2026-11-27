export type EnrollmentStatus =
  | 'active'
  | 'completed'
  | 'withdrawn'
  | 'suspended'

export interface StudentEnrollment {
  id: number

  user: {
    id: number
    name: string
    email: string
  }

  academic_year: {
    id: number
    name: string
  }

  year_level: {
    id: number
    name: string
  }

  major: {
    id: number
    name: string
  }

  semester: {
    id: number
    name: string
  }

  status: EnrollmentStatus
  created_at: string | null
  updated_at: string | null
}

export interface StudentEnrollmentFormData {
  user_id: number
  academic_year_id: number
  year_level_id: number
  major_id: number
  semester_id: number
  status: EnrollmentStatus
}

export interface StudentEnrollmentFilters {
  search?: string
  status?: EnrollmentStatus
  academic_year_id?: number
  year_level_id?: number
  major_id?: number
  semester_id?: number
  page?: number
  per_page?: number
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
