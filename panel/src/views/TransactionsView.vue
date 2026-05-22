<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useWalletStore } from '@/stores/wallet'
import AppLayout from '@/components/AppLayout.vue'
import { ArrowDownCircle, ArrowUpCircle, SlidersHorizontal } from 'lucide-vue-next'

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

function formatDate(dateStr: string) {
  const parts = dateStr.split(' ')
  const [y, m, d] = (parts[0] ?? '').split('-')
  return `${d}/${m}/${y} ${parts[1] ?? ''}`
}
</script>

<template>
  <AppLayout>
    <div v-if="wallet.loadingTransactions && !wallet.transactions" class="flex justify-center items-center py-24">
      <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
    </div>

    <div v-else class="space-y-6">
      <div class="bg-white rounded-2xl shadow p-5">
        <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-wrap sm:items-end">
          <div class="col-span-2 sm:col-auto">
            <label class="block text-xs text-gray-500 mb-1">Tipo</label>
            <select
              v-model="filterType"
              class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
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
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Até</label>
            <input
              v-model="filterEnd"
              type="date"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <button
            @click="fetch()"
            class="col-span-2 sm:col-auto flex items-center justify-center gap-1.5 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700"
          >
            <SlidersHorizontal class="w-3.5 h-3.5" />
            Filtrar
          </button>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow divide-y">
        <div
          v-for="tx in wallet.transactions?.data"
          :key="tx.id"
          class="flex items-center justify-between px-6 py-4"
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
    </div>
  </AppLayout>
</template>
