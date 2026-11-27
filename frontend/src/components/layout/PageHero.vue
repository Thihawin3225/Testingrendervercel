<script setup lang="ts">
import type { Component } from 'vue'

defineProps<{
  eyebrow: string
  title: string
  titleId: string
  description: string
  icon: Component
}>()
</script>

<template>
  <header class="page-hero">
    <div class="page-hero__copy">
      <p class="page-hero__eyebrow">{{ eyebrow }}</p>
      <h1 :id="titleId">{{ title }}</h1>
      <p class="page-hero__description">{{ description }}</p>
    </div>
    <span class="page-hero__icon" aria-hidden="true">
      <component :is="icon" :size="48" stroke-width="1.7" />
    </span>
  </header>
</template>

<style scoped>
.page-hero {
  display: flex;
  min-height: 150px;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  overflow: hidden;
  padding: 25px 30px;
  border: 1px solid #e2d9fc;
  border-radius: 19px;
  background: linear-gradient(106deg, #f2ecff, #ece1ff 57%, #d1cdfc);
}

.page-hero__copy {
  min-width: 0;
}

.page-hero__eyebrow {
  margin: 0 0 7px;
  color: #6a43cc;
  font-size: 11px;
  font-weight: 850;
  letter-spacing: 0.13em;
  text-transform: uppercase;
}

.page-hero h1 {
  margin: 0;
  color: #172244;
  font-size: 30px;
  line-height: 1.2;
}

.page-hero__description {
  margin: 8px 0 0;
  color: #625b7c;
  font-size: 14px;
  line-height: 1.5;
}

.page-hero__icon {
  display: grid;
  width: 78px;
  height: 78px;
  flex: none;
  place-items: center;
  border-radius: 22px;
  background: #ffffff9c;
  color: #7246db;
}

@media (max-width: 560px) {
  .page-hero {
    min-height: 125px;
    padding: 20px;
  }

  .page-hero h1 {
    font-size: 24px;
  }

  .page-hero__icon {
    width: 60px;
    height: 60px;
    border-radius: 18px;
  }

  .page-hero__icon :deep(svg) {
    width: 36px;
    height: 36px;
  }
}
</style>
