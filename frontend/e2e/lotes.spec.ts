import { expect, test } from '@playwright/test'

test.describe('Lotes — Admin', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' })

  test('admin acessa a lista de lotes', async ({ page }) => {
    await page.goto('/lotes')
    await expect(page.getByRole('heading', { name: 'Lotes' })).toBeVisible()
  })

  test('lista exibe lotes seedados', async ({ page }) => {
    await page.goto('/lotes')
    await expect(page.getByText('#1').first()).toBeVisible()
  })

  test('admin consegue navegar para formulário de novo lote', async ({ page }) => {
    await page.goto('/lotes/new')
    await expect(page).toHaveURL(/\/lotes\/new/)
    await expect(page.getByRole('heading', { name: 'Novo Lote' })).toBeVisible()
  })

  test('detalhe do lote exibe itens e totais', async ({ page }) => {
    await page.goto('/lotes/1')
    await expect(page.getByRole('heading', { name: /Lote #1/ })).toBeVisible()
    await expect(page.getByText('Total geral')).toBeVisible()
  })
})

test.describe('Lotes — Operador', () => {
  test.use({ storageState: 'e2e/.auth/operator.json' })

  test('operador acessa a lista de lotes', async ({ page }) => {
    await page.goto('/lotes')
    await expect(page.getByRole('heading', { name: 'Lotes' })).toBeVisible()
  })

  test('operador consegue criar novo lote', async ({ page }) => {
    await page.goto('/lotes/new')
    await expect(page.getByRole('heading', { name: 'Novo Lote' })).toBeVisible()
  })
})
