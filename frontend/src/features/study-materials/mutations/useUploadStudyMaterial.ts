import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { uploadStudyMaterial } from '../api/studyMaterial.api'
import { studyMaterialQueryKeys } from '../queries/studyMaterialQueryKeys'
import type { CreateStudyMaterialPayload } from '../interfaces/studyMaterial.interface'

export function useUploadStudyMaterial() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (payload: CreateStudyMaterialPayload) => uploadStudyMaterial(payload),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: studyMaterialQueryKeys.all })
    },
  })
}
