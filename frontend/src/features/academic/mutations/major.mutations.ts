import {
  useMutation,
  useQueryClient,
} from '@tanstack/vue-query'

import { majorsApi } from '../api/majors.api'

import { majorKeys } from '../queries/major.queries'

import type {
  MajorFormData,
} from '../interfaces/major.interface'

// CREATE
export function useCreateMajor() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: MajorFormData) =>
      majorsApi.create(data),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: majorKeys.all,
      }),
  })
}

// UPDATE
export function useUpdateMajor() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: MajorFormData
    }) => majorsApi.update(id, data),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: majorKeys.all,
      }),
  })
}

// DELETE
export function useDeleteMajor() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      majorsApi.delete(id),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: majorKeys.all,
      }),
  })
}