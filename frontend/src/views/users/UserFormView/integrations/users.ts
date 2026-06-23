import type { UserPayload, UserResponse } from './types'
import type { PaginatedResponse, StockMovement } from '@/types'
import api from '@/services/api'

export function fetchUserMovements (id: number, page = 1) {
  return api
    .request<PaginatedResponse<StockMovement>>({
      method: 'GET',
      url: `/users/${id}/movements`,
      params: { page, per_page: 10 },
    })
    .then(response => response.data)
}

export function fetchUser (id: number) {
  return api
    .request<UserResponse>({ method: 'GET', url: `/users/${id}` })
    .then(response => response.data)
}

export function createUser (payload: UserPayload) {
  return api
    .request<UserResponse>({ method: 'POST', url: '/users', data: payload })
    .then(response => response.data)
}

export function updateUser (id: number, payload: UserPayload) {
  return api
    .request<UserResponse>({ method: 'PUT', url: `/users/${id}`, data: payload })
    .then(response => response.data)
}
