import type { StockFlowEntry, ValorEstoqueItem } from '@/types'
import { defineStore } from 'pinia'
import { fetchStockFlow, fetchValorEstoque } from './integrations/reports'

interface State {
  valorEstoque: ValorEstoqueItem[]
  stockFlow: StockFlowEntry[]
  loading: boolean
}

export const useDashboardStore = defineStore('dashboard', {
  state: (): State => ({
    valorEstoque: [],
    stockFlow: [],
    loading: false,
  }),

  getters: {
    totalValue: state => state.valorEstoque.reduce((sum, r) => sum + r.total_value, 0),
    movementCount: state => state.stockFlow.reduce((sum, r) => sum + r.total, 0),
    sparklineValues: state => state.stockFlow.map(r => r.total),
  },

  actions: {
    async load () {
      this.loading = true
      try {
        const [valorEstoque, stockFlow] = await Promise.all([
          fetchValorEstoque(),
          fetchStockFlow(30),
        ])
        this.valorEstoque = valorEstoque
        this.stockFlow = stockFlow
      } finally {
        this.loading = false
      }
    },
  },
})
