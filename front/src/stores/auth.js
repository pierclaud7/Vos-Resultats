import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/index.js'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => user.value !== null)

  async function fetchMe() {
    try {
      const res = await authService.me()
      if (res.data.authenticated) {
        user.value = res.data.user
      } else {
        user.value = null
      }
    } catch {
      user.value = null
    }
  }

  async function login(email, password) {
    loading.value = true
    try {
      const res = await authService.login(email, password)
      user.value = res.data.user
      return { success: true }
    } catch (err) {
      return { success: false, error: err.response?.data?.error || 'Erreur de connexion.' }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authService.logout()
    } finally {
      user.value = null
    }
  }

  return { user, loading, isAuthenticated, fetchMe, login, logout }
})
