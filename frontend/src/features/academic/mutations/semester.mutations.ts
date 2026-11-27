import { useMutation, useQueryClient } from '@tanstack/vue-query'

import { semestersApi } from '../api/semesters.api'
import { semesterKeys } from '../queries/semester.queries'

import type { SemesterFormData } from '../interfaces/semester.interface'

export function useCreateSemester() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: SemesterFormData) =>
      semestersApi.create(data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: semesterKeys.all,
      })
    },
  })
}

export function useUpdateSemester() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: SemesterFormData
    }) => semestersApi.update(id, data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: semesterKeys.all,
      })
    },
  })
}

export function useDeleteSemester() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) => semestersApi.delete(id),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: semesterKeys.all,
      })
    },
  })
}
