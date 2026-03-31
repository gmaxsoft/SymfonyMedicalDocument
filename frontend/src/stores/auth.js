import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/api/client'

const STORAGE_KEY = 'jwt'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem(STORAGE_KEY) || '')
  const loading = ref(false)
  const error = ref('')
  const me = ref(null)

  const isDoctor = computed(() => (me.value?.roles || []).includes('ROLE_DOCTOR'))
  const isPatient = computed(() => (me.value?.roles || []).includes('ROLE_PATIENT'))

  function setToken(value) {
    token.value = value || ''
    if (value) {
      localStorage.setItem(STORAGE_KEY, value)
    } else {
      localStorage.removeItem(STORAGE_KEY)
    }
  }

  async function loadMe() {
    if (!token.value) {
      me.value = null
      return null
    }
    const { data } = await api.get('/api/me')
    me.value = data
    return data
  }

  async function login(email, password) {
    loading.value = true
    error.value = ''
    try {
      const { data } = await api.post('/api/auth', { email, password })
      if (!data?.token) {
        throw new Error('No token in response')
      }
      setToken(data.token)
      await loadMe()
      return true
    } catch (e) {
      error.value =
        e.response?.data?.message ||
        e.response?.data?.detail ||
        e.message ||
        'Login failed'
      return false
    } finally {
      loading.value = false
    }
  }

  function logout() {
    setToken('')
    me.value = null
  }

  async function fetchMe() {
    return loadMe()
  }

  return {
    token,
    loading,
    error,
    me,
    isDoctor,
    isPatient,
    login,
    logout,
    fetchMe,
    loadMe,
  }
})
