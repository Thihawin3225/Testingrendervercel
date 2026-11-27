import { useMutation, useQueryClient } from '@tanstack/vue-query'

import { subjectOfferingsApi } from '../api/subjectOfferings.api'
import { subjectOfferingKeys } from '../queries/subjectOffering.queries'

import type {
  SubjectOfferingFormData,
  CreateSubjectWithOfferingPayload,
} from '../interfaces/subjectOffering.interface'

export function useCreateSubjectOffering() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: SubjectOfferingFormData) =>
      subjectOfferingsApi.create(data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: subjectOfferingKeys.all,
      })
    },
  })
}

export function useCreateSubjectWithOffering() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (data: CreateSubjectWithOfferingPayload) =>
      subjectOfferingsApi.createWithSubject(data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: subjectOfferingKeys.all,
      })

      // Subject list ကိုလည်း refresh လုပ်ရန်
      queryClient.invalidateQueries({
        queryKey: ['subjects'],
      })
    },
  })
}

export function useUpdateSubjectOffering() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      id,
      data,
    }: {
      id: number
      data: SubjectOfferingFormData
    }) => subjectOfferingsApi.update(id, data),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: subjectOfferingKeys.all,
      })
    },
  })
}

export function useDeleteSubjectOffering() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: number) =>
      subjectOfferingsApi.delete(id),

    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: subjectOfferingKeys.all,
      })
    },
  })
}