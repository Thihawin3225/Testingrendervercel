<script setup lang="ts">
type CardElement = 'div' | 'article' | 'section'

withDefaults(
  defineProps<{
    as?: CardElement
    padded?: boolean
  }>(),
  {
    as: 'div',
    padded: false,
  },
)
</script>

<template>
  <component :is="as" class="base-card" :class="{ 'base-card--padded': padded }">
    <slot />
  </component>
</template>

<style scoped>
.base-card {
  overflow: hidden;
  border: 1px solid #e6e4f5;
  border-radius: 16px;
  background: #fff;
  box-shadow: var(--shadow);
}

.base-card--padded {
  padding: 20px;
}
</style>
