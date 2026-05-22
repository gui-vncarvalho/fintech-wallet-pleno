<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const auth = useAuthStore()
const toast = useToast()

const email = ref('')
const password = ref('')
const fieldErrors = ref<Record<string, string>>({})
const loading = ref(false)

async function submit() {
  fieldErrors.value = {}

  if (!email.value.trim()) fieldErrors.value.email = 'Informe o e-mail.'
  if (!password.value) fieldErrors.value.password = 'Informe a senha.'
  if (Object.keys(fieldErrors.value).length) return

  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (e: any) {
    fieldErrors.value.password = e.response?.data?.message ?? 'Credenciais inválidas.'
    toast.show(e.response?.data?.message ?? 'Erro ao fazer login.', 'error')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-8 w-full max-w-sm">
      <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Wallet</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
          <input
            v-model="email"
            type="email"
            :class="fieldErrors.email ? 'border-red-400' : 'border-gray-300'"
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p v-if="fieldErrors.email" class="text-xs text-red-500 mt-1">{{ fieldErrors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
          <input
            v-model="password"
            type="password"
            :class="fieldErrors.password ? 'border-red-400' : 'border-gray-300'"
            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p v-if="fieldErrors.password" class="text-xs text-red-500 mt-1">{{ fieldErrors.password }}</p>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-4">
        Não tem conta?
        <RouterLink to="/register" class="text-blue-600 hover:underline">Cadastre-se</RouterLink>
      </p>
    </div>
  </div>
</template>
