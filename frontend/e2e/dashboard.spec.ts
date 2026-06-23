import { test, expect } from '@playwright/test'

test.use({ storageState: 'e2e/.auth/admin.json' })

test.describe('Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard')
  })

  test('exibe os três cards de estatísticas', async ({ page }) => {
    await expect(page.getByText(/produtos? em ruptura/)).toBeVisible()
    await expect(page.getByText('valor total do estoque')).toBeVisible()
    await expect(page.getByText('itens movimentados (30 dias)')).toBeVisible()
  })

  test('card de ruptura é clicável e leva para relatórios', async ({ page }) => {
    await page.waitForLoadState('networkidle')
    await page.getByText(/produtos? em ruptura/).click()
    await expect(page).toHaveURL(/\/reports/, { timeout: 10000 })
  })

  test('nav drawer está visível com links principais', async ({ page }) => {
    await expect(page.getByRole('link', { name: 'Dashboard', exact: true })).toBeVisible()
    await expect(page.getByRole('link', { name: 'Produtos', exact: true })).toBeVisible()
    await expect(page.getByRole('link', { name: 'Relatórios', exact: true })).toBeVisible()
  })
})
