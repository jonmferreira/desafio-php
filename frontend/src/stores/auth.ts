import type { AuthUser } from '@/types'
import { defineStore } from 'pinia'
import api from '@/services/api'

interface State {
  token: string | null
  user: AuthUser | null
}

export const useAuthStore = defineStore('auth', {
  state: (): State => ({
    token: null,
    user: null,
  }),

  getters: {
    isAuthenticated: state => !!state.token,
    isAdmin: state => state.user?.role === 'admin',
  },

  actions: {
    setSession (token: string, user: AuthUser) {
      this.token = token
      this.user = user
    },

    clearSession () {
      this.token = null
      this.user = null
    },

    async logout () {
      this.clearSession()
      api.post('/logout').catch(() => {})
    },
  },

  persist: true,
})
