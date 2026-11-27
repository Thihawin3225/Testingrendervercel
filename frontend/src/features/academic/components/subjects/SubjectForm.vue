
<script setup lang="ts">
import { ref } from 'vue'

import BaseButton from '@/components/ui/BaseButton.vue'

import type {
  SubjectFormData,
} from '../../interfaces/subject.interface'

const formData = defineModel<SubjectFormData>({
  required: true,
})

withDefaults(
  defineProps<{
    loading?: boolean
    submitLabel?: string
  }>(),
  {
    loading: false,
    submitLabel: 'Save Subject',
  },
)

const emit = defineEmits<{
  submit: []
  cancel: []
}>()

const validationErrors = ref({
  subject_code: '',
  subject_name: '',
})

function validateForm(): boolean {
  validationErrors.value.subject_code = ''
  validationErrors.value.subject_name = ''

  const code = formData.value.subject_code.trim()
  const name = formData.value.subject_name.trim()

  let valid = true

  if (!code) {
    validationErrors.value.subject_code =
      'Subject code is required.'
    valid = false
  } else if (code.length > 30) {
    validationErrors.value.subject_code =
      'Subject code cannot exceed 30 characters.'
    valid = false
  }

  if (!name) {
    validationErrors.value.subject_name =
      'Subject name is required.'
    valid = false
  } else if (name.length > 150) {
    validationErrors.value.subject_name =
      'Subject name cannot exceed 150 characters.'
    valid = false
  }

  return valid
}

function handleSubmit() {
  if (!validateForm()) return

  formData.value.subject_code =
    formData.value.subject_code.trim()

  formData.value.subject_name =
    formData.value.subject_name.trim()

  formData.value.description =
    formData.value.description?.trim() ?? ''

  emit('submit')
}
</script>

<template>
  <form
    class="subject-form"
    @submit.prevent="handleSubmit"
  >
    <!-- SUBJECT CODE -->
    <div class="form-group">
      <label for="subject-code">
        Subject Code <span class="required">*</span>
      </label>

      <input
        id="subject-code"
        v-model.trim="formData.subject_code"
        type="text"
        maxlength="30"
        placeholder="e.g. CS101"
        :disabled="loading"
        required
        class="form-input"
        :class="{
          'form-input-error': validationErrors.subject_code,
        }"
      />

      <small
        v-if="validationErrors.subject_code"
        class="field-error"
      >
        {{ validationErrors.subject_code }}
      </small>

      <small v-else class="field-hint">
        Maximum 30 characters.
      </small>
    </div>

    <!-- SUBJECT NAME -->
    <div class="form-group">
      <label for="subject-name">
        Subject Name <span class="required">*</span>
      </label>

      <input
        id="subject-name"
        v-model.trim="formData.subject_name"
        type="text"
        maxlength="150"
        placeholder="e.g. Introduction to Programming"
        :disabled="loading"
        required
        class="form-input"
        :class="{
          'form-input-error': validationErrors.subject_name,
        }"
      />

      <small
        v-if="validationErrors.subject_name"
        class="field-error"
      >
        {{ validationErrors.subject_name }}
      </small>

      <small v-else class="field-hint">
        Maximum 150 characters.
      </small>
    </div>

    <!-- DESCRIPTION -->
    <div class="form-group">
      <label for="subject-description">
        Description
        <span class="optional">(Optional)</span>
      </label>

      <textarea
        id="subject-description"
        v-model="formData.description"
        rows="3"
        placeholder="Enter subject description"
        :disabled="loading"
        class="form-input form-textarea"
      />

      <small class="field-hint">
        Provide additional information about this subject.
      </small>
    </div>

    <!-- ACTIONS -->
    <div class="form-actions">
      <BaseButton
        variant="outline"
        type="button"
        :disabled="loading"
        @click="emit('cancel')"
      >
        Cancel
      </BaseButton>

      <BaseButton
        variant="primary"
        type="submit"
        :disabled="loading"
      >
        {{ loading ? 'Saving...' : submitLabel }}
      </BaseButton>
    </div>
  </form>
</template>

<style scoped>
.subject-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* FORM INTRO */
.form-intro {
  margin-bottom: 2px;
}

.form-intro h3 {
  margin: 0;
  color: #302744;
  font-size: 16px;
  font-weight: 750;
}

.form-intro p {
  margin: 6px 0 0;
  color: #8a849b;
  font-size: 13px;
}

/* FORM GROUP */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  color: #49415d;
  font-size: 13px;
  font-weight: 650;
}

.required {
  color: #dc4660;
}

.optional {
  color: #aaa4b7;
  font-size: 12px;
  font-weight: 400;
}

/* INPUT */
.form-input {
  width: 100%;
  min-height: 43px;
  padding: 10px 12px;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  outline: none;
  background: #fff;
  color: #302744;
  font: inherit;
  font-size: 13px;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px rgb(130 51 244 / 10%);
}

.form-input::placeholder {
  color: #aaa4b7;
}

.form-input:disabled {
  cursor: not-allowed;
  background: #f8f7fa;
  opacity: 0.7;
}

.form-input-error {
  border-color: #dc4660;
}

.form-input-error:focus {
  border-color: #dc4660;
  box-shadow: 0 0 0 3px rgb(220 70 96 / 10%);
}

/* TEXTAREA */
.form-textarea {
  min-height: 85px;
  resize: vertical;
}

/* HINT AND ERROR */
.field-hint {
  color: #8a849b;
  font-size: 12px;
}

.field-error {
  color: #dc4660;
  font-size: 12px;
}

/* ACTIONS */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 15px;
  border-top: 1px solid #eae6f2;
}

/* RESPONSIVE */
@media (max-width: 540px) {
  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions :deep(button) {
    width: 100%;
  }
}
</style>
