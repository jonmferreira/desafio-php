import type { StockFlowEntry, ValorEstoqueItem } from '@/types'
import api from '@/services/api'

export function fetchValorEstoque () {
  return api
    .request<ValorEstoqueItem[]>({ method: 'GET', url: '/reports/valor-estoque' })
    .then(r => r.data)
}

export function fetchStockFlow (days = 30) {
  return api
    .request<StockFlowEntry[]>({ method: 'GET', url: '/reports/stock-flow', params: { days } })
    .then(r => r.data)
}
