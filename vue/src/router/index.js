import { useAuthStore } from '@/stores/auth.js'
import { createRouter, createWebHashHistory } from 'vue-router'

const router = createRouter({
  history: createWebHashHistory(import.meta.env.BASE_URL),
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
      path: '/analytics/:code',
      name: 'analytics',
      component: () => import('../views/AnalyticsView.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('../views/SettingsView.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/:code',
      name: 'link',
      component: () => import('../views/RedirectLink.vue'),
    },
  ],
})

router.beforeEach(async (to, from, next) => {

    const authStore = useAuthStore();

    if (!authStore.token) {
        await authStore.fetchUser()
    }

    const isAuth = authStore.isAuth

    if (to.meta.requiresAuth && !isAuth) {
        next({ name: 'index' })
    } else {
        next()
    }
})

export default router
