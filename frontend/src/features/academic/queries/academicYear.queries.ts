import { computed, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'

import { academicYearsApi } from '../api/academicYears.api'

import type { MaybeRefOrGetter } from 'vue'

import type {
  AcademicYearFilters,
} from '../interfaces/academicYear.interface'

export const academicYearKeys = {
  all: ['academic-years'] as const,

  lists: () => [...academicYearKeys.all, 'list'] as const,

  list: (filters: AcademicYearFilters) =>
    [...academicYearKeys.lists(), filters] as const,

  details: () => [...academicYearKeys.all, 'detail'] as const,

  detail: (id: number) =>
    [...academicYearKeys.details(), id] as const,
}

export function useAcademicYears(
  filters: MaybeRefOrGetter<AcademicYearFilters> = {},
) {
  const currentFilters = computed(() => toValue(filters))

  return useQuery({
    queryKey: computed(() =>
      academicYearKeys.list(currentFilters.value),
    ),

    queryFn: () =>
      academicYearsApi.getAll(currentFilters.value),
  })
}

export function useAcademicYear(
  id: MaybeRefOrGetter<number>,
) {
  return useQuery({
    queryKey: computed(() =>
      academicYearKeys.detail(toValue(id)),
    ),

    queryFn: () =>
      academicYearsApi.getById(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  })
}
