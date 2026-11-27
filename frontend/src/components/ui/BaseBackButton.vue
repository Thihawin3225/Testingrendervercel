<script setup lang="ts">
import { useRouter } from 'vue-router'
import { ArrowLeft } from '@lucide/vue'

const router = useRouter()

const props = withDefaults(
  defineProps<{
    to?: string
  }>(),
  {
    to: '/admin/academics',
  },
)

function goBack() {
  router.push(props.to)
}
</script>

<template>
  <button
    type="button"
    class="base-back-button"
    @click="goBack"
  >
    <ArrowLeft :size="17" />
    <span>Back</span>
  </button>
</template>

<style scoped>
.base-back-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 9px 14px;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  background: #fff;
  color: #302744;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.base-back-button:hover {
  border-color: #6a43cc;
  background: #f7f2ff;
  color: #6a43cc;
}
</style>
