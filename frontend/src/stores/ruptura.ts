import type { RupturaItem } from '@/types'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useRupturaStore = defineStore('ruptura', {
  state: () => ({
    items: [] as RupturaItem[],
    loading: false,
  }),

  getters: {
    count: state => state.items.length,
  },

  actions: {
    async fetch () {
      this.loading = true
      try {
        const res = await api.request<RupturaItem[]>({ method: 'GET', url: '/reports/ruptura' })
        this.items = res.data
      } catch {
        // silencioso — badge simplesmente não aparece
      } finally {
        this.loading = false
      }
    },
  },
})
