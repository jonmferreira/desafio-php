import { expect, test } from '@playwright/test'
import { ADMIN, login } from './helpers/auth'

test.describe('Login direto', () => {
  test('credenciais válidas redirecionam para dashboard', async ({ page }) => {
    await login(page, ADMIN.email, ADMIN.password)
    await expect(page).toHaveURL(/\/dashboard/)
  })

  test('credenciais inválidas exibem alerta de erro', async ({ page }) => {
    await page.goto('/login')
    await page.locator('input[name="email"]').fill('invalido@systock.com.br')
    await page.locator('input[name="password"]').fill('errada')
    await page.locator('button[type="submit"]').click()
    await expect(page.locator('.v-alert')).toBeVisible()
  })

  test('acesso sem autenticação redireciona para /login', async ({ page }) => {
    await page.goto('/dashboard')
    await expect(page).toHaveURL(/\/login/)
  })
})

test.describe('Sessão autenticada', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' })

  test('usuário autenticado não consegue acessar /login (redirecionado)', async ({ page }) => {
    await page.goto('/login')
    await expect(page).toHaveURL(/\/dashboard/)
  })

  test('logout redireciona para /login', async ({ page }) => {
    await page.goto('/dashboard')
    await page.locator('[title="Sair"]').click()
    await expect(page).toHaveURL(/\/login/)
  })
})
