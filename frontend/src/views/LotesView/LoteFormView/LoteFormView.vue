<template>
  <div>
    <h1 class="text-h5 mb-4">
      Novo Lote
    </h1>

    <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
      {{ error }}
    </v-alert>

    <v-card class="mb-4">
      <v-card-title>Dados do lote</v-card-title>

      <v-card-text>
        <v-row dense>
          <v-col v-if="isAdmin" cols="12" md="6">
            <v-select
              v-model="header.user_id"
              hide-details
              item-title="text"
              item-value="value"
              :items="userOptions"
              label="Cliente"
              required
            />
          </v-col>

          <v-col cols="12" md="6">
            <v-text-field
              v-model.number="header.frete"
              hide-details
              label="Frete (R$)"
              min="0"
              step="0.01"
              type="number"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <v-card class="mb-4">
      <v-card-title>Itens do lote</v-card-title>

      <v-card-text>
        <v-row class="mb-3" dense>
          <v-col cols="12" md="3">
            <v-select
              v-model="newItem.product_id"
              hide-details
              item-title="text"
              item-value="value"
              :items="productOptions"
              label="Produto"
            />
          </v-col>

          <v-col cols="6" md="2">
            <v-text-field
              v-model.number="newItem.quantidade_fardos"
              hide-details
              label="Qtd. fardos"
              min="1"
              type="number"
            />
          </v-col>

          <v-col cols="6" md="2">
            <v-text-field
              v-model.number="newItem.itens_por_fardo"
              hide-details
              label="Itens/fardo"
              min="1"
              type="number"
            />
          </v-col>

          <v-col cols="6" md="2">
            <v-text-field
              v-model.number="newItem.valor_unitario"
              hide-details
              label="Valor unit. (R$)"
              min="0"
              step="0.01"
              type="number"
            />
          </v-col>

          <v-col class="d-flex align-center" cols="6" md="3">
            <v-btn color="secondary" prepend-icon="mdi-plus" @click="addItem">
              Adicionar item
            </v-btn>
          </v-col>
        </v-row>

        <v-data-table
          density="compact"
          :headers="itemHeaders"
          :items="items"
          no-data-text="Nenhum item adicionado."
        >
          <template #item.subtotal="{ item }">
            {{ formatCurrency(item.valor_unitario * item.itens_por_fardo * item.quantidade_fardos) }}
          </template>

          <template #item.actions="{ item }">
            <v-btn
              color="error"
              icon="mdi-delete"
              size="small"
              variant="text"
              @click="removeItem(item.product_id)"
            />
          </template>

          <template #body.append>
            <tr v-if="items.length > 0" class="font-weight-bold">
              <td colspan="4">
                Total itens
              </td>

              <td>{{ formatCurrency(totalItens) }}</td>
              <td />
            </tr>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <div class="d-flex justify-end ga-2">
      <v-btn variant="text" @click="$router.push({ name: 'lotes' })">
        Cancelar
      </v-btn>

      <v-btn color="primary" :disabled="!canSave" :loading="saving" @click="submit">
        Salvar lote
      </v-btn>
    </div>
  </div>
</template>

<script lang="ts">
  import type { Product } from '@/types'
  import { mapState } from 'pinia'
  import api from '@/services/api'
  import { useAuthStore } from '@/stores/auth'
  import { useNotificationsStore } from '@/stores/notifications'
  import { addLoteItem, createLote } from '../integrations/lotes'

  interface NewItemForm {
    product_id: number | null
    quantidade_fardos: number
    itens_por_fardo: number
    valor_unitario: number
  }

  interface ItemRow {
    product_id: number
    product_name: string
    quantidade_fardos: number
    itens_por_fardo: number
    valor_unitario: number
  }

  export default {
    name: 'LoteFormView',

    data () {
      return {
        header: { user_id: null as number | null, frete: 0 },
        newItem: { product_id: null, quantidade_fardos: 1, itens_por_fardo: 12, valor_unitario: 0 } as NewItemForm,
        items: [] as ItemRow[],
        products: [] as Product[],
        userOptions: [] as { text: string, value: number }[],
        saving: false,
        error: null as string | null,
        itemHeaders: [
          { title: 'Produto', key: 'product_name', sortable: false },
          { title: 'Qtd. fardos', key: 'quantidade_fardos', sortable: false },
          { title: 'Itens/fardo', key: 'itens_por_fardo', sortable: false },
          { title: 'Valor unit.', key: 'valor_unitario', sortable: false },
          { title: 'Subtotal', key: 'subtotal', sortable: false },
          { title: '', key: 'actions', sortable: false },
        ],
      }
    },

    computed: {
      ...mapState(useAuthStore, ['user', 'isAdmin']),

      productOptions () {
        const usedIds = new Set(this.items.map(i => i.product_id))
        return this.products
          .filter(p => !usedIds.has(p.id))
          .map(p => ({ text: `${p.sku} — ${p.name}`, value: p.id }))
      },

      totalItens (): number {
        return this.items.reduce((sum, i) => sum + i.valor_unitario * i.itens_por_fardo * i.quantidade_fardos, 0)
      },

      canSave (): boolean {
        return (this.isAdmin ? !!this.header.user_id : true) && this.items.length > 0
      },
    },

    async mounted () {
      await Promise.all([this.loadProducts(), this.loadUsers()])
      if (!this.isAdmin && this.user) {
        this.header.user_id = this.user.id
      }
    },

    methods: {
      async loadProducts () {
        const { data } = await api.get<{ data: Product[] }>('/products', { params: { per_page: 100 } })
        this.products = data.data
      },

      async loadUsers () {
        if (!this.isAdmin) return
        const { data } = await api.get<{ data: { id: number, name: string }[] }>('/users', { params: { per_page: 100 } })
        this.userOptions = data.data.map(u => ({ text: u.name, value: u.id }))
      },

      addItem () {
        if (!this.newItem.product_id) return
        const product = this.products.find(p => p.id === this.newItem.product_id)
        if (!product) return

        this.items.push({
          product_id: product.id,
          product_name: `${product.sku} — ${product.name}`,
          quantidade_fardos: this.newItem.quantidade_fardos,
          itens_por_fardo: this.newItem.itens_por_fardo,
          valor_unitario: this.newItem.valor_unitario,
        })

        this.newItem = { product_id: null, quantidade_fardos: 1, itens_por_fardo: 12, valor_unitario: 0 }
      },

      removeItem (productId: number) {
        this.items = this.items.filter(i => i.product_id !== productId)
      },

      async submit () {
        this.saving = true
        this.error = null

        try {
          const lote = await createLote({
            user_id: this.header.user_id ?? this.user!.id,
            frete: this.header.frete || 0,
          })

          for (const item of this.items) {
            await addLoteItem(lote.id, {
              product_id: item.product_id,
              quantidade_fardos: item.quantidade_fardos,
              itens_por_fardo: item.itens_por_fardo,
              valor_unitario: item.valor_unitario,
            })
          }

          useNotificationsStore().notify('Lote criado com sucesso.')
          this.$router.push({ name: 'lotes.show', params: { id: lote.id } })
        } catch (error: any) {
          this.error = error.response?.data?.message ?? 'Não foi possível salvar o lote.'
        } finally {
          this.saving = false
        }
      },

      formatCurrency (value: number): string {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
      },
    },
  }
</script>
