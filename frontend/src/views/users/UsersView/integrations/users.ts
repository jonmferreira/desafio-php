import type { UserListParams, UserListResponse } from './types'
import api from '@/services/api'

export function fetchUsers (params: UserListParams) {
  return api
    .request<UserListResponse>({ method: 'GET', url: '/users', params })
    .then(response => response.data)
}

export function deleteUser (id: number) {
  return api
    .request<void>({ method: 'DELETE', url: `/users/${id}` })
    .then(response => response.data)
}
