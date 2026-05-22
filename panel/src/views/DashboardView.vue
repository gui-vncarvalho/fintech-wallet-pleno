<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useWalletStore } from '@/stores/wallet'
import { useToast } from '@/composables/useToast'
import AppLayout from '@/components/AppLayout.vue'
import { Wallet, TrendingUp, TrendingDown, ArrowDownCircle, ArrowUpCircle, Plus, Minus } from 'lucide-vue-next'

const wallet = useWalletStore()
const toast = useToast()

const amount = ref('')
const amountError = ref('')
const loading = ref(false)

onMounted(() => wallet.fetchDashboard())

async function handleOperation(type: 'deposit' | 'withdraw') {
  amountError.value = ''
  const value = parseFloat(amount.value)

  if (!value || value < 0.01) {
    amountError.value = 'Informe um valor mínimo de R$ 0,01.'
    return
  }

  loading.value = true
  try {
    if (type === 'deposit') await wallet.deposit(value)
    else await wallet.withdraw(value)
    amount.value = ''
    toast.show(type === 'deposit' ? 'Depósito realizado com sucesso!' : 'Saque realizado com sucesso!')
  } catch (e: any) {
    toast.show(e.response?.data?.message ?? 'Erro ao realizar operação.', 'error')
  } finally {
    loading.value = false
  }
}

function formatBRL(value: number) {
  return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

function formatDate(dateStr: string) {
  const parts = dateStr.split(' ')
  const [y, m, d] = (parts[0] ?? '').split('-')
  return `${d}/${m}/${y} ${parts[1] ?? ''}`
}
</script>

<template>
  <AppLayout>
    <div v-if="wallet.loadingDashboard" class="flex justify-center items-center py-24">
      <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
    </div>

    <div v-else class="space-y-6">
      <div v-if="wallet.dashboard" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow p-5">
          <div class="flex items-center gap-1.5 mb-1">
            <Wallet class="w-3.5 h-3.5 text-gray-400" />
            <p class="text-xs text-gray-500">Saldo atual</p>
          </div>
          <p class="text-2xl font-bold text-gray-800">{{ formatBRL(wallet.dashboard.balance) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5">
          <div class="flex items-center gap-1.5 mb-1">
            <TrendingUp class="w-3.5 h-3.5 text-green-500" />
            <p class="text-xs text-gray-500">Depositado no mês</p>
          </div>
          <p class="text-xl font-semibold text-green-600">{{ formatBRL(wallet.dashboard.deposited_month) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5">
          <div class="flex items-center gap-1.5 mb-1">
            <TrendingDown class="w-3.5 h-3.5 text-red-400" />
            <p class="text-xs text-gray-500">Sacado no mês</p>
          </div>
          <p class="text-xl font-semibold text-red-500">{{ formatBRL(wallet.dashboard.withdrawn_month) }}</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Nova operação</h2>

        <div class="flex flex-col sm:flex-row gap-2 mb-1">
          <input
            v-model="amount"
            type="number"
            step="0.01"
            min="0.01"
            placeholder="R$ 0,00"
            :class="amountError ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500'"
            class="w-full sm:flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2"
          />
          <div class="flex gap-2">
            <button
              @click="handleOperation('deposit')"
              :disabled="loading"
              class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50"
            >
              <Plus class="w-3.5 h-3.5" />
              {{ loading ? '...' : 'Depositar' }}
            </button>
            <button
              @click="handleOperation('withdraw')"
              :disabled="loading"
              class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 disabled:opacity-50"
            >
              <Minus class="w-3.5 h-3.5" />
              {{ loading ? '...' : 'Sacar' }}
            </button>
          </div>
        </div>

        <p v-if="amountError" class="text-xs text-red-500 mt-1">{{ amountError }}</p>
      </div>

      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Últimas transações</h2>
        <div v-if="wallet.dashboard?.last_transactions.length" class="divide-y">
          <div
            v-for="tx in wallet.dashboard.last_transactions"
            :key="tx.id"
            class="flex items-center justify-between py-3"
          >
            <div class="flex items-center gap-2.5">
              <div
                :class="tx.type === 'credit' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500'"
                class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
              >
                <ArrowDownCircle v-if="tx.type === 'credit'" class="w-4 h-4" />
                <ArrowUpCircle v-else class="w-4 h-4" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-700">{{ tx.type === 'credit' ? 'Crédito' : 'Débito' }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(tx.created_at) }}</p>
              </div>
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
    </div>
  </AppLayout>
</template>
