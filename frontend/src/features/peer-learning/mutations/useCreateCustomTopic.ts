import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createCustomTopic } from '../api/customTopic.api'
import { peerLearningQueryKeys } from '../queries/peerLearningQueryKeys'

export function useCreateCustomTopic() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: createCustomTopic,
    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: peerLearningQueryKeys.recentCustomTopics(),
      })
    },
  })
}
