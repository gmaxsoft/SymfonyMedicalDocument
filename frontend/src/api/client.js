import axios from 'axios'

const baseURL = import.meta.env.VITE_API_BASE_URL || ''

export const api = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const t = localStorage.getItem('jwt')
  if (t) {
    config.headers.Authorization = `Bearer ${t}`
  }
  return config
})

export default api
