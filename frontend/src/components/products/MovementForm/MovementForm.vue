<template>
  <v-dialog v-model="dialog" max-width="480" persistent>
    <v-card>
      <v-card-title>Registrar movimentação — {{ productName }}</v-card-title>

      <v-card-text>
        <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
          {{ error }}
        </v-alert>

        <v-form @submit.prevent="confirm">
          <v-radio-group v-model="form.type" inline label="Tipo">
            <v-radio label="Entrada" value="in" />
            <v-radio label="Saída" value="out" />
          </v-radio-group>

          <v-text-field
            v-model.number="form.quantity"
            label="Quantidade"
            min="1"
            required
            type="number"
          />

          <v-text-field v-model="form.reason" label="Motivo" maxlength="255" required />

          <div class="d-flex justify-end ga-2 mt-4">
            <v-btn variant="text" @click="cancel">
              Cancelar
            </v-btn>

            <v-btn color="primary" :loading="loading" type="submit">
              Salvar
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
  import type { MovementFormData } from './types'

  export default {
    name: 'MovementForm',

    props: {
      loading: { type: Boolean, default: false },
      error: { type: String as () => string | null, default: null },
    },

    emits: ['submit'],

    data () {
      return {
        dialog: false,
        productName: '',
        form: {
          type: 'in',
          quantity: 1,
          reason: '',
        } as MovementFormData,
      }
    },

    methods: {
      open (productName: string) {
        this.productName = productName
        this.form = { type: 'in', quantity: 1, reason: '' }
        this.dialog = true
      },

      close () {
        this.dialog = false
      },

      cancel () {
        this.dialog = false
      },

      confirm () {
        this.$emit('submit', { ...this.form })
      },
    },
  }
</script>
