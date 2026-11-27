import { computed, toValue } from 'vue'
import type { MaybeRefOrGetter } from 'vue'

import { useQuery } from '@tanstack/vue-query'

import { marketplaceCategoriesApi } from '../api/marketplaceCategories.api'

import type {
  MarketplaceCategoryFilters,
} from '../interfaces/marketplaceCategory.interface'

import {
  marketplaceCategoryKeys,
} from '../queries/marketplaceCategoryKeys'

// ALL MARKETPLACE CATEGORIES
export function useMarketplaceCategories(
  filters: MaybeRefOrGetter<MarketplaceCategoryFilters> = {},
) {
  const currentFilters = computed(() => toValue(filters))

  return useQuery({
    queryKey: computed(() =>
      marketplaceCategoryKeys.list(currentFilters.value),
    ),

    queryFn: () =>
      marketplaceCategoriesApi.getAll(currentFilters.value),
  })
}

// SINGLE MARKETPLACE CATEGORY
export function useMarketplaceCategory(
  id: MaybeRefOrGetter<number>,
) {
  return useQuery({
    queryKey: computed(() =>
      marketplaceCategoryKeys.detail(toValue(id)),
    ),

    queryFn: () =>
      marketplaceCategoriesApi.getById(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  })
}
