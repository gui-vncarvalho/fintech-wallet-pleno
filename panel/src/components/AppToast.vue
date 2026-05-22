<script setup lang="ts">
import { useToast } from '@/composables/useToast'

const { toasts, dismiss } = useToast()
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-80">
      <Transition
        v-for="toast in toasts"
        :key="toast.id"
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-x-4"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-4"
        appear
      >
        <div
          :class="toast.type === 'success'
            ? 'bg-green-50 border-green-400 text-green-800'
            : 'bg-red-50 border-red-400 text-red-800'"
          class="overflow-hidden border rounded-xl shadow-md"
        >
          <div class="flex items-start gap-3 px-4 pt-3 pb-2">
            <span class="text-lg leading-none mt-0.5">
              {{ toast.type === 'success' ? '✓' : '✕' }}
            </span>
            <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
            <button @click="dismiss(toast.id)" class="text-current opacity-50 hover:opacity-100 text-lg leading-none">
              ×
            </button>
          </div>

          <div class="h-0.5 w-full bg-black/5">
            <div
              :class="toast.type === 'success' ? 'bg-green-400' : 'bg-red-400'"
              :style="{ animationDuration: `${toast.duration}ms` }"
              class="h-full w-full origin-left animate-shrink"
            />
          </div>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<style scoped>
@keyframes shrink {
  from { transform: scaleX(1); }
  to   { transform: scaleX(0); }
}
.animate-shrink {
  animation-name: shrink;
  animation-timing-function: linear;
  animation-fill-mode: forwards;
}
</style>
