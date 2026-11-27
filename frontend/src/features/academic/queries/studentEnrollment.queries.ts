import { computed, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'

import type { MaybeRefOrGetter } from 'vue'

import { studentEnrollmentApi } from '../api/studentEnrollment.api'

import type {
  StudentEnrollmentFilters,
} from '../interfaces/studentEnrollment.interface'

export const studentEnrollmentKeys = {
  all: ['student-enrollments'] as const,

  lists: () => [
    ...studentEnrollmentKeys.all,
    'list',
  ] as const,

  list: (filters: StudentEnrollmentFilters) => [
    ...studentEnrollmentKeys.lists(),
    filters,
  ] as const,

  details: () => [
    ...studentEnrollmentKeys.all,
    'detail',
  ] as const,

  detail: (id: number) => [
    ...studentEnrollmentKeys.details(),
    id,
  ] as const,
}

export function useStudentEnrollments(
  filters: MaybeRefOrGetter<StudentEnrollmentFilters> = {},
) {
  const currentFilters = computed(() => toValue(filters))

  return useQuery({
    queryKey: computed(() =>
      studentEnrollmentKeys.list(currentFilters.value),
    ),

    queryFn: () =>
      studentEnrollmentApi.getAll(currentFilters.value),
  })
}

export function useStudentEnrollment(
  id: MaybeRefOrGetter<number>,
) {
  return useQuery({
    queryKey: computed(() =>
      studentEnrollmentKeys.detail(toValue(id)),
    ),

    queryFn: () =>
      studentEnrollmentApi.getById(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  })
}
