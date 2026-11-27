import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { removeStudyMaterialBookmark } from '../api/studyMaterial.api'
import { studyMaterialQueryKeys } from '../queries/studyMaterialQueryKeys'

export function useRemoveStudyMaterialBookmark() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: removeStudyMaterialBookmark,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: studyMaterialQueryKeys.all })
    },
  })
}
