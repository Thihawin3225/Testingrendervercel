import { computed, toValue } from 'vue'
import type { MaybeRefOrGetter } from 'vue'

import { useQuery } from '@tanstack/vue-query'

import { majorsApi } from '../api/majors.api'

import type { MajorFilters } from '../interfaces/major.interface'

export const majorKeys = {
  all: ['majors'] as const,

  lists: () => [...majorKeys.all, 'list'] as const,

  list: (filters: MajorFilters) =>
    [...majorKeys.lists(), filters] as const,

  details: () => [...majorKeys.all, 'detail'] as const,

  detail: (id: number) =>
    [...majorKeys.details(), id] as const,
}

export function useMajors(
  filters: MaybeRefOrGetter<MajorFilters> = {},
) {
  const currentFilters = computed(() => toValue(filters))

  return useQuery({
    queryKey: computed(() =>
      majorKeys.list(currentFilters.value),
    ),

    queryFn: () =>
      majorsApi.getAll(currentFilters.value),
  })
}

export function useMajor(
  id: MaybeRefOrGetter<number>,
) {
  return useQuery({
    queryKey: computed(() =>
      majorKeys.detail(toValue(id)),
    ),

    queryFn: () =>
      majorsApi.getById(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  })
}