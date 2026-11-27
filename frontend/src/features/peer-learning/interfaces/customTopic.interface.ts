export interface CustomTopic {
  id: number
  title: string
  subtitle: string | null
  description: string | null
  is_mine: boolean
}

export interface CreateCustomTopicPayload {
  title: string
  subtitle?: string | null
  description?: string | null
}
