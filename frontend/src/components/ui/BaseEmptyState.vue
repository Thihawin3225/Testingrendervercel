<script setup lang="ts">
defineProps<{
  title: string
  description: string
}>()
</script>

<template>
  <div class="base-empty-state" role="status">
    <div v-if="$slots.icon" class="base-empty-state__icon" aria-hidden="true">
      <slot name="icon" />
    </div>
    <h3>{{ title }}</h3>
    <p>{{ description }}</p>
    <div v-if="$slots.action" class="base-empty-state__action">
      <slot name="action" />
    </div>
  </div>
</template>

<style scoped>
.base-empty-state {
  padding: 44px 19px;
  border: 1px dashed #dbd4ee;
  border-radius: 14px;
  background: #fff;
  color: #7d7694;
  text-align: center;
}

.base-empty-state__icon {
  display: grid;
  place-items: center;
  margin-bottom: 8px;
  color: #8f50f8;
}

.base-empty-state h3 {
  margin: 0;
  font-size: 15px;
}

.base-empty-state p {
  margin: 7px 0 0;
}

.base-empty-state__action {
  margin-top: 15px;
}
</style>
