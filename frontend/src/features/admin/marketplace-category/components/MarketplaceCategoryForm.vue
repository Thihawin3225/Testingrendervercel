<script setup lang="ts">
import { reactive, watch } from 'vue'
import { Save, X } from '@lucide/vue'

import BaseButton from '@/components/ui/BaseButton.vue'

import type {
  MarketplaceCategoryFormData,
} from '../interfaces/marketplaceCategory.interface'

const props = defineProps<{
  initialData: MarketplaceCategoryFormData
  isSubmitting: boolean
}>()

const emit = defineEmits<{
  submit: [data: MarketplaceCategoryFormData]
  cancel: []
}>()

const form = reactive<MarketplaceCategoryFormData>({
  name: '',
  description: '',
})

const errors = reactive({
  name: '',
})

watch(
  () => props.initialData,
  (data) => {
    form.name = data.name ?? ''
    form.description = data.description ?? ''

    errors.name = ''
  },
  {
    immediate: true,
    deep: true,
  },
)

function validateForm(): boolean {
  errors.name = ''

  const name = form.name.trim()

  if (!name) {
    errors.name = 'Category name is required.'
  } else if (name.length > 100) {
    errors.name = 'Category name cannot exceed 100 characters.'
  }

  return !errors.name
}

function handleSubmit() {
  if (!validateForm()) return

  emit('submit', {
    name: form.name.trim(),
    description: form.description.trim(),
  })
}
</script>

<template>
  <form
    class="space-y-5"
    @submit.prevent="handleSubmit"
  >
    <!-- Category Name -->
    <div class="form-group">
      <label
        for="category-name"
        class="form-label"
      >
        Category Name
        <span class="required-mark">*</span>
      </label>

      <input
        id="category-name"
        v-model="form.name"
        type="text"
        maxlength="100"
        placeholder="Enter marketplace category name"
        class="form-input"
        :class="{ 'input-error': errors.name }"
        :disabled="isSubmitting"
      />

      <div class="field-footer">
        <p
          v-if="errors.name"
          class="error-message"
        >
          {{ errors.name }}
        </p>

        <span
          v-else
          class="helper-text"
        >
          Category name is required.
        </span>

        <span class="character-count">
          {{ form.name.length }}/100
        </span>
      </div>
    </div>

    <!-- Description -->
    <div class="form-group">
      <label
        for="category-description"
        class="form-label"
      >
        Description
        <span class="optional-text">(Optional)</span>
      </label>

      <textarea
        id="category-description"
        v-model="form.description"
        rows="4"
        maxlength="1000"
        placeholder="Enter a short description about this category"
        class="form-input form-textarea"
        :disabled="isSubmitting"
      />

      <div class="character-count description-count">
        {{ form.description.length }}/1000
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <BaseButton
        type="button"
        variant="outline"
        :disabled="isSubmitting"
        @click="emit('cancel')"
      >
        <X :size="16" />
        Cancel
      </BaseButton>

      <BaseButton
        type="submit"
        :disabled="isSubmitting"
      >
        <Save :size="16" />
        {{ isSubmitting ? 'Saving...' : 'Save Category' }}
      </BaseButton>
    </div>
  </form>
</template>

<style scoped>
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}

.form-label {
  color: #403653;
  font-size: 13px;
  font-weight: 650;
}

.required-mark {
  color: #e5484d;
  margin-left: 3px;
}

.optional-text {
  color: #9991a8;
  font-size: 11px;
  font-weight: 400;
  margin-left: 4px;
}

.form-input {
  width: 100%;
  min-height: 42px;
  padding: 10px 13px;
  border: 1px solid #e4deed;
  border-radius: 9px;
  outline: none;
  background: #fff;
  color: #302744;
  font-size: 13px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input::placeholder {
  color: #aaa3b8;
}

.form-input:focus {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px #8233f41a;
}

.form-input:disabled {
  cursor: not-allowed;
  background: #f7f5fa;
}

.form-textarea {
  min-height: 100px;
  resize: vertical;
}

.input-error {
  border-color: #e5484d;
}

.input-error:focus {
  border-color: #e5484d;
  box-shadow: 0 0 0 3px #e5484d1a;
}

.field-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  min-height: 16px;
}

.error-message {
  margin: 0;
  color: #e5484d;
  font-size: 11px;
}

.helper-text {
  color: #9991a8;
  font-size: 11px;
}

.character-count {
  color: #9991a8;
  font-size: 11px;
  text-align: right;
  flex-shrink: 0;
}

.description-count {
  margin-top: -2px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 8px;
  border-top: 1px solid #eeeaf3;
}

@media (max-width: 480px) {
  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions :deep(button) {
    width: 100%;
  }
}
</style>
