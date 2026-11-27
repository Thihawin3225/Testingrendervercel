import type { StudyMaterial } from './studyMaterial.interface'

export interface BookmarkedStudyMaterial extends StudyMaterial {
  uploader?: { id: number; name: string } | null
  bookmarked_at?: string | null
}
