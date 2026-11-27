import axios from 'axios'

function responseMessage(data: unknown): string | null {
  if (!data || typeof data !== 'object' || !('message' in data)) return null

  const message = data.message
  return typeof message === 'string' && message.trim() ? message : null
}

function validationMessage(data: unknown): string | null {
  if (!data || typeof data !== 'object' || !('errors' in data)) return null

  const errors = (data as { errors?: unknown }).errors
  if (!errors || typeof errors !== 'object') return null

  for (const value of Object.values(errors as Record<string, unknown>)) {
    if (!Array.isArray(value)) continue

    const message = value.find(
      (entry): entry is string => typeof entry === 'string' && Boolean(entry.trim()),
    )

    if (message) return message
  }

  return null
}

export function getStudyMaterialApiErrorMessage(
  error: unknown,
  action: string,
): string {
  if (!axios.isAxiosError(error)) {
    return `Could not ${action}. Please try again.`
  }

  const status = error.response?.status

  if (!error.response) {
    return 'Could not reach the server. Check that the backend is running and try again.'
  }

  if (status === 401) {
    return 'Please sign in again before using Study Materials.'
  }

  if (status === 403) {
    return 'An active student account is required to use Study Materials.'
  }

  if (status === 422) {
    return validationMessage(error.response.data)
      ?? responseMessage(error.response.data)
      ?? 'Please check the form and try again.'
  }

  if (status !== undefined && status >= 500) {
    return `The server could not ${action}. Check the Laravel log for details.`
  }

  return responseMessage(error.response.data) ?? `Could not ${action} (HTTP ${status}).`
}
