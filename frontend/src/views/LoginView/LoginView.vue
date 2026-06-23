<template>
  <div class="login-page">
    <v-container class="fill-height" fluid>
      <v-row align="center" justify="center">
        <v-col cols="12" md="4" sm="8">
          <v-card elevation="8" rounded="lg">
            <v-card-title class="text-h5 pt-6 pb-1 px-6">
              Systock
            </v-card-title>

            <v-card-subtitle class="pb-4 px-6">
              Estoque Saudável, Disponível e Rentável
            </v-card-subtitle>

            <v-card-text class="px-6 pb-6">
              <v-form @submit.prevent="submit">
                <v-text-field
                  v-model="form.email"
                  autocomplete="username"
                  class="mb-2"
                  label="E-mail"
                  name="email"
                  required
                  type="email"
                  variant="outlined"
                />

                <v-text-field
                  v-model="form.password"
                  autocomplete="current-password"
                  class="mb-2"
                  label="Senha"
                  name="password"
                  required
                  type="password"
                  variant="outlined"
                />

                <v-alert v-if="error" class="mb-4" type="error" variant="tonal">
                  {{ error }}
                </v-alert>

                <v-btn
                  block
                  color="primary"
                  :loading="loading"
                  size="large"
                  type="submit"
                >
                  Entrar
                </v-btn>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script lang="ts">
  import { mapActions, mapState } from 'pinia'
  import { useLoginStore } from './store'

  export default {
    name: 'LoginView',

    data () {
      return {
        form: {
          email: '',
          password: '',
        },
      }
    },

    computed: {
      ...mapState(useLoginStore, ['loading', 'error']),
    },

    created () {
      document.title = 'Login | Systock'
    },

    methods: {
      ...mapActions(useLoginStore, ['login']),

      async submit () {
        try {
          await this.login(this.form)
          document.title = 'Systock'
          this.$router.push('/')
        } catch {
          // erro tratado na store
        }
      },
    },
  }
</script>

<style scoped>
.login-page {
  min-height: 100dvh;
  background: linear-gradient(135deg, #0D47A1 0%, #1565C0 55%, #0277BD 100%);
}

.login-page::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 15% 20%, rgba(255, 255, 255, 0.07) 0%, transparent 50%),
    radial-gradient(ellipse at 85% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 45%);
  pointer-events: none;
}
</style>
