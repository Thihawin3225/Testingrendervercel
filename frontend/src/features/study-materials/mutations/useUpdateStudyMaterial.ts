import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { updateStudyMaterial } from '../api/studyMaterial.api'
import { studyMaterialQueryKeys } from '../queries/studyMaterialQueryKeys'
import type { UpdateStudyMaterialPayload } from '../interfaces/studyMaterial.interface'

export function useUpdateStudyMaterial() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: updateStudyMaterial,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: studyMaterialQueryKeys.all })
    },
  })
}

