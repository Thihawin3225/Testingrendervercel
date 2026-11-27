import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchStudyMaterials } from '../api/studyMaterial.api'
import type { StudyMaterialFilters } from '../interfaces/studyMaterial.interface'
import { studyMaterialQueryKeys } from './studyMaterialQueryKeys'

export function useStudyMaterials(
  filters: MaybeRefOrGetter<StudyMaterialFilters> = {},
) {
  const currentFilters = computed(() => ({ ...toValue(filters) }))

  return useQuery({
    queryKey: computed(() =>
      studyMaterialQueryKeys.browse(currentFilters.value),
    ),
    queryFn: () => fetchStudyMaterials(currentFilters.value),
  })
}
