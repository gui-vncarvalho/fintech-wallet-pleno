<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useWalletStore } from '@/stores/wallet'

const router = useRouter()
const auth = useAuthStore()
const wallet = useWalletStore()

const amount = ref('')
const feedback = ref('')
const feedbackType = ref<'success' | 'error'>('success')
const loading = ref(false)

onMounted(() => wallet.fetchDashboard())

async function handleOperation(type: 'deposit' | 'withdraw') {
  feedback.value = ''
  const value = parseFloat(amount.value)
  if (!value || value <= 0) {
    feedback.value = 'Informe um valor válido.'
    feedbackType.value = 'error'
    return
  }
  loading.value = true
  try {
    if (type === 'deposit') await wallet.deposit(value)
    else await wallet.withdraw(value)
    amount.value = ''
    feedback.value = type === 'deposit' ? 'Depósito realizado!' : 'Saque realizado!'
    feedbackType.value = 'success'
  } catch (e: any) {
    feedback.value = e.response?.data?.message ?? 'Erro na operação.'
    feedbackType.value = 'error'
  } finally {
    loading.value = false
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

function formatBRL(value: number) {
  return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow px-6 py-4 flex items-center justify-between">
      <h1 class="text-lg font-bold text-gray-800">Wallet</h1>
      <div class="flex items-center gap-4">
        <RouterLink to="/transactions" class="text-sm text-blue-600 hover:underline">Histórico</RouterLink>
        <button @click="handleLogout" class="text-sm text-gray-500 hover:text-gray-700">Sair</button>
      </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8 space-y-6">
      <div v-if="wallet.dashboard" class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow p-5">
          <p class="text-xs text-gray-500 mb-1">Saldo atual</p>
          <p class="text-2xl font-bold text-gray-800">{{ formatBRL(wallet.dashboard.balance) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5">
          <p class="text-xs text-gray-500 mb-1">Depositado no mês</p>
          <p class="text-xl font-semibold text-green-600">{{ formatBRL(wallet.dashboard.deposited_month) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5">
          <p class="text-xs text-gray-500 mb-1">Sacado no mês</p>
          <p class="text-xl font-semibold text-red-500">{{ formatBRL(wallet.dashboard.withdrawn_month) }}</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Nova operação</h2>

        <div class="flex gap-2 mb-3">
          <input
            v-model="amount"
            type="number"
            step="0.01"
            min="0.01"
            placeholder="R$ 0,00"
            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <button
            @click="handleOperation('deposit')"
            :disabled="loading"
            class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50"
          >
            Depositar
          </button>
          <button
            @click="handleOperation('withdraw')"
            :disabled="loading"
            class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 disabled:opacity-50"
          >
            Sacar
          </button>
        </div>

        <p v-if="feedback" :class="feedbackType === 'success' ? 'text-green-600' : 'text-red-500'" class="text-sm">
          {{ feedback }}
        </p>
      </div>

      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Últimas transações</h2>
        <div v-if="wallet.dashboard?.last_transactions.length" class="divide-y">
          <div
            v-for="tx in wallet.dashboard.last_transactions"
            :key="tx.id"
            class="flex items-center justify-between py-3"
          >
            <div>
              <span
                :class="tx.type === 'credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                class="text-xs font-medium px-2 py-0.5 rounded-full"
              >
                {{ tx.type === 'credit' ? 'Crédito' : 'Débito' }}
              </span>
              <p class="text-xs text-gray-400 mt-1">{{ tx.created_at }}</p>
            </div>
            <div class="text-right">
              <p :class="tx.type === 'credit' ? 'text-green-600' : 'text-red-500'" class="font-semibold text-sm">
                {{ tx.type === 'credit' ? '+' : '-' }}{{ formatBRL(tx.amount) }}
              </p>
              <p class="text-xs text-gray-400">Saldo: {{ formatBRL(tx.balance_after) }}</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400">Nenhuma transação ainda.</p>
      </div>
    </main>
  </div>
</template>
