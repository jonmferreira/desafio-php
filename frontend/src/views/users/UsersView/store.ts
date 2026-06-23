import type { AuthUser } from '@/types'
import { defineStore } from 'pinia'
import { type DataTableOptions, toPaginationParams } from '@/helpers/pagination'
import { deleteUser, fetchUsers } from './integrations/users'

interface State {
  items: AuthUser[]
  loading: boolean
  error: string | null
  pagination: {
    page: number
    total: number
    perPage: number
  }
}

const DEFAULT_OPTIONS: DataTableOptions = { page: 1, itemsPerPage: 15 }

export const useUsersViewStore = defineStore('usersView', {
  state: (): State => ({
    items: [],
    loading: false,
    error: null,
    pagination: { page: 1, total: 0, perPage: 15 },
  }),

  actions: {
    async fetchAll (options: DataTableOptions = DEFAULT_OPTIONS) {
      this.loading = true
      this.error = null

      try {
        const response = await fetchUsers(toPaginationParams(options))
        this.items = response.data
        this.pagination = {
          page: response.current_page,
          total: response.total,
          perPage: response.per_page,
        }
      } catch {
        this.error = 'Não foi possível carregar os usuários.'
      } finally {
        this.loading = false
      }
    },

    async remove (id: number) {
      await deleteUser(id)
      await this.fetchAll({ page: this.pagination.page, itemsPerPage: this.pagination.perPage })
    },
  },
})
