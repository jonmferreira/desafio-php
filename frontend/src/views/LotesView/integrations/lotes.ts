import type { Avaria, Lote, LoteDetalhe, LoteItem, PaginatedResponse, Saida } from '@/types'
import api from '@/services/api'

export interface CreateLotePayload {
  user_id: number
  frete?: number
}

export interface CreateLoteItemPayload {
  product_id: number
  quantidade_fardos: number
  itens_por_fardo: number
  valor_unitario: number
}

export interface UpdateLoteItemPayload {
  quantidade_fardos?: number
  itens_por_fardo?: number
  valor_unitario?: number
}

export interface CreateAvariaPayload {
  descricao: string
  valor: number
}

export interface CreateSaidaPayload {
  lote_id: number
  product_id: number
  quantidade_fardos: number
  motivo?: string
}

export async function fetchLotes (params?: { user_id?: number, page?: number, per_page?: number }): Promise<PaginatedResponse<Lote>> {
  const { data } = await api.get<PaginatedResponse<Lote>>('/lotes', { params })
  return data
}

export async function createLote (payload: CreateLotePayload): Promise<Lote> {
  const { data } = await api.post<Lote>('/lotes', payload)
  return data
}

export async function fetchLote (id: number): Promise<LoteDetalhe> {
  const { data } = await api.get<LoteDetalhe>(`/lotes/${id}`)
  return data
}

export async function fetchLoteVolume (id: number): Promise<{ lote_id: number, peso_total: number, total_fardos: number }> {
  const { data } = await api.get(`/lotes/${id}/volume`)
  return data
}

export async function deleteLote (id: number): Promise<void> {
  await api.delete(`/lotes/${id}`)
}

export async function addLoteItem (loteId: number, payload: CreateLoteItemPayload): Promise<LoteItem> {
  const { data } = await api.post<LoteItem>(`/lotes/${loteId}/items`, payload)
  return data
}

export async function updateLoteItem (loteId: number, productId: number, payload: UpdateLoteItemPayload): Promise<LoteItem> {
  const { data } = await api.put<LoteItem>(`/lotes/${loteId}/items/${productId}`, payload)
  return data
}

export async function removeLoteItem (loteId: number, productId: number): Promise<void> {
  await api.delete(`/lotes/${loteId}/items/${productId}`)
}

export async function addAvaria (loteId: number, payload: CreateAvariaPayload): Promise<Avaria> {
  const { data } = await api.post<Avaria>(`/lotes/${loteId}/avarias`, payload)
  return data
}

export async function removeAvaria (loteId: number, avariaId: number): Promise<void> {
  await api.delete(`/lotes/${loteId}/avarias/${avariaId}`)
}

export async function createSaida (payload: CreateSaidaPayload): Promise<Saida> {
  const { data } = await api.post<Saida>('/saidas', payload)
  return data
}
