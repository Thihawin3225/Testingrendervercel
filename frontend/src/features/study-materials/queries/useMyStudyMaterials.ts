import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchMyStudyMaterials } from '../api/studyMaterial.api'
import type { StudyMaterialFilters } from '../interfaces/studyMaterial.interface'
import { studyMaterialQueryKeys } from './studyMaterialQueryKeys'

export function useMyStudyMaterials(
  filters: MaybeRefOrGetter<StudyMaterialFilters> = {},
) {
  const currentFilters = computed(() => ({ ...toValue(filters) }))

  return useQuery({
    queryKey: computed(() =>
      studyMaterialQueryKeys.mine(currentFilters.value),
    ),
    queryFn: () => fetchMyStudyMaterials(currentFilters.value),
  })
}
