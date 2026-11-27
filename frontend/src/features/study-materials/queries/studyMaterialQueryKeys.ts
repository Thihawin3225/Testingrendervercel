import type { StudyMaterialFilters } from '../interfaces/studyMaterial.interface'

export const studyMaterialQueryKeys = {
  all: ['study-materials'] as const,
  browse: (filters: StudyMaterialFilters = {}) =>
    ['study-materials', 'browse', filters] as const,
  mine: (filters: StudyMaterialFilters = {}) =>
    ['study-materials', 'mine', filters] as const,
  bookmarks: (filters: StudyMaterialFilters = {}) =>
    ['study-materials', 'bookmarks', filters] as const,
}
