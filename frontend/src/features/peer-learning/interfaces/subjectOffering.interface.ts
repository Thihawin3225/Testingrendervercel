export interface SubjectOffering {
  id: number
  academic_year: { id: number | null; name: string | null }
  year_level: { id: number | null; name: string | null }
  major: { id: number | null; name: string | null }
  semester: { id: number | null; name: string | null }
  subject: {
    id: number | null
    subject_name: string | null
    subject_code: string | null
  }
}
