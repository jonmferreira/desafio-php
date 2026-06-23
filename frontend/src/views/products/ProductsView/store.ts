import type { MovementPayload } from './integrations/types'
import type { Category, Product, StockFlowEntry } from '@/types'
import { defineStore } from 'pinia'
import { type DataTableOptions, toPaginationParams } from '@/helpers/pagination'
import {
  createMovement,
  deleteProduct,
  exportProducts,
  fetchCategories,
  fetchProducts,
  fetchStockFlow,
} from './integrations/products'

export interface ProductFilters {
  categoryId: number | null
  status: 'ruptura' | 'ok' | null
  priceMin: number | null
  priceMax: number | null
}

interface State {
  items: Product[]
  loading: boolean
  error: string | null
  pagination: {
    page: number
    total: number
    perPage: number
  }
  filters: ProductFilters
  categories: Category[]
  stockFlow: StockFlowEntry[]
  stockFlowLoading: boolean
  exporting: boolean
}

const DEFAULT_OPTIONS: DataTableOptions = { page: 1, itemsPerPage: 15 }

export const useProductsViewStore = defineStore('productsView', {
  state: (): State => ({
    items: [],
    loading: false,
    error: null,
    pagination: { page: 1, total: 0, perPage: 15 },
    filters: { categoryId: null, status: null, priceMin: null, priceMax: null },
    categories: [],
    stockFlow: [],
    stockFlowLoading: false,
    exporting: false,
  }),

  actions: {
    async fetchAll (options: DataTableOptions = DEFAULT_OPTIONS) {
      this.loading = true
      this.error = null

      try {
        const params = {
          ...toPaginationParams(options),
          category_id: this.filters.categoryId ?? undefined,
          status: this.filters.status ?? undefined,
          price_min: this.filters.priceMin ?? undefined,
          price_max: this.filters.priceMax ?? undefined,
        }
        const response = await fetchProducts(params)
        this.items = response.data
        this.pagination = {
          page: response.current_page,
          total: response.total,
          perPage: response.per_page,
        }
      } catch {
        this.error = 'Não foi possível carregar os produtos.'
      } finally {
        this.loading = false
      }
    },

    async applyFilters () {
      await this.fetchAll({ page: 1, itemsPerPage: this.pagination.perPage })
    },

    async loadCategories () {
      if (this.categories.length > 0) {
        return
      }
      this.categories = await fetchCategories()
    },

    async loadStockFlow () {
      this.stockFlowLoading = true
      try {
        this.stockFlow = await fetchStockFlow(30)
      } finally {
        this.stockFlowLoading = false
      }
    },

    async remove (id: number) {
      await deleteProduct(id)
      await this.fetchAll({ page: this.pagination.page, itemsPerPage: this.pagination.perPage })
    },

    async registerMovement (productId: number, payload: MovementPayload) {
      await createMovement(productId, payload)
      await this.fetchAll({ page: this.pagination.page, itemsPerPage: this.pagination.perPage })
      await this.loadStockFlow()
    },

    async exportCsv () {
      this.exporting = true
      try {
        await exportProducts()
      } finally {
        this.exporting = false
      }
    },
  },
})
