import axios from 'axios'

function responseMessage(data: unknown): string | null {
  if (!data || typeof data !== 'object' || !('message' in data)) return null

  const message = data.message
  return typeof message === 'string' && message.trim() ? message : null
}

export function getPeerLearningApiErrorMessage(
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
    return 'Please sign in again before using Peer Learning.'
  }

  if (status === 403) {
    return 'An active student account is required to use Peer Learning.'
  }

  if (status !== undefined && status >= 500) {
    return `The server could not ${action}. Check the Laravel log for details.`
  }

  return responseMessage(error.response.data) ?? `Could not ${action} (HTTP ${status}).`
}
