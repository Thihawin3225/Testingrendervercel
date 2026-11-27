import { computed, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import type { MaybeRefOrGetter } from 'vue'

import { subjectsApi } from '../api/subjects.api'

import type {
  SubjectFilters,
} from '../interfaces/subject.interface'

export const subjectKeys = {
  all: ['subjects'] as const,

  lists: () => [...subjectKeys.all, 'list'] as const,

  list: (filters: SubjectFilters) =>
    [...subjectKeys.lists(), filters] as const,

  details: () => [...subjectKeys.all, 'detail'] as const,

  detail: (id: number) =>
    [...subjectKeys.details(), id] as const,
}

export function useSubjects(
  filters: MaybeRefOrGetter<SubjectFilters> = {},
) {
  const currentFilters = computed(() => toValue(filters))

  return useQuery({
    queryKey: computed(() =>
      subjectKeys.list(currentFilters.value),
    ),

    queryFn: () =>
      subjectsApi.getAll(currentFilters.value),
  })
}

export function useSubject(
  id: MaybeRefOrGetter<number>,
) {
  return useQuery({
    queryKey: computed(() =>
      subjectKeys.detail(toValue(id)),
    ),

    queryFn: () =>
      subjectsApi.getById(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  })
}
