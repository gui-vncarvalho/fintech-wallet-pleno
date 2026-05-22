<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useWalletStore } from '@/stores/wallet'

const wallet = useWalletStore()

const filterType = ref('')
const filterStart = ref('')
const filterEnd = ref('')

onMounted(() => fetch())

function fetch(page = 1) {
  const params: Record<string, string | number> = { page }
  if (filterType.value) params.type = filterType.value
  if (filterStart.value) params.start_date = filterStart.value
  if (filterEnd.value) params.end_date = filterEnd.value
  wallet.fetchTransactions(params)
}

function formatBRL(value: number) {
  return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow px-6 py-4 flex items-center gap-4">
      <RouterLink to="/" class="text-sm text-blue-600 hover:underline">← Dashboard</RouterLink>
      <h1 class="text-lg font-bold text-gray-800">Histórico de transações</h1>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8 space-y-6">
      <div class="bg-white rounded-2xl shadow p-5 flex flex-wrap gap-3 items-end">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Tipo</label>
          <select
            v-model="filterType"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos</option>
            <option value="credit">Crédito</option>
            <option value="debit">Débito</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">De</label>
          <input
            v-model="filterStart"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Até</label>
          <input
            v-model="filterEnd"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <button
          @click="fetch()"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700"
        >
          Filtrar
        </button>
      </div>

      <div class="bg-white rounded-2xl shadow divide-y">
        <div
          v-for="tx in wallet.transactions?.data"
          :key="tx.id"
          class="flex items-center justify-between px-6 py-4"
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

        <p v-if="!wallet.transactions?.data?.length" class="text-sm text-gray-400 px-6 py-4">
          Nenhuma transação encontrada.
        </p>
      </div>

      <div v-if="wallet.transactions?.meta" class="flex justify-center gap-2">
        <button
          v-for="page in wallet.transactions.meta.last_page"
          :key="page"
          @click="fetch(page)"
          :class="page === wallet.transactions.meta.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'"
          class="w-8 h-8 rounded-lg text-sm font-medium shadow hover:opacity-80"
        >
          {{ page }}
        </button>
      </div>
    </main>
  </div>
</template>
