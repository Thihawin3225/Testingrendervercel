import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteStudyMaterial } from '../api/studyMaterial.api'
import { studyMaterialQueryKeys } from '../queries/studyMaterialQueryKeys'

export function useDeleteStudyMaterial() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deleteStudyMaterial,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: studyMaterialQueryKeys.all })
    },
  })
}

