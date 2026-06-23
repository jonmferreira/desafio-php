import type { ProductForm } from './types'
import { defineStore } from 'pinia'
import { type SelectOption, toSelectOptions } from '@/helpers/select'
import { createProduct, fetchCategories, fetchProduct, updateProduct } from './integrations/products'

interface State {
  form: ProductForm
  categoryOptions: SelectOption[]
  loading: boolean
  saving: boolean
  error: string | null
  productId: number | null
}

function emptyForm (): ProductForm {
  return {
    category_id: null,
    sku: '',
    name: '',
    description: '',
    unit: '',
    min_quantity: 0,
    price: 0,
  }
}

export const useProductFormStore = defineStore('productForm', {
  state: (): State => ({
    form: emptyForm(),
    categoryOptions: [],
    loading: false,
    saving: false,
    error: null,
    productId: null,
  }),

  getters: {
    isEdit: state => state.productId !== null,
  },

  actions: {
    reset () {
      this.form = emptyForm()
      this.productId = null
      this.error = null
    },

    async fetchCategoryOptions () {
      const categories = await fetchCategories()
      this.categoryOptions = toSelectOptions(categories, 'id', 'name')
    },

    async load (id: number) {
      this.loading = true
      this.error = null

      try {
        const product = await fetchProduct(id)
        this.productId = id
        this.form = {
          category_id: product.category_id,
          sku: product.sku,
          name: product.name,
          description: product.description ?? '',
          unit: product.unit,
          min_quantity: product.min_quantity,
          price: Number(product.price),
        }
      } catch {
        this.error = 'Não foi possível carregar o produto.'
      } finally {
        this.loading = false
      }
    },

    async submit () {
      this.saving = true
      this.error = null

      const payload = {
        category_id: this.form.category_id ?? undefined,
        sku: this.form.sku,
        name: this.form.name,
        description: this.form.description || null,
        unit: this.form.unit,
        min_quantity: this.form.min_quantity,
        price: this.form.price,
      }

      try {
        await (this.isEdit && this.productId ? updateProduct(this.productId, payload) : createProduct(payload))
      } catch (error: any) {
        this.error = error.response?.data?.message ?? 'Não foi possível salvar o produto.'
        throw error
      } finally {
        this.saving = false
      }
    },
  },
})
