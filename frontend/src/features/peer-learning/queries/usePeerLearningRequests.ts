import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchPeerLearningRequests } from '../api/peerLearningRequest.api'
import type { PeerLearningRequestFilters } from '../interfaces/peerLearningRequest.interface'
import { peerLearningQueryKeys } from './peerLearningQueryKeys'

export function usePeerLearningRequests(
  filters: MaybeRefOrGetter<PeerLearningRequestFilters> = {},
) {
  const currentFilters = computed(() => ({ ...toValue(filters) }))

  return useQuery({
    queryKey: computed(() =>
      peerLearningQueryKeys.requestList(currentFilters.value),
    ),
    queryFn: () => fetchPeerLearningRequests(currentFilters.value),
  })
}
