<template>
  <v-app>
    <template v-if="isAuthenticated && !$route.meta.public">
      <v-app-bar color="primary" elevation="2">
        <v-app-bar-title>Systock</v-app-bar-title>
        <v-spacer />

        <v-chip
          v-if="rupturaCount > 0"
          class="mr-3"
          color="warning"
          prepend-icon="mdi-alert"
          size="small"
          @click="$router.push({ name: 'reports', query: { tab: 'ruptura' } })"
        >
          {{ rupturaCount }} ruptura{{ rupturaCount !== 1 ? 's' : '' }}
        </v-chip>

        <span class="mr-4 text-body-2">{{ user?.name }}</span>
        <v-btn icon="mdi-logout" title="Sair" @click="handleLogout" />
      </v-app-bar>

      <v-navigation-drawer permanent>
        <v-list nav>
          <v-list-item
            prepend-icon="mdi-view-dashboard"
            title="Dashboard"
            to="/dashboard"
          />

          <v-list-item
            prepend-icon="mdi-package-variant"
            title="Produtos"
            to="/products"
          />

          <v-list-item
            prepend-icon="mdi-chart-bar"
            title="Relatórios"
            to="/reports"
          />

          <v-list-item
            v-if="isAdmin"
            prepend-icon="mdi-account-group"
            title="Usuários"
            to="/users"
          />
        </v-list>
      </v-navigation-drawer>
    </template>

    <v-main>
      <v-container v-if="!$route.meta.public" fluid>
        <router-view />
      </v-container>

      <router-view v-else />
    </v-main>

    <v-snackbar v-model="show" :color="color">
      {{ message }}
    </v-snackbar>
  </v-app>
</template>

<script lang="ts">
  import { mapActions, mapState, mapWritableState } from 'pinia'
  import { useAuthStore } from '@/stores/auth'
  import { useNotificationsStore } from '@/stores/notifications'
  import { useRupturaStore } from '@/stores/ruptura'

  export default {
    name: 'App',

    computed: {
      ...mapState(useAuthStore, ['isAuthenticated', 'isAdmin', 'user']),
      ...mapState(useNotificationsStore, ['message', 'color']),
      ...mapWritableState(useNotificationsStore, ['show']),
      ...mapState(useRupturaStore, { rupturaCount: 'count' }),
    },

    watch: {
      isAuthenticated (val: boolean) {
        if (val) this.fetchRuptura()
      },
    },

    async mounted () {
      if (this.isAuthenticated) {
        await this.fetchRuptura()
      }
    },

    methods: {
      ...mapActions(useAuthStore, ['logout']),
      ...mapActions(useRupturaStore, { fetchRuptura: 'fetch' }),

      async handleLogout () {
        await this.logout()
        this.$router.push('/login')
      },
    },
  }
</script>
