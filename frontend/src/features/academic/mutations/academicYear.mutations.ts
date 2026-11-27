import { useMutation, useQueryClient } from '@tanstack/vue-query'

import { academicYearsApi } from '../api/academicYears.api'
import { academicYearKeys } from '../queries/academicYear.queries'

import type {
  AcademicYearFormData,
} from '../interfaces/academicYear.interface'

export function useCreateAcademicYear() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: AcademicYearFormData) =>
      academicYearsApi.create(data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: academicYearKeys.all,
      })
    },
  })
}

export function useUpdateAcademicYear() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: AcademicYearFormData
    }) => academicYearsApi.update(id, data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: academicYearKeys.all,
      })
    },
  })
}

export function useDeleteAcademicYear() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      academicYearsApi.delete(id),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: academicYearKeys.all,
      })
    },
  })
}
