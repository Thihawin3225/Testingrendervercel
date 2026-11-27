import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deletePeerLearningRequest } from '../api/peerLearningRequest.api'
import { peerLearningQueryKeys } from '../queries/peerLearningQueryKeys'

export function useDeletePeerLearningRequest() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deletePeerLearningRequest,
    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: peerLearningQueryKeys.requests(),
      })
    },
  })
}
