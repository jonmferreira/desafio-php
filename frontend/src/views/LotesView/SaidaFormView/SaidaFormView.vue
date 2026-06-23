<template>
  <div>
    <h1 class="text-h5 mb-4">
      Registrar saída — Lote #{{ loteNumero }}
    </h1>

    <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
      {{ error }}
    </v-alert>

    <v-card>
      <v-card-text>
        <v-select
          v-model="form.product_id"
          class="mb-4"
          hide-details="auto"
          item-title="text"
          item-value="value"
          :items="productOptions"
          label="Produto"
          required
        />

        <v-text-field
          v-model.number="form.quantidade_fardos"
          class="mb-4"
          hide-details="auto"
          label="Quantidade de fardos"
          min="1"
          required
          type="number"
        />

        <v-text-field
          v-model="form.motivo"
          class="mb-4"
          hide-details
          label="Motivo (opcional)"
          maxlength="500"
        />

        <div class="d-flex justify-end ga-2 mt-4">
          <v-btn variant="text" @click="$router.push({ name: 'lotes.show', params: { id } })">
            Cancelar
          </v-btn>

          <v-btn color="warning" :loading="saving" @click="submit">
            Confirmar saída
          </v-btn>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script lang="ts">
  import type { LoteDetalhe } from '@/types'
  import { useNotificationsStore } from '@/stores/notifications'
  import { createSaida, fetchLote } from '../integrations/lotes'

  export default {
    name: 'SaidaFormView',

    props: {
      id: { type: [String, Number], required: true },
    },

    data () {
      return {
        detalhe: null as LoteDetalhe | null,
        form: { product_id: null as number | null, quantidade_fardos: 1, motivo: '' },
        saving: false,
        error: null as string | null,
      }
    },

    computed: {
      loteId (): number {
        return Number(this.id)
      },

      loteNumero (): number | string {
        return this.detalhe?.lote.numero ?? '…'
      },

      productOptions () {
        return (this.detalhe?.lote.itens ?? []).map(item => ({
          text: `${item.product?.sku} — ${item.product?.name} (${item.quantidade_fardos} fardos disponíveis)`,
          value: item.product_id,
        }))
      },
    },

    async mounted () {
      try {
        this.detalhe = await fetchLote(this.loteId)
      } catch {
        this.error = 'Não foi possível carregar o lote.'
      }
    },

    methods: {
      async submit () {
        if (!this.form.product_id) {
          this.error = 'Selecione um produto.'
          return
        }

        this.saving = true
        this.error = null

        try {
          await createSaida({
            lote_id: this.loteId,
            product_id: this.form.product_id,
            quantidade_fardos: this.form.quantidade_fardos,
            motivo: this.form.motivo || undefined,
          })
          useNotificationsStore().notify('Saída registrada com sucesso.')
          this.$router.push({ name: 'lotes.show', params: { id: this.id } })
        } catch (error: any) {
          this.error = error.response?.data?.message ?? 'Não foi possível registrar a saída.'
        } finally {
          this.saving = false
        }
      },
    },
  }
</script>
