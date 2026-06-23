<template>
  <v-chip :color="isLow ? 'error' : 'success'" size="small" variant="flat">
    {{ quantity }} {{ unit }}
  </v-chip>
</template>

<script lang="ts">
  export default {
    name: 'StockBadge',

    props: {
      quantity: { type: Number, required: true },
      minQuantity: { type: Number, required: true },
      unit: { type: String, default: '' },
    },

    computed: {
      isLow (): boolean {
        return this.quantity < this.minQuantity
      },
    },
  }
</script>
