import { computed, toValue } from 'vue'
import type { MaybeRefOrGetter } from 'vue'

import { useQuery } from '@tanstack/vue-query'

import { campusLocationsApi } from '../api/campusLocation.api'

import type {
  CampusLocationFilters,
} from '../interfaces/campusLocation.interface'

export const campusLocationKeys = {
  all: ['campus-locations'] as const,

  lists: () => [...campusLocationKeys.all, 'list'] as const,

  list: (filters: CampusLocationFilters) =>
    [...campusLocationKeys.lists(), filters] as const,

  details: () => [...campusLocationKeys.all, 'detail'] as const,

  detail: (id: number) =>
    [...campusLocationKeys.details(), id] as const,

  active: () => [...campusLocationKeys.all, 'active'] as const,
}

// ALL LOCATIONS
export function useCampusLocations(
  filters: MaybeRefOrGetter<CampusLocationFilters> = {},
) {
  const currentFilters = computed(() => toValue(filters))

  return useQuery({
    queryKey: computed(() =>
      campusLocationKeys.list(currentFilters.value),
    ),

    queryFn: () =>
      campusLocationsApi.getAll(currentFilters.value),
  })
}

// SINGLE LOCATION
export function useCampusLocation(
  id: MaybeRefOrGetter<number>,
) {
  return useQuery({
    queryKey: computed(() =>
      campusLocationKeys.detail(toValue(id)),
    ),

    queryFn: () =>
      campusLocationsApi.getById(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  })
}

// ACTIVE LOCATIONS
export function useActiveCampusLocations() {
  return useQuery({
    queryKey: campusLocationKeys.active(),

    queryFn: () =>
      campusLocationsApi.getActive(),
  })
}
