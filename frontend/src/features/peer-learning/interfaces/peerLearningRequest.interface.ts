export type PeerLearningRequestStatus =
  | 'open'
  | 'matched'
  | 'closed'
  | 'cancelled'

export type PeerLearningMode = 'online' | 'in_person' | 'either'

export interface PeerLearningRequest {
  id: number
  title: string
  description: string | null
  learning_goal: string | null
  preferred_mode: PeerLearningMode
  preferred_availability: string | null
  preferred_start_date: string | null
  preferred_end_date: string | null
  preferred_start_time: string | null
  preferred_end_time: string | null
  subject_offering_id: number | null
  custom_topic_id: number | null
  custom_topic: { id: number; title: string } | null
  topic_title?: string | null
  status: PeerLearningRequestStatus
  matches_count?: number
  created_at: string
  updated_at: string
}

export interface LearningNeedFeedItem extends PeerLearningRequest {
  learner: { id: number; name: string }
}

export interface LearningNeedsFeedFilters {
  search?: string
  academic_year_id?: number
  year_level_id?: number
  topic_type?: 'custom' | 'official'
  per_page?: number
  page?: number
}

export interface PeerLearningRequestFilters {
  search?: string
  status?: PeerLearningRequestStatus
  academic_year_id?: number
  year_level_id?: number
  topic_type?: 'custom' | 'official'
  per_page?: number
  page?: number
}

export interface CreatePeerLearningRequestPayload {
  subject_offering_id: number | null
  custom_topic_id: number | null
  title: string
  description: string
  learning_goal: string | null
  preferred_mode: PeerLearningMode
  preferred_availability: string | null
  preferred_start_date: string | null
  preferred_end_date: string | null
  preferred_start_time: string | null
  preferred_end_time: string | null
}

export type UpdatePeerLearningRequestPayload = CreatePeerLearningRequestPayload
