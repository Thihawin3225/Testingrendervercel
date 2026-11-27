import { useQuery } from '@tanstack/vue-query'
import { computed, type MaybeRefOrGetter, toValue } from 'vue'

import { semestersApi } from '../api/semesters.api'
import type { SemesterFilters } from '../interfaces/semester.interface'

export const semesterKeys = {
  all: ['semesters'] as const,

  lists: () => [...semesterKeys.all, 'list'] as const,

  list: (filters: SemesterFilters) =>
    [...semesterKeys.lists(), filters] as const,

  details: () => [...semesterKeys.all, 'detail'] as const,

  detail: (id: number) =>
    [...semesterKeys.details(), id] as const,
}

export function useSemesters(
  filters: MaybeRefOrGetter<SemesterFilters>,
) {
  return useQuery({
    queryKey: computed(() => semesterKeys.list(toValue(filters))),
    queryFn: () => semestersApi.getAll(toValue(filters)),
  })
}

export function useSemester(id: MaybeRefOrGetter<number>) {
  return useQuery({
    queryKey: computed(() => semesterKeys.detail(toValue(id))),
    queryFn: () => semestersApi.getById(toValue(id)),
    enabled: computed(() => toValue(id) > 0),
  })
}