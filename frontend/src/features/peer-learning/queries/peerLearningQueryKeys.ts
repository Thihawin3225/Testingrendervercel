import type {
  LearningNeedsFeedFilters,
  PeerLearningRequestFilters,
} from '../interfaces/peerLearningRequest.interface'

const peerLearningRootKey = ['peer-learning'] as const

export const peerLearningQueryKeys = {
  all: peerLearningRootKey,
  requests: () => [...peerLearningRootKey, 'requests'] as const,
  requestLists: () => [...peerLearningRootKey, 'requests', 'list'] as const,
  requestList: (filters: PeerLearningRequestFilters) =>
    [...peerLearningRootKey, 'requests', 'list', filters] as const,
  feed: () => [...peerLearningRootKey, 'feed'] as const,
  feedList: (filters: LearningNeedsFeedFilters) =>
    [...peerLearningRootKey, 'feed', 'list', filters] as const,
  recentCustomTopics: () =>
    [...peerLearningRootKey, 'custom-topics', 'recent'] as const,
  subjectOfferings: () =>
    [...peerLearningRootKey, 'subject-offerings'] as const,
}
