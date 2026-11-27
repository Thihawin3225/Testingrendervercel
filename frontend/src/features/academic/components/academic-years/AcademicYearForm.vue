<script setup lang="ts">
import BaseButton from '../../../../components/ui/BaseButton.vue'
import { computed } from 'vue'

import type {
  AcademicYearFormData,
} from '../../interfaces/academicYear.interface'

const formData = defineModel<AcademicYearFormData>({
  required: true,
})

const props = withDefaults(
  defineProps<{
    loading?: boolean
    submitLabel?: string
    latestStartDate?: string | null
    isEditMode?: boolean
    originalStartDate?: string | null
  }>(),
  {
    loading: false,
    submitLabel: 'Save Academic Year',
    latestStartDate: null,
    isEditMode: false,
    originalStartDate: null,
  },
)

// Academic Year Name ကို စစ်ဆေးခြင်း
const academicYearParts = computed(() => {
  const match = formData.value.name.trim().match(/^(\d{4})-(\d{4})$/)

  if (!match) return null

  const startYear = Number(match[1])
  const endYear = Number(match[2])

  // ဥပမာ 2016-2017 ဖြစ်ရမယ်
  if (endYear !== startYear + 1) return null

  return {
    startMin: `${startYear}-01-01`,
    startMax: `${startYear}-12-31`,
    endMin: `${endYear}-01-01`,
    endMax: `${endYear}-12-31`,
  }
})

// Create validation
const canCreateAcademicYear = computed(() => {
  if (!academicYearParts.value) return false

  // Latest Academic Year မရှိသေးရင် Create လုပ်ခွင့်ပေးမယ်
  if (!props.latestStartDate) return true

  const newStartYear = Number(
    formData.value.name.split('-')[0],
  )

  const latestStartYear = Number(
    props.latestStartDate.substring(0, 4),
  )

  // Latest Year ထက် နောက်ကျမှ Create လုပ်ခွင့်ပေးမယ်
  return newStartYear > latestStartYear
})

// Edit validation
const canEditAcademicYear = computed(() => {
  if (!academicYearParts.value) return false

  // Original date မရှိရင် Edit validation မအောင်မြင်စေဘူး
  if (!props.originalStartDate) return false

  const originalStartYear = Number(
    props.originalStartDate.substring(0, 4),
  )

  const newStartYear = Number(
    formData.value.name.split('-')[0],
  )

  // Original Year ထက် အရင်နှစ်ကို ပြောင်းခွင့်မပေးဘူး
  return newStartYear >= originalStartYear
})

// Create / Edit အလိုက် Submit ခွင့်ပြုခြင်း
const canSubmit = computed(() => {
  return props.isEditMode
    ? canEditAcademicYear.value
    : canCreateAcademicYear.value
})

const emit = defineEmits<{
  submit: []
  cancel: []
}>()
</script>

<template>
  <form class="academic-year-form" @submit.prevent="emit('submit')">

    <!-- Academic Year Name -->
    <div class="form-group">
      <label for="academic-year-name">
        Academic Year Name <span class="required">*</span>
      </label>

      <input
        id="academic-year-name"
        v-model.trim="formData.name"
        type="text"
        placeholder="e.g. 2026-2027"
        maxlength="20"
        required
        class="form-input"
      />

      <span class="input-hint">
        Enter the academic year name.
      </span>

      <!-- Create validation message -->
      <span
        v-if="!props.isEditMode && academicYearParts && !canCreateAcademicYear"
        class="input-hint validation-error"
      >
        You cannot create an academic year before or equal to the latest existing academic year.
      </span>

      <!-- Invalid Academic Year format -->
      <span
        v-if="formData.name.trim() && !academicYearParts"
        class="input-hint validation-error"
      >
        Please enter a valid academic year, for example 2016-2017.
      </span>

      <!-- Edit validation message -->
      <span
        v-if="props.isEditMode && academicYearParts && !canEditAcademicYear"
        class="input-hint validation-error"
      >
        You cannot change the academic year to an earlier year.
      </span>
    </div>

    <!-- Start Date -->
    <div class="form-group">
      <label for="academic-year-start">
        Start Date <span class="required">*</span>
      </label>

      <input
        id="academic-year-start"
        v-model="formData.start_date"
        type="date"
        :min="academicYearParts?.startMin"
        :max="academicYearParts?.startMax"
        :disabled="!canSubmit"
        required
        class="form-input"
      />
    </div>

    <!-- End Date -->
    <div class="form-group">
      <label for="academic-year-end">
        End Date <span class="required">*</span>
      </label>

      <input
        id="academic-year-end"
        v-model="formData.end_date"
        type="date"
        :min="academicYearParts?.endMin"
        :max="academicYearParts?.endMax"
        :disabled="!canSubmit"
        required
        class="form-input"
      />
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
        :disabled="loading || !canSubmit"
      >
        {{ loading ? 'Saving...' : submitLabel }}
      </BaseButton>
    </div>
  </form>
</template>

<style scoped>
.academic-year-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
  width: 100%;
}

/* Header */
.form-intro {
  display: flex;
  align-items: center;
  gap: 11px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eeeaf5;
}

.intro-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border-radius: 10px;
  background: #f2ebff;
  font-size: 18px;
}

.form-intro h3 {
  margin: 0;
  color: #302744;
  font-size: 15px;
  font-weight: 750;
}

.form-intro p {
  margin: 4px 0 0;
  color: #938ba3;
  font-size: 12px;
}

/* Form Groups */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;
}

.form-group label {
  color: #49415d;
  font-size: 13px;
  font-weight: 650;
}

.required {
  color: #e34e68;
}

/* Input Fields */
.form-input {
  width: 100%;
  min-height: 40px;
  padding: 8px 12px;
  border: 1px solid #e6e0ef;
  border-radius: 9px;
  outline: none;
  background: #fff;
  color: #302744;
  font-family: inherit;
  font-size: 13px;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

.form-input:hover {
  border-color: #c9b5ee;
}

.form-input:focus {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px rgb(130 51 244 / 10%);
}

.form-input::placeholder {
  color: #aaa4b7;
}

.input-hint {
  color: #a29aad;
  font-size: 11px;
}

.validation-error {
  color: #e34e68;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
  padding-top: 10px;
  margin-top: 0;
  border-top: 1px solid #eeeaf5;
}

@media (max-width: 540px) {
  .form-actions {
    flex-direction: column-reverse;
    align-items: stretch;
  }
}
</style>