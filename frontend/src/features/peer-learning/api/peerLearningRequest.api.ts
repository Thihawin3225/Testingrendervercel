import { http } from '@/services/http'
import type {
  LaravelPaginatedResponse,
  LaravelResourceResponse,
} from '../interfaces/apiResponse.interface'
import type {
  LearningNeedFeedItem,
  LearningNeedsFeedFilters,
  CreatePeerLearningRequestPayload,
  PeerLearningRequest,
  PeerLearningRequestFilters,
  UpdatePeerLearningRequestPayload,
} from '../interfaces/peerLearningRequest.interface'

export async function fetchLearningNeedsFeed(
  filters: LearningNeedsFeedFilters = {},
): Promise<LaravelPaginatedResponse<LearningNeedFeedItem>> {
  const response = await http.get<
    LaravelPaginatedResponse<LearningNeedFeedItem>
  >('/peer-learning/feed', { params: filters })

  return response.data
}

export async function fetchPeerLearningRequests(
  filters: PeerLearningRequestFilters = {},
): Promise<LaravelPaginatedResponse<PeerLearningRequest>> {
  const response = await http.get<
    LaravelPaginatedResponse<PeerLearningRequest>
  >('/peer-learning/requests', { params: filters })

  return response.data
}

export async function createPeerLearningRequest(
  payload: CreatePeerLearningRequestPayload,
): Promise<PeerLearningRequest> {
  const response = await http.post<
    LaravelResourceResponse<PeerLearningRequest>
  >('/peer-learning/requests', payload)

  return response.data.data
}

export async function updatePeerLearningRequest(
  id: number,
  payload: UpdatePeerLearningRequestPayload,
): Promise<PeerLearningRequest> {
  const response = await http.patch<LaravelResourceResponse<PeerLearningRequest>>(
    `/peer-learning/requests/${id}`,
    payload,
  )

  return response.data.data
}

export async function deletePeerLearningRequest(id: number): Promise<void> {
  await http.delete(`/peer-learning/requests/${id}`)
}
