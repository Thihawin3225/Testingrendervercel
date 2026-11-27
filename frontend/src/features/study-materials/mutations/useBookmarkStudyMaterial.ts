import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { http } from '@/services/http'
import { studyMaterialQueryKeys } from '../queries/studyMaterialQueryKeys'

async function bookmarkStudyMaterial(id: number): Promise<void> {
  await http.post(`/study-materials/${id}/bookmark`)
}

export function useBookmarkStudyMaterial() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: bookmarkStudyMaterial,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: studyMaterialQueryKeys.all })
    },
  })
}
