import type { GiroItem, RupturaItem, ValorEstoqueItem } from '@/types'
import api from '@/services/api'

export function fetchRuptura () {
  return api
    .request<RupturaItem[]>({ method: 'GET', url: '/reports/ruptura' })
    .then(r => r.data)
}

export function fetchValorEstoque () {
  return api
    .request<ValorEstoqueItem[]>({ method: 'GET', url: '/reports/valor-estoque' })
    .then(r => r.data)
}

export function fetchGiro () {
  return api
    .request<GiroItem[]>({ method: 'GET', url: '/reports/giro' })
    .then(r => r.data)
}
