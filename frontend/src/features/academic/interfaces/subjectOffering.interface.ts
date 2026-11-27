export interface SubjectOffering {
  id: number

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

  subject: {
    id: number
    subject_name: string
    subject_code: string
  }

  created_at: string | null
  updated_at: string | null
}

export interface SubjectOfferingFormData {
  academic_year_id: number
  year_level_id: number
  major_id: number
  semester_id: number
  subject_id: number
}

export interface SubjectOfferingFilters {
  search?: string
  academic_year_id?: number
  year_level_id?: number
  major_id?: number
  semester_id?: number
  subject_id?: number
  per_page?: number
  page?: number
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface SubjectDraft {
  subject_code: string
  subject_name: string
  description: string
}

export interface CreateSubjectWithOfferingPayload {
  subject: SubjectDraft
  offering: Omit<SubjectOfferingFormData, 'subject_id'>
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