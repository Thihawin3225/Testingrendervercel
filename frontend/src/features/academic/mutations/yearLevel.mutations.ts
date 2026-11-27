import { useMutation, useQueryClient } from '@tanstack/vue-query'

import { yearLevelsApi } from '../api/yearLevels.api'
import { yearLevelKeys } from '../queries/yearLevel.queries'

import type {
  YearLevelPayload,
} from '../interfaces/yearLevel.interface'

export function useCreateYearLevel() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: YearLevelPayload) =>
      yearLevelsApi.create(data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: yearLevelKeys.all,
      })
    },
  })
}

export function useUpdateYearLevel() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: YearLevelPayload
    }) => yearLevelsApi.update(id, data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: yearLevelKeys.all,
      })
    },
  })
}

export function useDeleteYearLevel() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      yearLevelsApi.delete(id),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: yearLevelKeys.all,
      })
    },
  })
}
