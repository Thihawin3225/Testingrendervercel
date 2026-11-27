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

export function useDeleteMarketplaceCategory() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      marketplaceCategoriesApi.delete(id),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: marketplaceCategoryKeys.all,
      }),
  })
}
