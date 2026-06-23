import type { AuthUser, PaginatedResponse } from '@/types'

export interface UserListParams {
  page?: number
  per_page?: number
}

export type UserListResponse = PaginatedResponse<AuthUser>
