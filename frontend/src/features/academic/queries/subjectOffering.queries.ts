import { computed, toValue } from 'vue'
import { useQuery } from '@tanstack/vue-query'

import { subjectOfferingsApi } from '../api/subjectOfferings.api'

import type { MaybeRefOrGetter } from 'vue'

import type {
  SubjectOfferingFilters,
} from '../interfaces/subjectOffering.interface'

export const subjectOfferingKeys = {
  all: ['subject-offerings'] as const,

  lists: () => [...subjectOfferingKeys.all, 'list'] as const,

  list: (filters: SubjectOfferingFilters) =>
    [...subjectOfferingKeys.lists(), filters] as const,

  details: () => [...subjectOfferingKeys.all, 'detail'] as const,

  detail: (id: number) =>
    [...subjectOfferingKeys.details(), id] as const,
}

export function useSubjectOfferings(
  filters: MaybeRefOrGetter<SubjectOfferingFilters> = {},
) {
  const currentFilters = computed(() => toValue(filters))

  return useQuery({
    queryKey: computed(() =>
      subjectOfferingKeys.list(currentFilters.value),
    ),

    queryFn: () =>
      subjectOfferingsApi.getAll(currentFilters.value),
  })
}

export function useSubjectOffering(
  id: MaybeRefOrGetter<number>,
) {
  return useQuery({
    queryKey: computed(() =>
      subjectOfferingKeys.detail(toValue(id)),
    ),

    queryFn: () =>
      subjectOfferingsApi.getById(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  })
}