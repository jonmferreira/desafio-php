import type {
  CategoryListResponse,
  MovementPayload,
  MovementResponse,
  ProductListParams,
  ProductListResponse,
} from './types'
import type { StockFlowEntry } from '@/types'
import api from '@/services/api'

export function fetchProducts (params: ProductListParams) {
  return api
    .request<ProductListResponse>({ method: 'GET', url: '/products', params })
    .then(response => response.data)
}

export function fetchCategories () {
  return api
    .request<CategoryListResponse>({ method: 'GET', url: '/categories' })
    .then(response => response.data)
}

export function deleteProduct (id: number) {
  return api
    .request<void>({ method: 'DELETE', url: `/products/${id}` })
    .then(response => response.data)
}

export function createMovement (productId: number, payload: MovementPayload) {
  return api
    .request<MovementResponse>({
      method: 'POST',
      url: `/products/${productId}/movements`,
      data: payload,
      headers: { 'Idempotency-Key': crypto.randomUUID() },
    })
    .then(response => response.data)
}

export function exportProducts () {
  return api
    .request<Blob>({ method: 'GET', url: '/products/export', responseType: 'blob' })
    .then(response => {
      const url = URL.createObjectURL(response.data)
      const a = document.createElement('a')
      a.href = url
      a.download = `produtos-${new Date().toISOString().slice(0, 10)}.csv`
      document.body.append(a)
      a.click()
      a.remove()
      URL.revokeObjectURL(url)
    })
}

export function fetchStockFlow (days = 30) {
  return api
    .request<StockFlowEntry[]>({ method: 'GET', url: '/reports/stock-flow', params: { days } })
    .then(response => response.data)
}
