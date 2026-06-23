<template>
  <v-data-table-server
    :headers="headers"
    :items="items"
    :items-length="total"
    :items-per-page="perPage"
    :items-per-page-options="[{ value: 15, title: '15' }, { value: 30, title: '30' }, { value: 50, title: '50' }]"
    :loading="loading"
    :page="page"
    @update:options="(options) => $emit('update:options', options)"
  >
    <template #item.category="{ item }">
      {{ item.category?.name }}
    </template>

    <template #item.peso="{ item }">
      {{ Number(item.peso).toFixed(3) }} kg
    </template>

    <template #item.price="{ item }">
      {{ formatCurrency(item.price) }}
    </template>

    <template #item.actions="{ item }">
      <template v-if="isAdmin">
        <v-btn
          icon="mdi-pencil"
          size="small"
          variant="text"
          @click="$emit('edit', item)"
        />

        <v-btn
          color="error"
          icon="mdi-delete"
          size="small"
          variant="text"
          @click="$emit('delete', item)"
        />
      </template>
    </template>
  </v-data-table-server>
</template>

<script lang="ts">
  import type { AuthUser, Product } from '@/types'

  export default {
    name: 'ProductTable',

    props: {
      items: { type: Array as () => Product[], required: true },
      loading: { type: Boolean, default: false },
      total: { type: Number, default: 0 },
      page: { type: Number, default: 1 },
      perPage: { type: Number, default: 15 },
      currentUser: { type: Object as () => AuthUser | null, default: null },
    },

    emits: ['edit', 'delete', 'update:options'],

    data () {
      return {
        headers: [
          { title: 'SKU', key: 'sku' },
          { title: 'Nome', key: 'name' },
          { title: 'Categoria', key: 'category', sortable: false },
          { title: 'Peso unit.', key: 'peso', sortable: false },
          { title: 'Fardos mín.', key: 'min_fardos' },
          { title: 'Preço', key: 'price' },
          { title: 'Ações', key: 'actions', sortable: false },
        ],
      }
    },

    computed: {
      isAdmin (): boolean {
        return this.currentUser?.role === 'admin'
      },
    },

    methods: {
      formatCurrency (value: string) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value))
      },
    },
  }
</script>
