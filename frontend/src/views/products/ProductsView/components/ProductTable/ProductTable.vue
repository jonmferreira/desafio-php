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

    <template #item.quantity="{ item }">
      <stock-badge :min-quantity="item.min_quantity" :quantity="item.quantity" :unit="item.unit" />
    </template>

    <template #item.price="{ item }">
      {{ formatCurrency(item.price) }}
    </template>

    <template #item.actions="{ item }">
      <v-btn
        icon="mdi-swap-vertical"
        size="small"
        title="Registrar movimentação"
        variant="text"
        @click="$emit('movement', item)"
      />

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
  import StockBadge from '@/components/products/StockBadge/StockBadge.vue'

  export default {
    name: 'ProductTable',

    components: { StockBadge },

    props: {
      items: { type: Array as () => Product[], required: true },
      loading: { type: Boolean, default: false },
      total: { type: Number, default: 0 },
      page: { type: Number, default: 1 },
      perPage: { type: Number, default: 15 },
      currentUser: { type: Object as () => AuthUser | null, default: null },
    },

    emits: ['edit', 'delete', 'movement', 'update:options'],

    data () {
      return {
        headers: [
          { title: 'SKU', key: 'sku' },
          { title: 'Nome', key: 'name' },
          { title: 'Categoria', key: 'category', sortable: false },
          { title: 'Estoque', key: 'quantity', sortable: false },
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
