import { computed, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'

import { yearLevelsApi } from '../api/yearLevels.api'

import type { MaybeRefOrGetter } from 'vue'

import type {
  YearLevelFilters,
} from '../interfaces/yearLevel.interface'

export const yearLevelKeys = {
  all: ['year-levels'] as const,

  lists: () => [...yearLevelKeys.all, 'list'] as const,

  list: (filters: YearLevelFilters) =>
    [...yearLevelKeys.lists(), filters] as const,

  details: () => [...yearLevelKeys.all, 'detail'] as const,

  detail: (id: number) =>
    [...yearLevelKeys.details(), id] as const,
}

export function useYearLevels(
  filters: MaybeRefOrGetter<YearLevelFilters> = {},
) {
  const currentFilters = computed(() => toValue(filters))

  return useQuery({
    queryKey: computed(() =>
      yearLevelKeys.list(currentFilters.value),
    ),

    queryFn: () =>
      yearLevelsApi.getAll(currentFilters.value),
  })
}

export function useYearLevel(
  id: MaybeRefOrGetter<number>,
) {
  return useQuery({
    queryKey: computed(() =>
      yearLevelKeys.detail(toValue(id)),
    ),

    queryFn: () =>
      yearLevelsApi.getById(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  })
}
