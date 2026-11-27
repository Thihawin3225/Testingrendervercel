import { useQuery } from '@tanstack/vue-query'
import { fetchRecentCustomTopics } from '../api/customTopic.api'
import { peerLearningQueryKeys } from './peerLearningQueryKeys'

export function useRecentCustomTopics() {
  return useQuery({
    queryKey: peerLearningQueryKeys.recentCustomTopics(),
    queryFn: fetchRecentCustomTopics,
  })
}
