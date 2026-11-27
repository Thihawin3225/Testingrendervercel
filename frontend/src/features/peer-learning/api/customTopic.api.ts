import { http } from '@/services/http'
import type { LaravelResourceResponse } from '../interfaces/apiResponse.interface'
import type {
  CreateCustomTopicPayload,
  CustomTopic,
} from '../interfaces/customTopic.interface'

function extractTopicList(payload: unknown): CustomTopic[] | null {
  if (Array.isArray(payload)) {
    return payload as CustomTopic[]
  }

  if (payload && typeof payload === 'object' && 'data' in payload) {
    return extractTopicList(payload.data)
  }

  return null
}

export async function fetchRecentCustomTopics(): Promise<CustomTopic[]> {
  const response = await http.get<unknown>(
    '/peer-learning/custom-topics/recent',
  )
  const topics = extractTopicList(response.data)

  if (!topics) {
    throw new Error('The recent topics endpoint returned an unexpected response.')
  }

  return topics
}

export async function createCustomTopic(
  payload: CreateCustomTopicPayload,
): Promise<CustomTopic> {
  const response = await http.post<LaravelResourceResponse<CustomTopic>>(
    '/peer-learning/custom-topics',
    payload,
  )

  return response.data.data
}

export async function deleteCustomTopic(id: number): Promise<void> {
  await http.delete(`/peer-learning/custom-topics/${id}`)
}
