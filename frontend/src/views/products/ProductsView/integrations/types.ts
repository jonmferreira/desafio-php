import type { Category, PaginatedResponse, Product } from '@/types'

export interface ProductListParams {
  page?: number
  per_page?: number
  category_id?: number | null
  status?: 'critico' | 'ok' | null
  price_min?: number | null
  price_max?: number | null
}

export type ProductListResponse = PaginatedResponse<Product>

export type CategoryListResponse = Category[]
