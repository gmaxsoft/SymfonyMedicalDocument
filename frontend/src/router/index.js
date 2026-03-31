import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/HomeView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/patients',
      name: 'patients',
      component: () => import('@/views/PatientsView.vue'),
      meta: { requiresAuth: true, requiresDoctor: true },
    },
    {
      path: '/patients/new',
      name: 'patient-new',
      component: () => import('@/views/PatientFormView.vue'),
      meta: { requiresAuth: true, requiresDoctor: true },
    },
    {
      path: '/patients/:id/edit',
      name: 'patient-edit',
      component: () => import('@/views/PatientFormView.vue'),
      meta: { requiresAuth: true, requiresDoctor: true },
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.token) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
  if (to.meta.guestOnly && auth.token) {
    return { name: 'home' }
  }
  if (to.meta.requiresDoctor && auth.token) {
    if (!auth.me) {
      try {
        await auth.loadMe()
      } catch {
        return { name: 'login', query: { redirect: to.fullPath } }
      }
    }
    if (!auth.isDoctor) {
      return { name: 'home' }
    }
  }
  return true
})

export default router
