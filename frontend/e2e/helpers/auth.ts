import type { Page } from '@playwright/test'

export const ADMIN = {
  email: 'admin@systock.com.br',
  password: 'password',
}

export const OPERATOR = {
  email: 'operador@systock.com.br',
  password: 'password',
}

export async function login (page: Page, email: string, password: string) {
  await page.goto('/login')
  await page.locator('input[name="email"]').fill(email)
  await page.locator('input[name="password"]').fill(password)
  await page.locator('button[type="submit"]').click()
  await page.waitForURL('**/dashboard')
}
