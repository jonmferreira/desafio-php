import type { AuthUser } from '@/types'

export interface UserPayload {
  name?: string
  cpf?: string
  email?: string
  password?: string
  password_confirmation?: string
}

export type UserResponse = AuthUser
