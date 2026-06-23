import { expect, test } from '@playwright/test'
import { ADMIN, login, OPERATOR } from './helpers/auth'

// ─── 1. XSS stored ───────────────────────────────────────────────────────────
test.describe('Segurança — XSS stored', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' })

  test('payload XSS em nome de produto é escapado, não executado', async ({ page }) => {
    const xssPayload = '<img src=x onerror="window.__xss=true">'

    await page.route('**/api/products**', async route => {
      const response = await route.fetch()
      const json = await response.json()
      const fakeItem = {
        id: 9999,
        name: xssPayload,
        sku: 'XSS-001',
        stock: 0,
        min_quantity: 1,
        price: 10,
        unit: 'un',
        category: { id: 1, name: 'Teste' },
      }
      json.data = json.data ? [fakeItem, ...json.data] : [fakeItem]
      await route.fulfill({ json })
    })

    await page.goto('/products')

    const xssExecuted = await page.evaluate(() => (window as any).__xss)
    expect(xssExecuted).toBeFalsy()

    await expect(page.getByText('<img src=x', { exact: false })).toBeVisible()
  })
})

// ─── 2. Sessão destruída após logout ─────────────────────────────────────────
test.describe('Segurança — sessão destruída após logout', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' })

  test('logout invalida a sessão — acesso posterior a área protegida redireciona para /login', async ({ page }) => {
    await page.goto('/dashboard')
    await expect(page).toHaveURL(/\/dashboard/)

    await page.locator('[title="Sair"]').click()
    await expect(page).toHaveURL(/\/login/)

    await page.goto('/dashboard')
    await expect(page).toHaveURL(/\/login/)
  })
})

// ─── 3. Escalada de role via localStorage ─────────────────────────────────────
test.describe('Segurança — escalada de role via localStorage', () => {
  test.use({ storageState: 'e2e/.auth/operator.json' })

  test('adulterar role no localStorage não expõe dados de admin (API bloqueia)', async ({ page }) => {
    await page.goto('/dashboard')

    await page.evaluate(() => {
      // Suporte ao formato antigo (chaves separadas) e novo (pinia-plugin-persistedstate)
      const authRaw = localStorage.getItem('auth')
      if (authRaw) {
        const state = JSON.parse(authRaw)
        if (state.user) {
          state.user.role = 'admin'
        }
        localStorage.setItem('auth', JSON.stringify(state))
      } else {
        const userRaw = localStorage.getItem('user')
        if (userRaw) {
          const user = JSON.parse(userRaw)
          user.role = 'admin'
          localStorage.setItem('user', JSON.stringify(user))
        }
      }
    })

    await page.goto('/users')

    await expect(page.locator('.v-alert')).toBeVisible()
  })
})

// ─── 4. Open redirect bloqueado ───────────────────────────────────────────────
test.describe('Segurança — open redirect', () => {
  test('parâmetro redirect externo é ignorado após login', async ({ page }) => {
    // Mock do login para não consumir cota do throttle:5,1 — o que testamos
    // aqui é o comportamento do router, não da API.
    await page.route('**/api/login', async route => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          token: 'fake-token-open-redirect-test',
          user: { id: 1, name: 'Admin', email: ADMIN.email, role: 'admin', cpf: '11144477735' },
        }),
      })
    })

    await page.goto('/login?redirect=https://evil.com')
    await page.locator('input[name="email"]').fill(ADMIN.email)
    await page.locator('input[name="password"]').fill(ADMIN.password)
    await page.locator('button[type="submit"]').click()
    await page.waitForURL('**/dashboard')

    expect(page.url()).not.toContain('evil.com')
    await expect(page).toHaveURL(/\/dashboard/)
  })
})

// ─── 5. IDOR — produto inexistente não expõe dados ───────────────────────────
test.describe('Segurança — IDOR produto inexistente', () => {
  test.use({ storageState: 'e2e/.auth/admin.json' })

  test('acesso a /products/99999/edit exibe erro, sem dados expostos', async ({ page }) => {
    await page.goto('/products/99999/edit')

    await expect(page.locator('.v-alert')).toBeVisible()

    const skuField = page.getByLabel('SKU')
    const skuValue = await skuField.inputValue().catch(() => '')
    expect(skuValue).toBe('')
  })
})
