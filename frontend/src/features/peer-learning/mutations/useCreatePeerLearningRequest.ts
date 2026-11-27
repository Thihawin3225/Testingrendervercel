import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createPeerLearningRequest } from '../api/peerLearningRequest.api'
import { peerLearningQueryKeys } from '../queries/peerLearningQueryKeys'

export function useCreatePeerLearningRequest() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createPeerLearningRequest,
    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: peerLearningQueryKeys.requests(),
      })
    },
  })
}
