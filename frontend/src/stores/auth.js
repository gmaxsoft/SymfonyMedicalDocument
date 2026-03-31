import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/client'

const STORAGE_KEY = 'jwt'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem(STORAGE_KEY) || '')
  const loading = ref(false)
  const error = ref('')

  function setToken(value) {
    token.value = value || ''
    if (value) {
      localStorage.setItem(STORAGE_KEY, value)
    } else {
      localStorage.removeItem(STORAGE_KEY)
    }
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
  }

  async function fetchMe() {
    const { data } = await api.get('/api/me')
    return data
  }

  return { token, loading, error, login, logout, fetchMe }
})
