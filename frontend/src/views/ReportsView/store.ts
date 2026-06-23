import type { GiroItem, RupturaItem, ValorEstoqueItem } from '@/types'
import { defineStore } from 'pinia'
import { fetchGiro, fetchRuptura, fetchValorEstoque } from './integrations/reports'

interface State {
  ruptura: RupturaItem[]
  valorEstoque: ValorEstoqueItem[]
  giro: GiroItem[]
  loading: boolean
}

export const useReportsStore = defineStore('reports', {
  state: (): State => ({
    ruptura: [],
    valorEstoque: [],
    giro: [],
    loading: false,
  }),

  actions: {
    async loadAll () {
      this.loading = true
      try {
        const [ruptura, valorEstoque, giro] = await Promise.all([
          fetchRuptura(),
          fetchValorEstoque(),
          fetchGiro(),
        ])
        this.ruptura = ruptura
        this.valorEstoque = valorEstoque
        this.giro = giro
      } finally {
        this.loading = false
      }
    },
  },
})
