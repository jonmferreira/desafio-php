<template>
  <div v-if="detalhe">
    <!-- Cabeçalho -->
    <div class="d-flex align-center justify-space-between mb-4">
      <div>
        <h1 class="text-h5">
          Lote #{{ detalhe.lote.numero }}
        </h1>

        <p class="text-body-2 text-medium-emphasis">
          Cliente: {{ detalhe.lote.user?.name }}
        </p>
      </div>

      <div class="d-flex ga-2">
        <v-btn
          color="warning"
          prepend-icon="mdi-package-variant-remove"
          :to="{ name: 'lotes.saida', params: { id: loteId } }"
          variant="outlined"
        >
          Registrar saída
        </v-btn>
      </div>
    </div>

    <!-- Cards de totais -->
    <v-row class="mb-4">
      <v-col cols="6" md="3">
        <v-card variant="outlined">
          <v-card-text class="text-center">
            <div class="text-body-2 text-medium-emphasis">Total itens</div>
            <div class="text-h6">{{ formatCurrency(detalhe.total_itens) }}</div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="6" md="3">
        <v-card variant="outlined">
          <v-card-text class="text-center">
            <div class="text-body-2 text-medium-emphasis">Frete</div>
            <div class="text-h6">{{ formatCurrency(Number(detalhe.lote.frete)) }}</div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="6" md="3">
        <v-card variant="outlined">
          <v-card-text class="text-center">
            <div class="text-body-2 text-medium-emphasis">Avarias</div>
            <div class="text-h6 text-error">{{ formatCurrency(detalhe.total_avarias) }}</div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="6" md="3">
        <v-card color="primary" variant="tonal">
          <v-card-text class="text-center">
            <div class="text-body-2 text-medium-emphasis">Total geral</div>
            <div class="text-h6 font-weight-bold">{{ formatCurrency(detalhe.total) }}</div>
            <div class="text-caption text-medium-emphasis">{{ detalhe.peso_total.toFixed(3) }} kg</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Itens -->
    <v-card class="mb-4">
      <v-card-title>Itens</v-card-title>

      <v-card-text>
        <v-data-table
          density="compact"
          :headers="itemHeaders"
          :items="detalhe.lote.itens ?? []"
          no-data-text="Nenhum item."
        >
          <template #item.product="{ item }">
            {{ item.product?.sku }} — {{ item.product?.name }}
          </template>

          <template #item.subtotal="{ item }">
            {{ formatCurrency(Number(item.valor_unitario) * item.itens_por_fardo * item.quantidade_fardos) }}
          </template>

          <template #item.peso="{ item }">
            {{ ((Number(item.product?.peso) ?? 0) * item.itens_por_fardo * item.quantidade_fardos).toFixed(3) }} kg
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Avarias -->
    <v-card class="mb-4">
      <v-card-title class="d-flex align-center justify-space-between">
        Avarias

        <v-btn
          color="warning"
          density="compact"
          prepend-icon="mdi-plus"
          variant="text"
          @click="showAvariaDialog = true"
        >
          Adicionar
        </v-btn>
      </v-card-title>

      <v-card-text>
        <v-data-table
          density="compact"
          :headers="avariaHeaders"
          :items="detalhe.lote.avarias ?? []"
          no-data-text="Nenhuma avaria registrada."
        >
          <template #item.valor="{ item }">
            {{ formatCurrency(Number(item.valor)) }}
          </template>

          <template #item.actions="{ item }">
            <v-btn
              color="error"
              icon="mdi-delete"
              size="small"
              variant="text"
              @click="removeAvaria(item.id)"
            />
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Saídas -->
    <v-card>
      <v-card-title>Saídas</v-card-title>

      <v-card-text>
        <v-data-table
          density="compact"
          :headers="saidaHeaders"
          :items="detalhe.lote.saidas ?? []"
          no-data-text="Nenhuma saída registrada."
        >
          <template #item.product="{ item }">
            {{ item.product?.sku }} — {{ item.product?.name }}
          </template>

          <template #item.user="{ item }">
            {{ item.user?.name }}
          </template>

          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Dialog avaria -->
    <v-dialog v-model="showAvariaDialog" max-width="400">
      <v-card>
        <v-card-title>Nova avaria</v-card-title>

        <v-card-text>
          <v-text-field v-model="avariaForm.descricao" label="Descrição" maxlength="500" />

          <v-text-field
            v-model.number="avariaForm.valor"
            label="Valor (R$)"
            min="0"
            prefix="R$"
            step="0.01"
            type="number"
          />
        </v-card-text>

        <v-card-actions>
          <v-spacer />

          <v-btn variant="text" @click="showAvariaDialog = false">
            Cancelar
          </v-btn>

          <v-btn color="primary" :loading="savingAvaria" @click="submitAvaria">
            Salvar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>

  <div v-else-if="loading" class="text-center py-8">
    <v-progress-circular indeterminate />
  </div>

  <v-alert v-else type="error" variant="tonal">
    Não foi possível carregar o lote.
  </v-alert>
