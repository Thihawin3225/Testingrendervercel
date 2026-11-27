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

export function useUpdateMarketplaceCategory() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: MarketplaceCategoryFormData
    }) => marketplaceCategoriesApi.update(id, data),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: marketplaceCategoryKeys.all,
      }),
  })
}
