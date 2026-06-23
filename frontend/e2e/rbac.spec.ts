import { expect, test } from '@playwright/test'

test.describe('RBAC — Admin', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' })

  test('admin vê o menu Usuários no nav drawer', async ({ page }) => {
    await page.goto('/dashboard')
    await expect(page.getByRole('link', { name: 'Usuários' })).toBeVisible()
  })

  test('admin vê link Novo produto', async ({ page }) => {
    await page.goto('/products')
    await expect(page.getByRole('link', { name: /Novo produto/i })).toBeVisible()
  })
})

test.describe('RBAC — Operador', () => {
  test.use({ storageState: 'e2e/.auth/operator.json' })

  test('operador não vê o menu Usuários', async ({ page }) => {
    await page.goto('/dashboard')
    await expect(page.getByRole('link', { name: 'Usuários' })).not.toBeVisible()
  })

  test('operador redirecionado ao acessar /users diretamente', async ({ page }) => {
    await page.goto('/users')
    await expect(page).toHaveURL(/\/dashboard/)
  })

  test('operador redirecionado ao acessar /users/new diretamente', async ({ page }) => {
    await page.goto('/users/new')
    await expect(page).toHaveURL(/\/dashboard/)
  })

  test('operador não vê link Novo produto', async ({ page }) => {
    await page.goto('/products')
    await expect(page.getByRole('link', { name: /Novo produto/i })).not.toBeVisible()
  })
})
