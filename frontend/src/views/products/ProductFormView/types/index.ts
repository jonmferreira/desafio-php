export interface ProductForm {
  category_id: number | null
  sku: string
  name: string
  description: string
  unit: string
  min_quantity: number
  price: number
}
