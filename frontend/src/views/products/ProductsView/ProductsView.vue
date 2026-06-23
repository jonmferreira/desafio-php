<template>
  <div>
    <!-- Cabeçalho -->
    <div class="d-flex align-center justify-space-between mb-4">
      <h1 class="text-h5">
        Produtos
      </h1>

      <div class="d-flex ga-2">
        <v-btn
          v-if="isAdmin"
          :loading="exporting"
          prepend-icon="mdi-download"
          variant="outlined"
          @click="exportCsv"
        >
          Exportar CSV
        </v-btn>

        <v-btn v-if="isAdmin" color="primary" prepend-icon="mdi-plus" :to="{ name: 'products.new' }">
          Novo produto
        </v-btn>
      </div>
    </div>

    <!-- Gráfico de giro -->
    <v-card class="mb-4" variant="outlined">
      <v-card-title class="text-body-1 font-weight-medium pt-4 px-4 pb-1">
        Giro de estoque — últimos 30 dias
      </v-card-title>

      <v-card-text class="pb-2">
        <v-sparkline
          v-if="stockFlow.length > 0"
          color="primary"
          :gradient="['#0D47A1', '#1976D2']"
          line-width="1.5"
          :model-value="stockFlowValues"
          padding="12"
          smooth
          stroke-linecap="round"
        />

        <div v-else-if="stockFlowLoading" class="text-center py-4">
          <v-progress-circular indeterminate size="24" />
        </div>

        <p v-else class="text-body-2 text-medium-emphasis text-center py-2">
          Nenhuma movimentação nos últimos 30 dias.
        </p>
      </v-card-text>
    </v-card>

    <!-- Filtros -->
    <v-card class="mb-4" variant="outlined">
      <v-card-text>
        <v-row dense>
          <v-col cols="12" md="4" sm="6">
            <v-select
              v-model="filters.categoryId"
              clearable
              density="compact"
              hide-details
              item-title="name"
              item-value="id"
              :items="categoryItems"
              label="Categoria"
              variant="outlined"
            />
          </v-col>

          <v-col cols="12" md="2" sm="6">
            <v-select
              v-model="filters.status"
              clearable
              density="compact"
              hide-details
              item-title="label"
              item-value="value"
              :items="statusItems"
              label="Status"
              variant="outlined"
            />
          </v-col>

          <v-col cols="6" md="2" sm="3">
            <v-text-field
              v-model.number="filters.priceMin"
              clearable
              density="compact"
              hide-details
              label="Preço mín."
              min="0"
              step="0.01"
              type="number"
              variant="outlined"
            />
          </v-col>

          <v-col cols="6" md="2" sm="3">
            <v-text-field
              v-model.number="filters.priceMax"
              clearable
              density="compact"
              hide-details
              label="Preço máx."
              min="0"
              step="0.01"
              type="number"
              variant="outlined"
            />
          </v-col>

          <v-col class="d-flex align-center ga-2" cols="12" md="2" sm="12">
            <v-btn block color="primary" density="comfortable" @click="applyFilters">
              Filtrar
            </v-btn>

            <v-btn block density="comfortable" variant="text" @click="clearFilters">
              Limpar
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
      {{ error }}
    </v-alert>

    <product-table
      :current-user="user"
      :items="items"
      :loading="loading"
      :page="pagination.page"
      :per-page="pagination.perPage"
      :total="pagination.total"
      @delete="confirmDelete"
      @edit="edit"
      @update:options="fetchAll"
    />

    <confirm-dialog ref="confirmDialog" />
  </div>
</template>

<script lang="ts">
  import type { Product } from '@/types'
  import { mapActions, mapState, mapWritableState } from 'pinia'
  import ConfirmDialog from '@/components/shared/ConfirmDialog/ConfirmDialog.vue'
  import { useAuthStore } from '@/stores/auth'
  import { useNotificationsStore } from '@/stores/notifications'
  import ProductTable from './components/ProductTable/ProductTable.vue'
  import { useProductsViewStore } from './store'

  export default {
    name: 'ProductsView',

    components: { ProductTable, ConfirmDialog },

    data () {
      return {
        statusItems: [
          { label: 'Estoque crítico', value: 'critico' },
          { label: 'OK', value: 'ok' },
        ],
      }
    },

    computed: {
      ...mapState(useProductsViewStore, [
        'items', 'loading', 'error', 'pagination',
        'categories', 'stockFlow', 'stockFlowLoading', 'exporting',
      ]),
      ...mapWritableState(useProductsViewStore, ['filters']),
      ...mapState(useAuthStore, ['user', 'isAdmin']),

      categoryItems () {
        return this.categories
      },

      stockFlowValues (): number[] {
        return this.stockFlow.map(e => e.total)
      },
    },

    async mounted () {
      await Promise.all([
        this.loadCategories(),
        this.loadStockFlow(),
      ])
    },

    methods: {
      ...mapActions(useProductsViewStore, [
        'fetchAll', 'remove',
        'loadCategories', 'loadStockFlow', 'exportCsv', 'applyFilters',
      ]),
      ...mapActions(useNotificationsStore, ['notify']),

      clearFilters () {
        this.filters = { categoryId: null, status: null, priceMin: null, priceMax: null }
        this.applyFilters()
      },

      edit (product: Product) {
        this.$router.push({ name: 'products.edit', params: { id: product.id } })
      },

      async confirmDelete (product: Product) {
        const confirmed = await (this.$refs.confirmDialog as InstanceType<typeof ConfirmDialog>).open({
          title: 'Excluir produto',
          message: `Tem certeza que deseja excluir o produto "${product.name}"?`,
          confirmText: 'Excluir',
        })

        if (!confirmed) return

        try {
          await this.remove(product.id)
          this.notify('Produto excluído com sucesso.')
        } catch {
          this.notify('Não foi possível excluir o produto.', 'error')
        }
      },
    },
  }
</script>
