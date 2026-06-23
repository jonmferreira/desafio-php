<template>
  <div>
    <h1 class="text-h5 mb-4">
      {{ isEdit ? 'Editar produto' : 'Novo produto' }}
    </h1>

    <v-card>
      <v-card-text>
        <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
          {{ error }}
        </v-alert>

        <v-form @submit.prevent="submitForm">
          <v-select
            v-model="form.category_id"
            item-title="text"
            item-value="value"
            :items="categoryOptions"
            label="Categoria"
            required
          />

          <v-text-field v-model="form.sku" label="SKU" maxlength="50" required />

          <v-text-field v-model="form.name" label="Nome" maxlength="255" required />

          <v-textarea v-model="form.description" label="Descrição" maxlength="1000" />

          <v-text-field v-model="form.unit" label="Unidade" maxlength="10" required />

          <v-text-field
            v-model.number="form.peso"
            label="Peso unitário (kg)"
            min="0"
            required
            step="0.001"
            type="number"
          />

          <v-text-field
            v-model.number="form.min_fardos"
            label="Fardos mínimos em estoque"
            min="0"
            required
            type="number"
          />

          <v-text-field
            v-model.number="form.price"
            label="Preço"
            min="0"
            prefix="R$"
            required
            step="0.01"
            type="number"
          />

          <div class="d-flex justify-end ga-2 mt-4">
            <v-btn variant="text" @click="$router.push({ name: 'products' })">
              Cancelar
            </v-btn>

            <v-btn color="primary" :loading="saving" type="submit">
              Salvar
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </div>
</template>

<script lang="ts">
  import { mapActions, mapState } from 'pinia'
  import { useNotificationsStore } from '@/stores/notifications'
  import { useProductFormStore } from './store'

  export default {
    name: 'ProductFormView',

    props: {
      id: { type: [String, Number], default: null },
    },

    computed: {
      ...mapState(useProductFormStore, ['form', 'loading', 'saving', 'error', 'isEdit', 'categoryOptions']),
    },

    async mounted () {
      this.reset()
      await this.fetchCategoryOptions()

      if (this.id) {
        await this.load(Number(this.id))
      }
    },

    methods: {
      ...mapActions(useProductFormStore, ['load', 'submit', 'reset', 'fetchCategoryOptions']),
      ...mapActions(useNotificationsStore, ['notify']),

      async submitForm () {
        try {
          await this.submit()
          this.notify(this.isEdit ? 'Produto atualizado com sucesso.' : 'Produto criado com sucesso.')
          this.$router.push({ name: 'products' })
        } catch {
          // erro tratado na store
        }
      },
    },
  }
</script>
