export interface AuthUser {
  id: number
  name: string
  cpf: string
  email: string
  role: 'admin' | 'operator'
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface Category {
  id: number
  name: string
  created_at: string
  updated_at: string
}

export interface Product {
  id: number
  category_id: number
  category?: Category
  sku: string
  name: string
  description: string | null
  unit: string
  min_quantity: number
  price: string
  quantity: number
  created_at: string
  updated_at: string
}

export interface StockMovement {
  id: number
  product_id: number
  user_id: number
  user?: { id: number, name: string }
  product?: { id: number, name: string, sku: string }
  type: 'in' | 'out'
  quantity: number
  reason: string
  created_at: string
  updated_at: string
}

export interface StockFlowEntry {
  date: string
  total: number
}

export interface RupturaItem {
  id: number
  sku: string
  name: string
  min_quantity: number
  unit: string
  category: string
  balance: number
}

export interface ValorEstoqueItem {
  id: number
  category: string
  product_count: number
  total_value: number
}

export interface GiroItem {
  id: number
  sku: string
  name: string
  operation_count: number
  total_quantity: number
}
