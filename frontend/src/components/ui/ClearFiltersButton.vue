<script setup lang="ts">
import { RotateCcw } from '@lucide/vue'
import BaseButton from './BaseButton.vue'

defineProps<{
  disabled?: boolean
}>()

const emit = defineEmits<{
  clear: []
}>()
</script>

<template>
  <BaseButton
    variant="outline"
    size="sm"
    class="clear-filters-button"
    :disabled="disabled"
    @click="emit('clear')"
  >
    <RotateCcw :size="13" aria-hidden="true" />
    Clear
  </BaseButton>
</template>

<style scoped>
.clear-filters-button {
  align-self: end;
  border-color: #dcd8ef;
  color: #6a43cc;
  font-size: 11px;
}

.clear-filters-button:hover:not(:disabled) {
  border-color: #966cf0;
  background: #faf8ff;
  filter: none;
}
</style>
