import {
  useMutation,
  useQueryClient,
} from '@tanstack/vue-query'

import { subjectsApi } from '../api/subjects.api'
import { subjectKeys } from '../queries/subject.queries'

import type {
  SubjectFormData,
} from '../interfaces/subject.interface'

export function useCreateSubject() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: SubjectFormData) =>
      subjectsApi.create(data),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: subjectKeys.all,
      }),
  })
}

export function useUpdateSubject() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: SubjectFormData
    }) => subjectsApi.update(id, data),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: subjectKeys.all,
      }),
  })
}

export function useDeleteSubject() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      subjectsApi.delete(id),

    onSuccess: () =>
      queryClient.invalidateQueries({
        queryKey: subjectKeys.all,
      }),
  })
}
