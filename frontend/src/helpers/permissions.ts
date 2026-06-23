import type { AuthUser } from '@/types'

/**
 * Padrao para esconder acoes restritas na UI: admin pode gerenciar qualquer
 * usuario, demais perfis só podem gerenciar o proprio registro (reflete a
 * UserPolicy do backend - API1/IDOR).
 */
export function canManageUser (authUser: AuthUser | null, target: AuthUser): boolean {
  if (!authUser) {
    return false
  }

  return authUser.role === 'admin' || authUser.id === target.id
}
