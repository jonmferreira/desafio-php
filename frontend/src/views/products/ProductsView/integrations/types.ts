import type { Category, PaginatedResponse, Product, StockMovement } from '@/types'

export interface ProductListParams {
  page?: number
  per_page?: number
  category_id?: number | null
  status?: 'ruptura' | 'ok' | null
  price_min?: number | null
  price_max?: number | null
}

export type ProductListResponse = PaginatedResponse<Product>

export type CategoryListResponse = Category[]

export interface MovementPayload {
  type: 'in' | 'out'
  quantity: number
  reason: string
}

export type MovementResponse = StockMovement
