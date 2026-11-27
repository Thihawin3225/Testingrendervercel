<script setup lang="ts">
import { computed } from 'vue'

import BaseButton from '../../../../components/ui/BaseButton.vue'

import type {
  YearLevelFormData,
} from '../../interfaces/yearLevel.interface'

const formData = defineModel<YearLevelFormData>({
  required: true,
})

const props = withDefaults(
  defineProps<{
    loading?: boolean
    submitLabel?: string
    isEditMode?: boolean
    originalName?: string
  }>(),
  {
    loading: false,
    submitLabel: 'Save Year Level',
    isEditMode: false,
    originalName: '',
  },
)

const emit = defineEmits<{
  submit: []
  cancel: []
}>()

const standardNames = [
  'first year',
  'second year',
  'third year',
  'fourth year',
  'fifth year',
  'sixth year',
]

const isFinalName = computed(() => {
  return /\bfinal\b/i.test(formData.value.name.trim())
})

const wasAlreadyFinal = computed(() => {
  return /\bfinal\b/i.test(props.originalName.trim())
})

function handleSubmit() {
  const name = formData.value.name.trim()
  const normalizedName = name.toLowerCase()

  const isStandardName = standardNames.includes(normalizedName)

  // Standard year names can be submitted directly.
  if (isStandardName) {
    emit('submit')
    return
  }

  // Reject custom names that do not contain "Final".
  if (!/\bfinal\b/i.test(name)) {
    alert(
      'Only First Year to Sixth Year or a custom name containing "Final" is allowed.',
    )
    return
  }

  // Existing Final Year does not need confirmation again.
  if (props.isEditMode && wasAlreadyFinal.value) {
    emit('submit')
    return
  }

  // Ask confirmation when creating or changing to a Final Year.
  const confirmed = window.confirm(
    'Confirm that this is the final year of your university?',
  )

  if (!confirmed) {
    return
  }

  emit('submit')
}
</script>

<template>
  <form class="year-level-form" @submit.prevent="handleSubmit">

    <!-- Year Level Name -->
    <div class="form-group">
      <label for="year-level-name">
        Year Level Name <span class="required">*</span>
      </label>

      <input
        id="year-level-name"
        v-model.trim="formData.name"
        type="text"
        placeholder="e.g. First Year or Final Stage"
        maxlength="30"
        required
        class="form-input"
        :disabled="loading"
      />

      <small class="field-hint">
        Use First Year to Fifth Year, or a custom name containing "Final".
      </small>
    </div>

    <!-- Actions -->
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
.year-level-form {
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
  color: #49415d;
  font-size: 13px;
  font-weight: 650;
}

.required {
  color: #dc4660;
}

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

.readonly-input {
  background: #f5f3f9;
  color: #8a849b;
  cursor: not-allowed;
}

.field-hint {
  color: #8a849b;
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
    flex-wrap: wrap;
  }
}
</style>
