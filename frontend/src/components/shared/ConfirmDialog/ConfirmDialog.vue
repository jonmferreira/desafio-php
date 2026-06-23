<template>
  <v-dialog v-model="dialog" max-width="420" persistent>
    <v-card>
      <v-card-title>{{ options.title ?? 'Confirmar ação' }}</v-card-title>
      <v-card-text>{{ options.message }}</v-card-text>

      <v-card-actions>
        <v-spacer />

        <v-btn variant="text" @click="cancel">
          {{ options.cancelText ?? 'Cancelar' }}
        </v-btn>

        <v-btn :color="options.color ?? 'error'" variant="flat" @click="confirm">
          {{ options.confirmText ?? 'Confirmar' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts" setup>
  import type { ConfirmDialogOptions } from './types'
  import { ref } from 'vue'

  const dialog = ref(false)
  const options = ref<ConfirmDialogOptions>({ message: '' })

  let resolvePromise: (value: boolean) => void

  function open (opts: ConfirmDialogOptions) {
    options.value = opts
    dialog.value = true
    return new Promise<boolean>(resolve => {
      resolvePromise = resolve
    })
  }

  function confirm () {
    dialog.value = false
    resolvePromise(true)
  }

  function cancel () {
    dialog.value = false
    resolvePromise(false)
  }

  defineExpose({ open })
</script>
