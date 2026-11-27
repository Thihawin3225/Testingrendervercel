import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { updatePeerLearningRequest } from '../api/peerLearningRequest.api'
import { peerLearningQueryKeys } from '../queries/peerLearningQueryKeys'
import type { UpdatePeerLearningRequestPayload } from '../interfaces/peerLearningRequest.interface'

export function useUpdatePeerLearningRequest() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, payload }: { id: number; payload: UpdatePeerLearningRequestPayload }) =>
      updatePeerLearningRequest(id, payload),
    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: peerLearningQueryKeys.requests(),
      })
    },
  })
}
