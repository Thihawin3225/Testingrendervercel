<script setup lang="ts">
import { reactive, ref, watch } from 'vue'

import BaseButton from '@/components/ui/BaseButton.vue'

import type {
  MajorFormData,
} from '../../interfaces/major.interface'

const props = withDefaults(
  defineProps<{
    initialData: MajorFormData
    isSubmitting?: boolean
  }>(),
  {
    isSubmitting: false,
  },
)

const emit = defineEmits<{
  submit: [data: MajorFormData]
  cancel: []
}>()

const form = reactive<MajorFormData>({
  code: '',
  name: '',
})

const errors = ref({
  code: '',
  name: '',
})

watch(
  () => props.initialData,
  (data) => {
    form.code = data.code
    form.name = data.name

    errors.value.code = ''
    errors.value.name = ''
  },
  { immediate: true, deep: true },
)

function validateForm(): boolean {
  errors.value.code = ''
  errors.value.name = ''

  const code = form.code.trim()
  const name = form.name.trim()

  let valid = true

  if (!code) {
    errors.value.code = 'Major code is required.'
    valid = false
  } else if (code.length > 10) {
    errors.value.code =
      'Major code cannot exceed 10 characters.'
    valid = false
  }

  if (!name) {
    errors.value.name = 'Major name is required.'
    valid = false
  } else if (name.length > 100) {
    errors.value.name =
      'Major name cannot exceed 100 characters.'
    valid = false
  }

  return valid
}

function handleSubmit() {
  if (!validateForm()) return

  emit('submit', {
    code: form.code.trim(),
    name: form.name.trim(),
  })
}
</script>

<template>
  <form
    class="major-form"
    @submit.prevent="handleSubmit"
  >
  
    <!-- MAJOR CODE -->
    <div class="form-group">
      <label for="major-code">
        Major Code <span class="required">*</span>
      </label>

      <input
        id="major-code"
        v-model.trim="form.code"
        type="text"
        maxlength="10"
        placeholder="e.g. CSE"
        required
        class="form-input"
        :class="{ 'form-input-error': errors.code }"
      />

      <small
        v-if="errors.code"
        class="field-error"
      >
        {{ errors.code }}
      </small>

      <small v-else class="field-hint">
        Maximum 10 characters.
      </small>
    </div>

    <!-- MAJOR NAME -->
    <div class="form-group">
      <label for="major-name">
        Major Name <span class="required">*</span>
      </label>

      <input
        id="major-name"
        v-model.trim="form.name"
        type="text"
        maxlength="100"
        placeholder="e.g. Computer Science and Engineering"
        required
        class="form-input"
        :class="{ 'form-input-error': errors.name }"
      />

      <small
        v-if="errors.name"
        class="field-error"
      >
        {{ errors.name }}
      </small>

      <small v-else class="field-hint">
        Maximum 100 characters.
      </small>
    </div>

    <!-- ACTIONS -->
    <div class="form-actions">
      <BaseButton
        variant="outline"
        type="button"
        :disabled="isSubmitting"
        @click="emit('cancel')"
      >
        Cancel
      </BaseButton>

      <BaseButton
        variant="primary"
        type="submit"
        :disabled="isSubmitting"
      >
        {{ isSubmitting ? 'Saving...' : 'Save Major' }}
      </BaseButton>
    </div>
  </form>
</template>

<style scoped>
.major-form {
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
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px rgb(130 51 244 / 10%);
}

.form-input::placeholder {
  color: #aaa4b7;
}

.form-input-error {
  border-color: #dc4660;
}

.form-input-error:focus {
  border-color: #dc4660;
  box-shadow: 0 0 0 3px rgb(220 70 96 / 10%);
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
