import { ref } from 'vue'

const successMessage = ref('')
const pageError = ref('')

let messageTimer: ReturnType<typeof setTimeout> | null = null

function resetTimer() {
  if (messageTimer) {
    clearTimeout(messageTimer)
    messageTimer = null
  }
}

function showSuccess(text: string) {
  resetTimer()

  pageError.value = ''
  successMessage.value = text

  messageTimer = setTimeout(() => {
    successMessage.value = ''
    messageTimer = null
  }, 2000)
}

function showError(text: string) {
  resetTimer()

  successMessage.value = ''
  pageError.value = text

  messageTimer = setTimeout(() => {
    pageError.value = ''
    messageTimer = null
  }, 2000)
}

function clearSuccess() {
  successMessage.value = ''
}

function clearError() {
  pageError.value = ''
}

function clearMessages() {
  resetTimer()
  successMessage.value = ''
  pageError.value = ''
}

export function usePageMessage() {
  return {
    successMessage,
    pageError,
    showSuccess,
    showError,
    clearSuccess,
    clearError,
    clearMessages,
  }
}
