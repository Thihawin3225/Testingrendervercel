<script setup lang="ts">
import { reactive, watch } from 'vue'
import { Save, X } from '@lucide/vue'

import BaseButton from '@/components/ui/BaseButton.vue'

import type {
  CampusLocationFormData,
} from '../interfaces/campusLocation.interface'

const props = defineProps<{
  initialData: CampusLocationFormData
  isSubmitting: boolean
}>()

const emit = defineEmits<{
  submit: [data: CampusLocationFormData]
  cancel: []
}>()

const form = reactive<CampusLocationFormData>({
  name: '',
  building: '',
  description: '',
  is_active: true,
})

const errors = reactive({
  name: '',
  building: '',
})

watch(
  () => props.initialData,
  (data) => {
    form.name = data.name ?? ''
    form.building = data.building ?? ''
    form.description = data.description ?? ''
    form.is_active = data.is_active ?? true

    errors.name = ''
    errors.building = ''
  },
  { immediate: true, deep: true },
)

function validateForm(): boolean {
  errors.name = ''
  errors.building = ''

  const name = form.name.trim()
  const building = form.building.trim()

  if (!name) {
    errors.name = 'Location name is required.'
  } else if (name.length > 150) {
    errors.name = 'Location name cannot exceed 150 characters.'
  }

  if (!building) {
    errors.building = 'Building is required.'
  } else if (building.length > 150) {
    errors.building = 'Building cannot exceed 150 characters.'
  }

  return !errors.name && !errors.building
}

function handleSubmit() {
  if (!validateForm()) return

  emit('submit', {
    name: form.name.trim(),
    building: form.building.trim(),
    description: form.description.trim(),
    is_active: form.is_active,
  })
}
</script>

<template>
  <form class="space-y-5" @submit.prevent="handleSubmit">
    <!-- Location Name -->
    <div class="form-group">
      <label for="location-name" class="form-label">
        Location Name
        <span class="required-mark">*</span>
      </label>

      <input
        id="location-name"
        v-model="form.name"
        type="text"
        maxlength="150"
        placeholder="Enter campus location name"
        class="form-input"
        :class="{ 'input-error': errors.name }"
        :disabled="isSubmitting"
      />

      <p v-if="errors.name" class="error-message">
        {{ errors.name }}
      </p>
    </div>

    <!-- Building -->
    <div class="form-group">
      <label for="location-building" class="form-label">
        Building
        <span class="required-mark">*</span>
      </label>

      <input
        id="location-building"
        v-model="form.building"
        type="text"
        maxlength="150"
        placeholder="Enter building name"
        class="form-input"
        :class="{ 'input-error': errors.building }"
        :disabled="isSubmitting"
      />

      <p v-if="errors.building" class="error-message">
        {{ errors.building }}
      </p>
    </div>

    <!-- Description -->
    <div class="form-group">
      <label for="location-description" class="form-label">
        Description
        <span class="optional-text">(Optional)</span>
      </label>

      <textarea
        id="location-description"
        v-model="form.description"
        rows="4"
        placeholder="Enter a short description about this location"
        class="form-input form-textarea"
        :disabled="isSubmitting"
      />
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
        {{ isSubmitting ? 'Saving...' : 'Save Location' }}
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

.error-message {
  margin: 0;
  color: #e5484d;
  font-size: 11px;
}

.status-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px;
  border: 1px solid #e9e3f2;
  border-radius: 10px;
  background: #faf9fc;
}

.status-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.status-title {
  color: #403653;
  font-size: 13px;
  font-weight: 650;
}

.status-description {
  color: #8a849b;
  font-size: 11px;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 42px;
  height: 23px;
  flex-shrink: 0;
}

.toggle-switch input {
  width: 0;
  height: 0;
  opacity: 0;
}

.toggle-slider {
  position: absolute;
  inset: 0;
  cursor: pointer;
  border-radius: 20px;
  background: #d6d1df;
  transition: 0.2s;
}

.toggle-slider::before {
  position: absolute;
  bottom: 3px;
  left: 3px;
  width: 17px;
  height: 17px;
  border-radius: 50%;
  background: white;
  content: '';
  transition: 0.2s;
}

.toggle-switch input:checked + .toggle-slider {
  background: #7138e5;
}

.toggle-switch input:checked + .toggle-slider::before {
  transform: translateX(19px);
}

.toggle-switch input:disabled + .toggle-slider {
  cursor: not-allowed;
  opacity: 0.6;
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
