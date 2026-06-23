<template>
  <v-data-table-server
    :headers="headers"
    :items="items"
    :items-length="total"
    :items-per-page="perPage"
    :items-per-page-options="[{ value: 15, title: '15' }, { value: 30, title: '30' }, { value: 50, title: '50' }]"
    :loading="loading"
    :page="page"
    @update:options="(options) => $emit('update:options', options)"
  >
    <template #item.cpf="{ item }">
      {{ formatCpf(item.cpf) }}
    </template>

    <template #item.actions="{ item }">
      <template v-if="canManageUser(currentUser, item)">
        <v-btn
          icon="mdi-pencil"
          size="small"
          variant="text"
          @click="$emit('edit', item)"
        />

        <v-btn
          color="error"
          icon="mdi-delete"
          size="small"
          variant="text"
          @click="$emit('delete', item)"
        />
      </template>
    </template>
  </v-data-table-server>
</template>

<script lang="ts">
  import type { AuthUser } from '@/types'
  import { formatCpf } from '@/helpers/cpf'
  import { canManageUser } from '@/helpers/permissions'

  export default {
    name: 'UserTable',

    props: {
      items: { type: Array as () => AuthUser[], required: true },
      loading: { type: Boolean, default: false },
      total: { type: Number, default: 0 },
      page: { type: Number, default: 1 },
      perPage: { type: Number, default: 15 },
      currentUser: { type: Object as () => AuthUser | null, default: null },
    },

    emits: ['edit', 'delete', 'update:options'],

    data () {
      return {
        headers: [
          { title: 'Nome', key: 'name' },
          { title: 'CPF', key: 'cpf' },
          { title: 'E-mail', key: 'email' },
          { title: 'Perfil', key: 'role' },
          { title: 'Ações', key: 'actions', sortable: false },
        ],
      }
    },

    methods: {
      formatCpf,
      canManageUser,
    },
  }
</script>
