<script setup lang="ts">
import { reactive, watch } from 'vue'

import BaseButton from '@/components/ui/BaseButton.vue'

import type {
  SemesterFormData,
} from '../../interfaces/semester.interface'

const props = withDefaults(
  defineProps<{
    initialData: SemesterFormData
    isSubmitting?: boolean
  }>(),
  {
    isSubmitting: false,
  },
)

const emit = defineEmits<{
  submit: [data: SemesterFormData]
  cancel: []
}>()

const form = reactive<SemesterFormData>({
  name: '',
  sort_order: 1,
})

const errors = reactive({
  name: '',
})

watch(
  () => props.initialData,
  (newData) => {
    form.name = newData.name ?? ''
    form.sort_order = Number(newData.sort_order ?? 1)

    errors.name = ''
  },
  { immediate: true, deep: true },
)

function validateForm(): boolean {
  errors.name = ''

  const name = form.name.trim().toLowerCase()

  const allowedNames = [
    'first',
    'first semester',
    'second',
    'second semester',
  ]

  if (!name) {
    errors.name = 'Semester name is required.'
  } else if (!allowedNames.includes(name)) {
    errors.name =
      'Only First, First Semester, Second, and Second Semester are allowed.'
  }

  return !errors.name
}

function handleSubmit() {
  if (!validateForm()) return

  const name = form.name.trim().toLowerCase()

  const isFirst = name.startsWith('first')

  emit('submit', {
    name: form.name.trim().replace(/\b\w/g, (char) => char.toUpperCase()),
    sort_order: isFirst ? 1 : 2,
  })
}

</script>

<template>
  <form class="semester-form" @submit.prevent="handleSubmit">
    <!-- Semester Name -->
    <div class="form-group">
      <label for="semester-name">
        Semester Name <span class="required">*</span>
      </label>

      <input
        id="semester-name"
        v-model="form.name"
        type="text"
        class="form-input"
        :class="{ 'input-error': errors.name }"
        placeholder="Enter First or Second"
        maxlength="30"
        required
        :disabled="isSubmitting"
      />

      <p v-if="errors.name" class="field-error">
        {{ errors.name }}
      </p>

      <p v-else class="field-hint">
        'Only First, First Semester, Second, and Second Semester are allowed.'
      </p>
    </div>

    <!-- Actions -->
    <div class="form-actions">
      <BaseButton
        type="button"
        variant="outline"
        :disabled="isSubmitting"
        @click="emit('cancel')"
      >
        Cancel
      </BaseButton>

      <BaseButton
        type="submit"
        variant="primary"
        :disabled="isSubmitting"
      >
        {{ isSubmitting ? 'Saving...' : 'Save Semester' }}
      </BaseButton>
    </div>
  </form>
</template>

<style scoped>
.semester-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  color: #40384f;
  font-size: 13px;
  font-weight: 650;
}

.required {
  color: #e34b68;
}

.form-input {
  width: 100%;
  min-height: 43px;
  padding: 10px 12px;
  color: #29213b;
  background: #ffffff;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  outline: none;
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  border-color: #7138e5;
  box-shadow: 0 0 0 3px rgba(113, 56, 229, 0.1);
}

.form-input::placeholder {
  color: #aaa3b7;
}

.form-input.input-error {
  border-color: #e34b68;
}

.field-hint {
  margin: 0;
  color: #9690a3;
  font-size: 12px;
}

.field-error {
  margin: 0;
  color: #e34b68;
  font-size: 12px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 5px;
}

@media (max-width: 540px) {
  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions :deep(button) {
    width: 100%;
  }
}
</style>
