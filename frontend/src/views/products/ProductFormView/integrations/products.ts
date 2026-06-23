import type { CategoryListResponse, ProductPayload, ProductResponse } from './types'
import api from '@/services/api'

export function fetchProduct (id: number) {
  return api
    .request<ProductResponse>({ method: 'GET', url: `/products/${id}` })
    .then(response => response.data)
}

export function createProduct (payload: ProductPayload) {
  return api
    .request<ProductResponse>({ method: 'POST', url: '/products', data: payload })
    .then(response => response.data)
}

export function updateProduct (id: number, payload: ProductPayload) {
  return api
    .request<ProductResponse>({ method: 'PUT', url: `/products/${id}`, data: payload })
    .then(response => response.data)
}

export function fetchCategories () {
  return api
    .request<CategoryListResponse>({ method: 'GET', url: '/categories' })
    .then(response => response.data)
}
