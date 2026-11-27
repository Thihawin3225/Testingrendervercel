import { ref } from 'vue'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

export interface ToastItem {
  id: number
  type: ToastType
  message: string
  duration: number
}

export interface ToastOptions {
  duration?: number
}

const DEFAULT_TOAST_DURATION = 3000
const toasts = ref<ToastItem[]>([])
const timers = new Map<number, ReturnType<typeof setTimeout>>()
let nextToastId = 0

function dismiss(id: number): void {
  const timer = timers.get(id)

  if (timer !== undefined) {
    clearTimeout(timer)
    timers.delete(id)
  }

  toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

function clearAll(): void {
  timers.forEach((timer) => clearTimeout(timer))
  timers.clear()
  toasts.value = []
}

function show(type: ToastType, message: string, options: ToastOptions = {}): number {
  const requestedDuration = options.duration ?? DEFAULT_TOAST_DURATION
  const duration = Number.isFinite(requestedDuration)
    ? Math.max(0, requestedDuration)
    : DEFAULT_TOAST_DURATION
  const id = ++nextToastId

  toasts.value = [...toasts.value, { id, type, message, duration }]
  timers.set(
    id,
    setTimeout(() => dismiss(id), duration),
  )

  return id
}

export function useToast() {
  return {
    toasts,
    success: (message: string, options?: ToastOptions) => show('success', message, options),
    error: (message: string, options?: ToastOptions) => show('error', message, options),
    warning: (message: string, options?: ToastOptions) => show('warning', message, options),
    info: (message: string, options?: ToastOptions) => show('info', message, options),
    dismiss,
    clearAll,
  }
}
