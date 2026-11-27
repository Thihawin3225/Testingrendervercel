import {
  useMutation,
  useQueryClient,
} from '@tanstack/vue-query'

import {
  marketplaceCategoriesApi,
} from '../api/marketplaceCategories.api'

import {
  marketplaceCategoryKeys,
} from '../queries/marketplaceCategoryKeys'

import type {
  MarketplaceCategoryFormData,
} from '../interfaces/marketplaceCategory.interface'

export function useCreateMarketplaceCategory() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: MarketplaceCategoryFormData) =>
      marketplaceCategoriesApi.create(data),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: marketplaceCategoryKeys.all,
      }),
  })
}
