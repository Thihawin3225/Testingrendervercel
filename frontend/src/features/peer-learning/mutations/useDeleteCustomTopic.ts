import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { deleteCustomTopic } from '../api/customTopic.api'
import { peerLearningQueryKeys } from '../queries/peerLearningQueryKeys'

export function useDeleteCustomTopic() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: deleteCustomTopic,
    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: peerLearningQueryKeys.recentCustomTopics(),
      })
    },
  })
}
