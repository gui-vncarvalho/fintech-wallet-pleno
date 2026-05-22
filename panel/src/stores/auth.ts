import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

interface User {
  id: number
  name: string
  email: string
  wallet: { id: number; balance: number }
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))

  const isAuthenticated = () => !!token.value

  async function register(name: string, email: string, password: string, password_confirmation: string) {
    const { data } = await api.post('/register', { name, email, password, password_confirmation })
    setSession(data)
  }

  async function login(email: string, password: string) {
    const { data } = await api.post('/login', { email, password })
    setSession(data)
  }

  async function logout() {
    await api.post('/logout')
    clearSession()
  }

  function setSession(data: { user: User; token: string }) {
    user.value = data.user
    token.value = data.token
    localStorage.setItem('token', data.token)
  }

  function clearSession() {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  return { user, token, isAuthenticated, register, login, logout }
})
