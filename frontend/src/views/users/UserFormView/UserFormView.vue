<template>
  <div>
    <h1 class="text-h5 mb-4">
      {{ isEdit ? 'Editar usuário' : 'Novo usuário' }}
    </h1>

    <v-card class="mb-4">
      <v-card-text>
        <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
          {{ error }}
        </v-alert>

        <v-form @submit.prevent="submitForm">
          <v-text-field v-model="form.name" label="Nome" maxlength="255" required />

          <v-text-field
            v-model="cpfDisplay"
            label="CPF"
            maxlength="14"
            required
          />

          <v-text-field
            v-model="form.email"
            label="E-mail"
            maxlength="255"
            required
            type="email"
          />

          <v-text-field
            v-model="form.password"
            :hint="isEdit ? 'Deixe em branco para manter a senha atual' : ''"
            label="Senha"
            maxlength="255"
            persistent-hint
            :required="!isEdit"
            type="password"
          />

          <v-text-field
            v-model="form.password_confirmation"
            label="Confirmar senha"
            maxlength="255"
            :required="!isEdit && !!form.password"
            type="password"
          />

          <div class="d-flex justify-end ga-2 mt-4">
            <v-btn variant="text" @click="$router.push({ name: 'users' })">
              Cancelar
            </v-btn>

            <v-btn color="primary" :loading="saving" type="submit">
              Salvar
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>

    <v-card v-if="isEdit" variant="outlined">
      <v-card-title class="text-body-1 font-weight-medium pt-4 px-4 pb-1">
        Histórico de movimentações
      </v-card-title>

      <v-card-text class="pa-0">
        <v-data-table-server
          :headers="movementHeaders"
          :items="movements"
          :items-length="movementsTotal"
          :items-per-page="10"
          :items-per-page-options="[{ value: 10, title: '10' }]"
          :loading="movementsLoading"
          :page="movementsPage"
          @update:options="loadMovements"
        >
          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #item.product="{ item }">
            {{ item.product ? `${item.product.sku} — ${item.product.name}` : '—' }}
          </template>

          <template #item.type="{ item }">
            <v-chip
              :color="item.type === 'in' ? 'success' : 'error'"
              density="comfortable"
              size="small"
            >
              {{ item.type === 'in' ? 'Entrada' : 'Saída' }}
            </v-chip>
          </template>
        </v-data-table-server>
      </v-card-text>
    </v-card>
  </div>
</template>

<script lang="ts">
  import type { StockMovement } from '@/types'
  import { mapActions, mapState } from 'pinia'
  import { formatCpf, unmaskCpf } from '@/helpers/cpf'
  import { useNotificationsStore } from '@/stores/notifications'
  import { fetchUserMovements } from './integrations/users'
  import { useUserFormStore } from './store'

  export default {
    name: 'UserFormView',

    props: {
      id: { type: [String, Number], default: null },
    },

    data () {
      return {
        movements: [] as StockMovement[],
        movementsTotal: 0,
        movementsPage: 1,
        movementsLoading: false,
        movementHeaders: [
          { title: 'Data', key: 'created_at', sortable: false },
          { title: 'Produto', key: 'product', sortable: false },
          { title: 'Tipo', key: 'type', sortable: false },
          { title: 'Quantidade', key: 'quantity', sortable: false },
          { title: 'Motivo', key: 'reason', sortable: false },
        ],
      }
    },

    computed: {
      ...mapState(useUserFormStore, ['form', 'loading', 'saving', 'error', 'isEdit']),

      cpfDisplay: {
        get (): string {
          return formatCpf(this.form.cpf)
        },
        set (value: string) {
          this.form.cpf = unmaskCpf(value)
        },
      },
    },

    async mounted () {
      this.reset()

      if (this.id) {
        await this.load(Number(this.id))
        await this.loadMovements({ page: 1 })
      }
    },

    methods: {
      ...mapActions(useUserFormStore, ['load', 'submit', 'reset']),
      ...mapActions(useNotificationsStore, ['notify']),

      async submitForm () {
        try {
          await this.submit()
          this.notify(this.isEdit ? 'Usuário atualizado com sucesso.' : 'Usuário criado com sucesso.')
          this.$router.push({ name: 'users' })
        } catch {
          // erro tratado na store
        }
      },

      async loadMovements ({ page }: { page: number }) {
        if (!this.id) return
        this.movementsLoading = true
        this.movementsPage = page

        try {
          const result = await fetchUserMovements(Number(this.id), page)
          this.movements = result.data
          this.movementsTotal = result.total
        } catch {
          // ignora silenciosamente — tabela ficará vazia
        } finally {
          this.movementsLoading = false
        }
      },

      formatDate (iso: string): string {
        return new Date(iso).toLocaleString('pt-BR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
        })
      },
    },
  }
</script>
