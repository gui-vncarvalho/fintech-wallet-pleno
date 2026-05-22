<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const auth = useAuthStore()
const toast = useToast()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errors = ref<Record<string, string[]>>({})
const loading = ref(false)

async function submit() {
  errors.value = {}

  if (!name.value.trim()) errors.value.name = ['Informe o nome.']
  if (!email.value.trim()) errors.value.email = ['Informe o e-mail.']
  if (!password.value) errors.value.password = ['Informe a senha.']
  if (!passwordConfirmation.value) {
    errors.value.password_confirmation = ['Confirme a senha.']
  } else if (passwordConfirmation.value !== password.value) {
    errors.value.password_confirmation = ['As senhas não coincidem.']
  }
  if (Object.keys(errors.value).length) return

  loading.value = true
  try {
    await auth.register(name.value, email.value, password.value, passwordConfirmation.value)
    router.push('/')
  } catch (e: any) {
    errors.value = e.response?.data?.errors ?? {}
    if (!Object.keys(errors.value).length) {
      toast.show(e.response?.data?.message ?? 'Erro ao criar conta.', 'error')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-8 w-full max-w-sm">
      <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Criar conta</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
          <input
            v-model="name"
            type="text"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-400': errors.name }"
          />
          <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
          <input
            v-model="email"
            type="email"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-400': errors.email }"
          />
          <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
          <input
            v-model="password"
            type="password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-400': errors.password }"
          />
          <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar senha</label>
          <input
            v-model="passwordConfirmation"
            type="password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-400': errors.password_confirmation }"
          />
          <p v-if="errors.password_confirmation" class="text-xs text-red-500 mt-1">{{ errors.password_confirmation[0] }}</p>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? 'Criando...' : 'Criar conta' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-4">
        Já tem conta?
        <RouterLink to="/login" class="text-blue-600 hover:underline">Entrar</RouterLink>
      </p>
    </div>
  </div>
</template>
