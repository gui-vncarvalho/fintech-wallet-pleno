import { ref } from 'vue'

type ToastType = 'success' | 'error'

interface Toast {
  id: number
  message: string
  type: ToastType
  duration: number
}

const toasts = ref<Toast[]>([])
let counter = 0

export function useToast() {
  function show(message: string, type: ToastType = 'success', duration = 3500) {
    const id = ++counter
    toasts.value.push({ id, message, type, duration })
    setTimeout(() => dismiss(id), duration)
  }

  function dismiss(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  return { toasts, show, dismiss }
}
