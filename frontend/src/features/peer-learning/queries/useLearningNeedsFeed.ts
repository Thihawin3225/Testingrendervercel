import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchLearningNeedsFeed } from '../api/peerLearningRequest.api'
import type { LearningNeedsFeedFilters } from '../interfaces/peerLearningRequest.interface'
import { peerLearningQueryKeys } from './peerLearningQueryKeys'

export function useLearningNeedsFeed(
  filters: MaybeRefOrGetter<LearningNeedsFeedFilters> = {},
) {
  const currentFilters = computed(() => ({ ...toValue(filters) }))

  return useQuery({
    queryKey: computed(() => peerLearningQueryKeys.feedList(currentFilters.value)),
    queryFn: () => fetchLearningNeedsFeed(currentFilters.value),
  })
}
