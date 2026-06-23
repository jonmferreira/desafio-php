import type { Category, Product } from '@/types'

export interface ProductPayload {
  category_id?: number
  sku?: string
  name?: string
  description?: string | null
  unit?: string
  min_quantity?: number
  price?: number
}

export type ProductResponse = Product

export type CategoryListResponse = Category[]
