import { chromium, type FullConfig } from '@playwright/test'
import { mkdir } from 'fs/promises'
import { ADMIN, OPERATOR } from './helpers/auth'

const BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:3000'

async function saveState (email: string, password: string, path: string) {
  const browser = await chromium.launch()
  const context = await browser.newContext()
  const page = await context.newPage()

  await page.goto(`${BASE_URL}/login`)
  await page.locator('input[name="email"]').fill(email)
  await page.locator('input[name="password"]').fill(password)
  await page.locator('button[type="submit"]').click()
  await page.waitForURL('**/dashboard')

  // Pré-aquece todas as rotas para que Vite conclua a otimização
  // de dependências antes dos testes (evita reloads que cancelam navegações)
  await page.goto(`${BASE_URL}/products`)
  await page.waitForLoadState('networkidle')
  await page.goto(`${BASE_URL}/reports`)
  await page.waitForLoadState('networkidle')
  await page.goto(`${BASE_URL}/dashboard`)
  await page.waitForLoadState('networkidle')

  await context.storageState({ path })
  await browser.close()
}

export default async function globalSetup (_config: FullConfig) {
  await mkdir('e2e/.auth', { recursive: true })
  await saveState(ADMIN.email, ADMIN.password, 'e2e/.auth/admin.json')
  await saveState(OPERATOR.email, OPERATOR.password, 'e2e/.auth/operator.json')
}
