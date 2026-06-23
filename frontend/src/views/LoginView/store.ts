import type { LoginForm } from './types'
import { defineStore } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { login } from './integrations/auth'

interface State {
  loading: boolean
  error: string | null
}

export const useLoginStore = defineStore('login', {
  state: (): State => ({
    loading: false,
    error: null,
  }),

  actions: {
    async login (form: LoginForm) {
      this.loading = true
      this.error = null

      try {
        const { token, user } = await login(form)
        const auth = useAuthStore()
        auth.setSession(token, user)
      } catch (error: any) {
        this.error = error.response?.data?.message ?? 'Não foi possível entrar. Tente novamente.'
        throw error
      } finally {
        this.loading = false
      }
    },
  },
})
