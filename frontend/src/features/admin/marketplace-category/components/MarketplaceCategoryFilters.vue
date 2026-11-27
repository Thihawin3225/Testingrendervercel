<script setup lang="ts">
import { Search } from '@lucide/vue'

defineProps<{
  modelValue: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  search: []
}>()

function handleSearch() {
  emit('search')
}
</script>

<template>
  <form
    class="search-form"
    @submit.prevent="handleSearch"
  >
    <div class="search-input-wrapper">
      <Search :size="17" />

      <input
        :value="modelValue"
        type="search"
        placeholder="Search category name..."
        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <button
      type="submit"
      class="search-button"
    >
      Search
    </button>
  </form>
</template>

<style scoped>
.search-form {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 9px;
  margin-bottom: 19px;
}

.search-input-wrapper {
  display: flex;
  width: 280px;
  height: 39px;
  align-items: center;
  gap: 9px;
  padding: 0 12px;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  background: #fff;
  color: #9b93ac;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input-wrapper:focus-within {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px #8233f41a;
}

.search-input-wrapper input {
  width: 100%;
  min-width: 0;
  border: 0;
  outline: none;
  background: transparent;
  color: #302744;
  font-size: 12px;
}

.search-input-wrapper input::placeholder {
  color: #aaa3b8;
}

.search-button {
  flex-shrink: 0;
  height: 39px;
  padding: 0 18px;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  background: #fff;
  color: #7138e5;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.search-button:hover {
  border-color: #8233f4;
  background: #f8f5ff;
}

@media (max-width: 640px) {
  .search-form {
    width: 100%;
  }

  .search-input-wrapper {
    width: auto;
    flex: 1;
  }

  .search-button {
    padding: 0 14px;
  }
}
</style>
