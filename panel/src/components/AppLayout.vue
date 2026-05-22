<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Wallet, LayoutDashboard, List, LogOut, UserRound, Menu, X } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const mobileMenuOpen = ref(false)

watch(route, () => { mobileMenuOpen.value = false })

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white border-b border-gray-200 px-6 py-0 flex items-center justify-between">
      <div class="flex items-center gap-8">
        <span class="flex items-center gap-1.5 text-base font-bold text-blue-600 py-4">
          <Wallet class="w-4 h-4" />
          Wallet
        </span>

        <nav class="hidden md:flex items-center h-full">
          <RouterLink
            to="/"
            :class="route.name === 'dashboard'
              ? 'border-b-2 border-blue-600 text-blue-600'
              : 'border-b-2 border-transparent text-gray-500 hover:text-gray-800'"
            class="flex items-center gap-1.5 px-1 py-4 text-sm font-medium transition-colors"
          >
            <LayoutDashboard class="w-3.5 h-3.5" />
            Dashboard
          </RouterLink>
          <RouterLink
            to="/transactions"
            :class="route.name === 'transactions'
              ? 'border-b-2 border-blue-600 text-blue-600'
              : 'border-b-2 border-transparent text-gray-500 hover:text-gray-800'"
            class="ml-6 flex items-center gap-1.5 px-1 py-4 text-sm font-medium transition-colors"
          >
            <List class="w-3.5 h-3.5" />
            Histórico
          </RouterLink>
        </nav>
      </div>

      <div class="hidden md:flex items-center gap-3">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
            <UserRound class="w-4 h-4" />
          </div>
          <span v-if="auth.user" class="text-sm font-medium text-gray-700">{{ auth.user.name }}</span>
        </div>
        <span class="w-px h-4 bg-gray-200 block" />
        <button
          @click="handleLogout"
          class="flex items-center gap-1 text-sm text-gray-400 hover:text-red-500 transition-colors"
        >
          <LogOut class="w-3.5 h-3.5" />
          Sair
        </button>
      </div>

      <button
        @click="mobileMenuOpen = !mobileMenuOpen"
        class="md:hidden p-2 text-gray-500 hover:text-gray-800 transition-colors"
      >
        <X v-if="mobileMenuOpen" class="w-5 h-5" />
        <Menu v-else class="w-5 h-5" />
      </button>
    </header>

    <div v-if="mobileMenuOpen" class="md:hidden bg-white border-b border-gray-200">
      <nav class="px-6 py-1">
        <RouterLink
          to="/"
          :class="route.name === 'dashboard' ? 'text-blue-600' : 'text-gray-600'"
          class="flex items-center gap-2 py-3 text-sm font-medium border-b border-gray-100"
        >
          <LayoutDashboard class="w-4 h-4" />
          Dashboard
        </RouterLink>
        <RouterLink
          to="/transactions"
          :class="route.name === 'transactions' ? 'text-blue-600' : 'text-gray-600'"
          class="flex items-center gap-2 py-3 text-sm font-medium border-b border-gray-100"
        >
          <List class="w-4 h-4" />
          Histórico
        </RouterLink>
        <div class="flex items-center justify-between py-3">
          <div class="flex items-center gap-2 text-gray-600">
            <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
              <UserRound class="w-3.5 h-3.5" />
            </div>
            <span class="text-sm font-medium">{{ auth.user?.name }}</span>
          </div>
          <button
            @click="handleLogout"
            class="flex items-center gap-1 text-sm text-gray-400 hover:text-red-500 transition-colors"
          >
            <LogOut class="w-3.5 h-3.5" />
            Sair
          </button>
        </div>
      </nav>
    </div>

    <main class="max-w-2xl mx-auto px-4 py-8">
      <slot />
    </main>
  </div>
</template>
