import type { UserForm } from './types'
import { defineStore } from 'pinia'
import { unmaskCpf } from '@/helpers/cpf'
import { createUser, fetchUser, updateUser } from './integrations/users'

interface State {
  form: UserForm
  loading: boolean
  saving: boolean
  error: string | null
  userId: number | null
}

export const useUserFormStore = defineStore('userForm', {
  state: (): State => ({
    form: {
      name: '',
      cpf: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
    loading: false,
    saving: false,
    error: null,
    userId: null,
  }),

  getters: {
    isEdit: state => state.userId !== null,
  },

  actions: {
    reset () {
      this.form = {
        name: '',
        cpf: '',
        email: '',
        password: '',
        password_confirmation: '',
      }
      this.userId = null
      this.error = null
    },

    async load (id: number) {
      this.loading = true
      this.error = null

      try {
        const user = await fetchUser(id)
        this.userId = id
        this.form = {
          name: user.name,
          cpf: user.cpf,
          email: user.email,
          password: '',
          password_confirmation: '',
        }
      } catch {
        this.error = 'Não foi possível carregar o usuário.'
      } finally {
        this.loading = false
      }
    },

    async submit () {
      this.saving = true
      this.error = null

      const payload = {
        name: this.form.name,
        cpf: unmaskCpf(this.form.cpf),
        email: this.form.email,
        ...(this.form.password
          ? { password: this.form.password, password_confirmation: this.form.password_confirmation }
          : {}),
      }

      try {
        await (this.isEdit && this.userId ? updateUser(this.userId, payload) : createUser(payload))
      } catch (error: any) {
        this.error = error.response?.data?.message ?? 'Não foi possível salvar o usuário.'
        throw error
      } finally {
        this.saving = false
      }
    },
  },
})
