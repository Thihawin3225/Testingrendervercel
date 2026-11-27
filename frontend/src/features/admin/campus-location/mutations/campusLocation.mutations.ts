import {
  useMutation,
  useQueryClient,
} from '@tanstack/vue-query'

import { campusLocationsApi } from '../api/campusLocation.api'

import { campusLocationKeys } from '../queries/campusLocation.queries'

import type {
  CampusLocationFormData,
} from '../interfaces/campusLocation.interface'

// CREATE
export function useCreateCampusLocation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CampusLocationFormData) =>
      campusLocationsApi.create(data),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: campusLocationKeys.all,
      }),
  })
}

// UPDATE
export function useUpdateCampusLocation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: CampusLocationFormData
    }) => campusLocationsApi.update(id, data),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: campusLocationKeys.all,
      }),
  })
}

// DELETE
export function useDeleteCampusLocation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      campusLocationsApi.delete(id),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: campusLocationKeys.all,
      }),
  })
}

// DEACTIVATE
export function useDeactivateCampusLocation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      campusLocationsApi.deactivate(id),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: campusLocationKeys.all,
      }),
  })
}
