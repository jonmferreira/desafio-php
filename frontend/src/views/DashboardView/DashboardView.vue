<template>
  <div>
    <h1 class="text-h5 mb-4">
      Dashboard
    </h1>

    <v-row class="mb-4" dense>
      <v-col cols="12" md="4" sm="6">
        <v-card
          :color="rupturaCount > 0 ? 'error' : 'success'"
          :to="{ name: 'reports', query: { tab: 'ruptura' } }"
          variant="tonal"
        >
          <v-card-text class="d-flex align-center justify-space-between pa-5">
            <div>
              <p class="text-h4 font-weight-bold mb-0">
                {{ rupturaCount }}
              </p>

              <p class="text-body-2 text-medium-emphasis mt-1 mb-0">
                {{ rupturaCount === 1 ? 'produto com estoque crítico' : 'produtos com estoque crítico' }}
              </p>
            </div>

            <v-icon :color="rupturaCount > 0 ? 'error' : 'success'" size="40">
              {{ rupturaCount > 0 ? 'mdi-alert-circle' : 'mdi-check-circle' }}
            </v-icon>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4" sm="6">
        <v-card variant="tonal">
          <v-card-text class="d-flex align-center justify-space-between pa-5">
            <div>
              <p class="text-h4 font-weight-bold mb-0">
                {{ formatCurrency(totalValue) }}
              </p>

              <p class="text-body-2 text-medium-emphasis mt-1 mb-0">
                valor total do estoque
              </p>
            </div>

            <v-icon color="primary" size="40">
              mdi-currency-usd
            </v-icon>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4" sm="12">
        <v-card variant="tonal">
          <v-card-text class="d-flex align-center justify-space-between pa-5">
            <div>
              <p class="text-h4 font-weight-bold mb-0">
                {{ movementCount }}
              </p>

              <p class="text-body-2 text-medium-emphasis mt-1 mb-0">
                itens movimentados (30 dias)
              </p>
            </div>

            <v-icon color="secondary" size="40">
              mdi-swap-vertical
            </v-icon>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-card variant="outlined">
      <v-card-title class="text-body-1 font-weight-medium pt-4 px-4 pb-1">
        Giro de estoque — últimos 30 dias
      </v-card-title>

      <v-card-text class="pb-2">
        <v-sparkline
          v-if="sparklineValues.length > 0"
          color="primary"
          :gradient="['#0D47A1', '#1976D2']"
          line-width="1.5"
          :model-value="sparklineValues"
          padding="12"
          smooth
          stroke-linecap="round"
        />

        <div v-else-if="loading" class="text-center py-4">
          <v-progress-circular indeterminate size="24" />
        </div>

        <p v-else class="text-body-2 text-medium-emphasis text-center py-2">
          Nenhuma movimentação nos últimos 30 dias.
        </p>
      </v-card-text>
    </v-card>
  </div>
</template>

<script lang="ts">
  import { mapActions, mapState } from 'pinia'
  import { useRupturaStore } from '@/stores/ruptura'
  import { useDashboardStore } from './store'

  export default {
    name: 'DashboardView',

    computed: {
      ...mapState(useRupturaStore, { rupturaCount: 'count' }),
      ...mapState(useDashboardStore, ['loading', 'totalValue', 'movementCount', 'sparklineValues']),
    },

    async mounted () {
      await this.loadDashboard()
    },

    methods: {
      ...mapActions(useDashboardStore, { loadDashboard: 'load' }),

      formatCurrency (value: number): string {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
      },
    },
  }
</script>
