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
  peso: string
  min_fardos: number
  price: string
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
  min_fardos: number
  unit: string
  category: string
  total_fardos: number
}

export interface Lote {
  id: number
  user_id: number
  numero: number
  frete: string
  user?: { id: number, name: string }
  itens?: LoteItem[]
  avarias?: Avaria[]
  saidas?: Saida[]
  created_at: string
  updated_at: string
}

export interface LoteItem {
  lote_id: number
  product_id: number
  quantidade_fardos: number
  itens_por_fardo: number
  valor_unitario: string
  product?: { id: number, sku: string, name: string, unit: string, peso: string }
  created_at: string
  updated_at: string
}

export interface Avaria {
  id: number
  lote_id: number
  descricao: string
  valor: string
  created_at: string
  updated_at: string
}

export interface Saida {
  id: number
  lote_id: number
  product_id: number
  user_id: number
  quantidade_fardos: number
  motivo: string | null
  product?: { id: number, sku: string, name: string }
  user?: { id: number, name: string }
  lote?: { id: number, numero: number, user_id: number }
  created_at: string
  updated_at: string
}

export interface LoteDetalhe {
  lote: Lote
  total_itens: number
  total_avarias: number
  total: number
  peso_total: number
}

export interface CapitalCliente {
  id: number
  name: string
  capital: number
}

export interface EstoqueProduto {
  id: number
  sku: string
  name: string
  unit: string
  min_fardos: number
  total_fardos: number
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
