import { useAuthStore } from '@/stores/auth.js'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'index',
      component: () => import('../views/IndexView.vue'),
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/analytics',
      name: 'analytics',
      component: () => import('../views/AnalyticsView.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/protected',
      name: 'protected',
      component: () => import('../views/ProtectedView.vue'),
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('../views/SettingsView.vue'),
      meta: {
        requiresAuth: true
      }
    },
  ],
})

router.beforeEach(async (to, from, next) => {

    const authStore = useAuthStore();

    if (!authStore.isInit == false) {
        await authStore.fetchUser()
    }

    const isAuth = authStore.isAuthenticated

    if (to.meta.requiresAuth && !isAuth) {
        next({ name: 'index' })
    } else {
        next()
    }
})

export default router
