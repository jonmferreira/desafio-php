import type { AuthUser } from '@/types'

export interface LoginPayload {
  email: string
  password: string
}

export interface LoginResponse {
  token: string
  user: AuthUser
}
