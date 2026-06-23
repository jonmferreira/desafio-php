<template>
  <div>
    <h1 class="text-h5 mb-4">
      Relatórios
    </h1>

    <v-tabs v-model="activeTab" color="primary">
      <v-tab value="ruptura">
        <v-badge
          v-if="ruptura.length > 0"
          class="ma-1"
          color="error"
          :content="ruptura.length"
          inline
        >
          Estoque crítico
        </v-badge>

        <span v-else>Estoque crítico</span>
      </v-tab>

      <v-tab value="valor">
        Valor por categoria
      </v-tab>

      <v-tab value="giro">
        Giro — top 10 (30 dias)
      </v-tab>
    </v-tabs>

    <v-progress-linear v-if="loading" indeterminate />

    <v-window v-model="activeTab" class="mt-4">
      <v-window-item value="ruptura">
        <v-data-table
          :headers="rupturaHeaders"
          :items="ruptura"
          :loading="loading"
          no-data-text="Nenhum produto em ruptura."
        >
          <template #item.total_fardos="{ item }">
            <span class="text-error font-weight-medium">
              {{ item.total_fardos }} fardos
            </span>
          </template>

          <template #item.min_fardos="{ item }">
            {{ item.min_fardos }} fardos
          </template>
        </v-data-table>
      </v-window-item>

      <v-window-item value="valor">
        <v-data-table
          :headers="valorHeaders"
          :items="valorEstoque"
          :loading="loading"
          no-data-text="Nenhum dado disponível."
        >
          <template #item.total_value="{ item }">
            {{ formatCurrency(item.total_value) }}
          </template>

          <template #body.append>
            <tr v-if="valorEstoque.length > 0" class="font-weight-bold">
              <td>Total</td>

              <td class="text-right">
                {{ totalProducts }}
              </td>

              <td class="text-right">
                {{ formatCurrency(totalValue) }}
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-window-item>

      <v-window-item value="giro">
        <v-data-table
          :headers="giroHeaders"
          :items="giro"
          :loading="loading"
          no-data-text="Nenhuma movimentação nos últimos 30 dias."
        >
          <template #item.index="{ index }">
            {{ index + 1 }}
          </template>
        </v-data-table>
      </v-window-item>
    </v-window>
  </div>
</template>

<script lang="ts">
  import { mapActions, mapState } from 'pinia'
  import { useReportsStore } from './store'

  export default {
    name: 'ReportsView',

    data () {
      return {
        activeTab: (this.$route.query.tab as string) || 'ruptura',
        rupturaHeaders: [
          { title: 'SKU', key: 'sku' },
          { title: 'Produto', key: 'name' },
          { title: 'Categoria', key: 'category', sortable: false },
          { title: 'Fardos em estoque', key: 'total_fardos' },
          { title: 'Mínimo de fardos', key: 'min_fardos' },
        ],
        valorHeaders: [
          { title: 'Categoria', key: 'category' },
          { title: 'Produtos', key: 'product_count', align: 'end' as const },
          { title: 'Valor total', key: 'total_value', align: 'end' as const },
        ],
        giroHeaders: [
          { title: '#', key: 'index', sortable: false },
          { title: 'SKU', key: 'sku' },
          { title: 'Produto', key: 'name' },
          { title: 'Operações', key: 'operation_count', align: 'end' as const },
          { title: 'Itens movimentados', key: 'total_quantity', align: 'end' as const },
        ],
      }
    },

    computed: {
      ...mapState(useReportsStore, ['ruptura', 'valorEstoque', 'giro', 'loading']),

      totalValue (): number {
        return this.valorEstoque.reduce((sum, r) => sum + r.total_value, 0)
      },

      totalProducts (): number {
        return this.valorEstoque.reduce((sum, r) => sum + r.product_count, 0)
      },
    },

    async mounted () {
      await this.loadAll()
    },

    methods: {
      ...mapActions(useReportsStore, ['loadAll']),

      formatCurrency (value: number): string {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
      },
    },
  }
</script>
