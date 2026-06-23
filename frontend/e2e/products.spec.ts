import { expect, test } from '@playwright/test'

test.describe('Produtos — Admin', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' })

  test('admin acessa a lista de produtos', async ({ page }) => {
    await page.goto('/products')
    await expect(page.getByRole('heading', { name: 'Produtos' })).toBeVisible()
  })

  test('admin consegue navegar para formulário de novo produto', async ({ page }) => {
    await page.goto('/products/new')
    await expect(page).toHaveURL(/\/products\/new/)
    await expect(page.getByLabel('SKU')).toBeVisible()
  })
})

test.describe('Produtos — Operador', () => {
  test.use({ storageState: 'e2e/.auth/operator.json' })

  test('operador acessa a lista de produtos (leitura)', async ({ page }) => {
    await page.goto('/products')
    await expect(page.getByRole('heading', { name: 'Produtos' })).toBeVisible()
  })

  test('operador é redirecionado ao tentar acessar /products/new', async ({ page }) => {
    await page.goto('/products/new')
    await expect(page).toHaveURL(/\/dashboard/)
  })
})
