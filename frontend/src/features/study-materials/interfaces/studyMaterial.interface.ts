export interface CreateStudyMaterialPayload {
  subject_offering_id: number | null
  title: string
  description: string | null
  file: File
}

export interface UpdateStudyMaterialPayload {
  id: number
  subject_offering_id?: number | null
  title?: string
  description?: string | null
  file?: File
}

export type StudyMaterialStatus = 'pending' | 'active' | 'rejected' | 'archived'

export type StudyMaterialFileType = 'pdf' | 'ppt' | 'pptx' | 'doc' | 'docx' | 'other'

export interface StudyMaterialFilters {
  search?: string
  material_type?: 'official' | 'custom'
  academic_year_id?: number
  year_level_id?: number
  per_page?: number
}

/** Mirrors the metadata returned by the Laravel StudyMaterial resource. */
export interface StudyMaterial {
  id: number
  subject_offering_id: number | null
  subject_offering?: {
    id: number
    subject: {
      id: number | null
      subject_name: string | null
      subject_code: string | null
    }
  } | null
  uploader?: { id: number; name: string } | null
  title: string
  description: string | null
  original_filename: string
  file_type: StudyMaterialFileType
  mime_type: string
  file_size: number
  status: StudyMaterialStatus
  is_bookmarked?: boolean
  created_at: string | null
  updated_at: string | null
}
