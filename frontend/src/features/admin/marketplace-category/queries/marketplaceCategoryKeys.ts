import type {
  MarketplaceCategoryFilters,
} from '../interfaces/marketplaceCategory.interface'

export const marketplaceCategoryKeys = {
  all: ['marketplace-categories'] as const,

  lists: () => [...marketplaceCategoryKeys.all, 'list'] as const,

  list: (filters: MarketplaceCategoryFilters) =>
    [...marketplaceCategoryKeys.lists(), filters] as const,

  details: () => [...marketplaceCategoryKeys.all, 'detail'] as const,

  detail: (id: number) =>
    [...marketplaceCategoryKeys.details(), id] as const,
}