</template>

<script lang="ts">
  import type { LoteDetalhe } from '@/types'
  import { useNotificationsStore } from '@/stores/notifications'
  import { addAvaria, fetchLote, removeAvaria } from '../integrations/lotes'

  export default {
    name: 'LoteDetailView',

    props: {
      id: { type: [String, Number], required: true },
    },

    data () {
      return {
        detalhe: null as LoteDetalhe | null,
        loading: false,
        showAvariaDialog: false,
        savingAvaria: false,
        avariaForm: { descricao: '', valor: 0 },
        itemHeaders: [
          { title: 'Produto', key: 'product', sortable: false },
          { title: 'Qtd. fardos', key: 'quantidade_fardos', sortable: false },
          { title: 'Itens/fardo', key: 'itens_por_fardo', sortable: false },
          { title: 'Valor unit.', key: 'valor_unitario', sortable: false },
          { title: 'Subtotal', key: 'subtotal', sortable: false },
          { title: 'Peso', key: 'peso', sortable: false },
        ],
        avariaHeaders: [
          { title: 'Descrição', key: 'descricao', sortable: false },
          { title: 'Valor', key: 'valor', sortable: false },
          { title: '', key: 'actions', sortable: false },
        ],
        saidaHeaders: [
          { title: 'Produto', key: 'product', sortable: false },
          { title: 'Qtd. fardos', key: 'quantidade_fardos', sortable: false },
          { title: 'Motivo', key: 'motivo', sortable: false },
          { title: 'Operador', key: 'user', sortable: false },
          { title: 'Data', key: 'created_at', sortable: false },
        ],
      }
    },

    computed: {
      loteId (): number {
        return Number(this.id)
      },
    },

    async mounted () {
      await this.load()
    },

    methods: {
      async load () {
        this.loading = true
        try {
          this.detalhe = await fetchLote(this.loteId)
        } finally {
          this.loading = false
        }
      },

      async submitAvaria () {
        this.savingAvaria = true
        try {
          await addAvaria(this.loteId, this.avariaForm)
          useNotificationsStore().notify('Avaria registrada com sucesso.')
          this.showAvariaDialog = false
          this.avariaForm = { descricao: '', valor: 0 }
          await this.load()
        } catch {
          useNotificationsStore().notify('Não foi possível registrar a avaria.', 'error')
        } finally {
          this.savingAvaria = false
        }
      },

      async removeAvaria (avariaId: number) {
        try {
          await removeAvaria(this.loteId, avariaId)
          useNotificationsStore().notify('Avaria removida.')
          await this.load()
        } catch {
          useNotificationsStore().notify('Não foi possível remover a avaria.', 'error')
        }
      },

      formatCurrency (value: number): string {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
      },

      formatDate (value: string): string {
        return new Date(value).toLocaleString('pt-BR')
      },
    },
  }
</script>
