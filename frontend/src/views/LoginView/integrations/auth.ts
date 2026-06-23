import type { LoginPayload, LoginResponse } from './types'
import api from '@/services/api'

export function login (payload: LoginPayload) {
  return api
    .request<LoginResponse>({ method: 'POST', url: '/login', data: payload })
    .then(response => response.data)
}
