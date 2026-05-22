import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

interface Transaction {
  id: number
  type: 'credit' | 'debit'
  amount: number
  balance_after: number
  created_at: string
}

interface DashboardData {
  balance: number
  deposited_month: number
  withdrawn_month: number
  last_transactions: Transaction[]
}

interface PaginatedTransactions {
  data: Transaction[]
  meta: { current_page: number; last_page: number; total: number }
}

export const useWalletStore = defineStore('wallet', () => {
  const dashboard = ref<DashboardData | null>(null)
  const transactions = ref<PaginatedTransactions | null>(null)

  async function fetchDashboard() {
    const { data } = await api.get('/dashboard')
    dashboard.value = data
  }

  async function fetchTransactions(params: Record<string, string | number> = {}) {
    const { data } = await api.get('/transactions', { params })
    transactions.value = data
  }

  async function deposit(amount: number) {
    await api.post('/wallet/deposit', { amount })
    await fetchDashboard()
  }

  async function withdraw(amount: number) {
    await api.post('/wallet/withdraw', { amount })
    await fetchDashboard()
  }

  return { dashboard, transactions, fetchDashboard, fetchTransactions, deposit, withdraw }
})
