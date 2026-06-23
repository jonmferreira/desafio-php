<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4">
      <h1 class="text-h5">
        Lotes
      </h1>

      <v-btn color="primary" prepend-icon="mdi-plus" :to="{ name: 'lotes.new' }">
        Novo lote
      </v-btn>
    </div>

    <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
      {{ error }}
    </v-alert>

    <v-data-table-server
      :headers="headers"
      :items="items"
      :items-length="pagination.total"
      :items-per-page="pagination.perPage"
      :items-per-page-options="[{ value: 15, title: '15' }, { value: 30, title: '30' }]"
      :loading="loading"
      :page="pagination.page"
      @update:options="fetchAll"
    >
      <template #item.numero="{ item }">
        <v-chip color="primary" size="small" variant="outlined">
          #{{ item.numero }}
        </v-chip>
      </template>

      <template #item.user="{ item }">
        {{ item.user?.name }}
      </template>

      <template #item.frete="{ item }">
        {{ formatCurrency(Number(item.frete)) }}
      </template>

      <template #item.created_at="{ item }">
        {{ formatDate(item.created_at) }}
      </template>

      <template #item.actions="{ item }">
        <v-btn
          icon="mdi-eye"
          size="small"
          title="Ver detalhes"
          variant="text"
          @click="$router.push({ name: 'lotes.show', params: { id: item.id } })"
        />

        <v-btn
          v-if="isAdmin"
          color="error"
          icon="mdi-delete"
          size="small"
          variant="text"
          @click="confirmDelete(item)"
        />
      </template>
    </v-data-table-server>

    <confirm-dialog ref="confirmDialog" />
  </div>
</template>

<script lang="ts">
  import type { DataTableOptions } from '@/helpers/pagination'
  import type { Lote } from '@/types'
  import { mapState } from 'pinia'
  import ConfirmDialog from '@/components/shared/ConfirmDialog/ConfirmDialog.vue'
  import { toPaginationParams } from '@/helpers/pagination'
  import { useAuthStore } from '@/stores/auth'
  import { useNotificationsStore } from '@/stores/notifications'
  import { deleteLote, fetchLotes } from './integrations/lotes'

  export default {
    name: 'LotesView',

    components: { ConfirmDialog },

    data () {
      return {
        items: [] as Lote[],
        loading: false,
        error: null as string | null,
        pagination: { page: 1, total: 0, perPage: 15 },
        headers: [
          { title: 'Nº', key: 'numero', sortable: false },
          { title: 'Cliente', key: 'user', sortable: false },
          { title: 'Frete', key: 'frete', sortable: false },
          { title: 'Criado em', key: 'created_at', sortable: false },
          { title: 'Ações', key: 'actions', sortable: false },
        ],
      }
    },

    computed: {
      ...mapState(useAuthStore, ['user', 'isAdmin']),
    },

    async mounted () {
      await this.fetchAll()
    },

    methods: {
      async fetchAll (options?: DataTableOptions) {
        this.loading = true
        this.error = null

        try {
          const params = options ? toPaginationParams(options) : {}
          const userId = this.isAdmin ? undefined : this.user?.id

          const response = await fetchLotes({ ...params, user_id: userId })
          this.items = response.data
          this.pagination = {
            page: response.current_page,
            total: response.total,
            perPage: response.per_page,
          }
        } catch {
          this.error = 'Não foi possível carregar os lotes.'
        } finally {
          this.loading = false
        }
      },

      async confirmDelete (lote: Lote) {
        const confirmed = await (this.$refs.confirmDialog as InstanceType<typeof ConfirmDialog>).open({
          title: 'Excluir lote',
          message: `Tem certeza que deseja excluir o lote #${lote.numero}?`,
          confirmText: 'Excluir',
        })

        if (!confirmed) return

        try {
          await deleteLote(lote.id)
          useNotificationsStore().notify('Lote excluído com sucesso.')
          await this.fetchAll()
        } catch {
          useNotificationsStore().notify('Não foi possível excluir o lote.', 'error')
        }
      },

      formatCurrency (value: number): string {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
      },

      formatDate (value: string): string {
        return new Date(value).toLocaleDateString('pt-BR')
      },
    },
  }
</script>
