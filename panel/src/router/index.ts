import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guest: true, title: 'Entrar' },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/RegisterView.vue'),
      meta: { guest: true, title: 'Criar conta' },
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { auth: true, title: 'Dashboard' },
    },
    {
      path: '/transactions',
      name: 'transactions',
      component: () => import('@/views/TransactionsView.vue'),
      meta: { auth: true, title: 'Histórico' },
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: () => (localStorage.getItem('token') ? '/' : '/login'),
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  document.title = `${to.meta.title ?? 'Wallet'} — Wallet`
  if (to.meta.auth && !token) return { name: 'login' }
  if (to.meta.guest && token) return { name: 'dashboard' }
})

export default router
