<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4">
      <h1 class="text-h5">
        Usuários
      </h1>

      <v-btn color="primary" prepend-icon="mdi-plus" :to="{ name: 'users.new' }">
        Novo usuário
      </v-btn>
    </div>

    <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
      {{ error }}
    </v-alert>

    <user-table
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
  import type { AuthUser } from '@/types'
  import { mapActions, mapState } from 'pinia'
  import ConfirmDialog from '@/components/shared/ConfirmDialog/ConfirmDialog.vue'
  import { useAuthStore } from '@/stores/auth'
  import { useNotificationsStore } from '@/stores/notifications'
  import UserTable from './components/UserTable/UserTable.vue'
  import { useUsersViewStore } from './store'

  export default {
    name: 'UsersView',

    components: { UserTable, ConfirmDialog },

    computed: {
      ...mapState(useUsersViewStore, ['items', 'loading', 'error', 'pagination']),
      ...mapState(useAuthStore, ['user']),
    },

    methods: {
      ...mapActions(useUsersViewStore, ['fetchAll', 'remove']),
      ...mapActions(useNotificationsStore, ['notify']),

      edit (user: AuthUser) {
        this.$router.push({ name: 'users.edit', params: { id: user.id } })
      },

      async confirmDelete (user: AuthUser) {
        const confirmed = await (this.$refs.confirmDialog as InstanceType<typeof ConfirmDialog>).open({
          title: 'Excluir usuário',
          message: `Tem certeza que deseja excluir o usuário "${user.name}"?`,
          confirmText: 'Excluir',
        })

        if (!confirmed) return

        try {
          await this.remove(user.id)
          this.notify('Usuário excluído com sucesso.')
        } catch {
          this.notify('Não foi possível excluir o usuário.', 'error')
        }
      },
    },
  }
</script>
