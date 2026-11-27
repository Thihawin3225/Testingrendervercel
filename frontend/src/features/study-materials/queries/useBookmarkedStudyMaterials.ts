import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchBookmarkedStudyMaterials } from '../api/studyMaterial.api'
import type { StudyMaterialFilters } from '../interfaces/studyMaterial.interface'
import { studyMaterialQueryKeys } from './studyMaterialQueryKeys'

export function useBookmarkedStudyMaterials(
  filters: MaybeRefOrGetter<StudyMaterialFilters> = {},
) {
  const currentFilters = computed(() => ({ ...toValue(filters) }))

  return useQuery({
    queryKey: computed(() =>
      studyMaterialQueryKeys.bookmarks(currentFilters.value),
    ),
    queryFn: () => fetchBookmarkedStudyMaterials(currentFilters.value),
  })
}
