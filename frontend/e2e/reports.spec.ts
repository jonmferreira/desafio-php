import { test, expect } from '@playwright/test'

test.describe('Relatórios — Admin', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' })

  test.beforeEach(async ({ page }) => {
    await page.goto('/reports')
  })

  test('exibe as três abas de relatório', async ({ page }) => {
    await expect(page.getByRole('tab', { name: /Ruptura de estoque/i })).toBeVisible()
    await expect(page.getByRole('tab', { name: /Valor por categoria/i })).toBeVisible()
    await expect(page.getByRole('tab', { name: /Giro/i })).toBeVisible()
  })

  test('aba Ruptura de estoque está ativa por padrão', async ({ page }) => {
    const tab = page.getByRole('tab', { name: /Ruptura de estoque/i })
    await expect(tab).toHaveAttribute('aria-selected', 'true')
  })

  test('navegar para aba Valor por categoria exibe tabela correta', async ({ page }) => {
    await page.getByRole('tab', { name: /Valor por categoria/i }).click()
    await expect(page.getByRole('columnheader', { name: 'Categoria' }).first()).toBeVisible()
    await expect(page.getByRole('columnheader', { name: 'Valor total' }).first()).toBeVisible()
  })

  test('navegar para aba Giro exibe tabela com colunas corretas', async ({ page }) => {
    await page.getByRole('tab', { name: /Giro/i }).click()
    await expect(page.getByRole('columnheader', { name: 'Produto' }).first()).toBeVisible()
    await expect(page.getByRole('columnheader', { name: 'Itens movimentados' }).first()).toBeVisible()
  })
})

test.describe('Relatórios — Operador', () => {
  test.use({ storageState: 'e2e/.auth/operator.json' })

  test('operador também acessa relatórios', async ({ page }) => {
    await page.goto('/reports')
    await expect(page.getByRole('tab', { name: /Ruptura de estoque/i })).toBeVisible()
  })
})
