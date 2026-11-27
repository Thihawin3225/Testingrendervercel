import {
  useMutation,
  useQueryClient,
} from '@tanstack/vue-query'

import { studentEnrollmentApi } from '../api/studentEnrollment.api'
import { studentEnrollmentKeys } from '../queries/studentEnrollment.queries'

import type {
  StudentEnrollmentFormData,
} from '../interfaces/studentEnrollment.interface'

export function useCreateStudentEnrollment() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: StudentEnrollmentFormData) =>
      studentEnrollmentApi.create(data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: studentEnrollmentKeys.all,
      })
    },
  })
}

export function useUpdateStudentEnrollment() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: StudentEnrollmentFormData
    }) => studentEnrollmentApi.update(id, data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: studentEnrollmentKeys.all,
      })
    },
  })
}

export function useDeleteStudentEnrollment() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      studentEnrollmentApi.delete(id),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: studentEnrollmentKeys.all,
      })
    },
  })
}
