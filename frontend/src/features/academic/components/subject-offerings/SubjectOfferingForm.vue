<script setup lang="ts">
import { computed, watch } from 'vue'

import BaseButton from '../../../../components/ui/BaseButton.vue'

import type {
  SubjectOfferingFormData,
} from '../../interfaces/subjectOffering.interface'

import { useAcademicYears } from '../../queries/academicYear.queries'
import { useYearLevels } from '../../queries/yearLevel.queries'
import { useMajors } from '../../queries/major.queries'
import { useSemesters } from '../../queries/semester.queries'
import { useSubjects } from '../../queries/subject.queries'

// ==========================================
// TYPES
// ==========================================

type SubjectDraftDisplay = {
  subject_code: string
  subject_name: string
}

// ==========================================
// FORM MODEL
// ==========================================

const formData = defineModel<SubjectOfferingFormData>({
  required: true,
})

// ==========================================
// PROPS & EMITS
// ==========================================

const props = withDefaults(
  defineProps<{
    loading?: boolean
    submitLabel?: string
    lockedSubject?: SubjectDraftDisplay | null
  }>(),
  {
    loading: false,
    submitLabel: 'Save Subject Offering',
    lockedSubject: null,
  },
)

const emit = defineEmits<{
  submit: []
  cancel: []
}>()

// ==========================================
// TANSTACK QUERIES
// ==========================================

const {
  data: academicYearsData,
  isPending: academicYearsLoading,
} = useAcademicYears({
  per_page: 100,
})

const {
  data: yearLevelsData,
  isPending: yearLevelsLoading,
} = useYearLevels({
  per_page: 100,
})

const {
  data: majorsData,
  isPending: majorsLoading,
} = useMajors({
  per_page: 100,
})

const {
  data: semestersData,
  isPending: semestersLoading,
} = useSemesters({
  per_page: 100,
})

const {
  data: subjectsData,
  isPending: subjectsLoading,
} = useSubjects({
  per_page: 100,
})

// ==========================================
// DROPDOWN OPTIONS
// ==========================================

// Academic Years
const academicYears = computed(() => {
  return academicYearsData.value?.data ?? []
})

// Check different possible boolean representations.
function isActiveYear(value: unknown): boolean {
  return (
    value === true ||
    value === 1 ||
    value === '1' ||
    value === 'true'
  )
}

// Find Active Academic Year.
const activeAcademicYear = computed(() => {
  return academicYears.value.find(
    (year) => isActiveYear(year.is_active),
  )
})

// Display the selected year.
// Preserve the existing year when editing.
const selectedAcademicYear = computed(() => {
  return (
    academicYears.value.find(
      (year) => year.id === formData.value.academic_year_id,
    ) ?? activeAcademicYear.value
  )
})

// Automatically select Active Academic Year.
// Only set it when academic_year_id is empty.
watch(
  activeAcademicYear,
  (year) => {
    if (
      year &&
      !formData.value.academic_year_id
    ) {
      formData.value.academic_year_id = year.id
    }
  },
  { immediate: true },
)

// Also check when the form model becomes empty.
watch(
  () => formData.value.academic_year_id,
  (id) => {
    if (!id && activeAcademicYear.value) {
      formData.value.academic_year_id =
        activeAcademicYear.value.id
    }
  },
)

// Year Levels
const yearLevels = computed(
  () => yearLevelsData.value?.data ?? [],
)

// Majors
const majors = computed(
  () => majorsData.value?.data ?? [],
)

// Semesters
const semesters = computed(
  () => semestersData.value?.data ?? [],
)

// Subjects
const subjects = computed(
  () => subjectsData.value?.data ?? [],
)

// ==========================================
// LOADING
// ==========================================

const optionsLoading = computed(
  () =>
    academicYearsLoading.value ||
    yearLevelsLoading.value ||
    majorsLoading.value ||
    semestersLoading.value ||
    subjectsLoading.value,
)

// ==========================================
// SUBMIT
// ==========================================

function handleSubmit() {
  console.log('1. Submit button clicked')
  console.log('Form Data:', formData.value)
  console.log('Locked Subject:', props.lockedSubject)

  if (!formData.value.academic_year_id) {
    alert('Please select an Academic Year.')
    return
  }

  if (!formData.value.year_level_id) {
    alert('Please select a Year Level.')
    return
  }

  if (!formData.value.major_id) {
    alert('Please select a Major.')
    return
  }

  if (!formData.value.semester_id) {
    alert('Please select a Semester.')
    return
  }

  if (
    !props.lockedSubject &&
    !formData.value.subject_id
  ) {
    alert('Please select a Subject.')
    return
  }

  console.log('2. Validation passed. Emitting submit.')

  emit('submit')
}
</script>

<template>
  <form
    class="subject-offering-form"
    @submit.prevent="handleSubmit"
  >
    <!-- ACADEMIC YEAR -->
    <div class="form-group">
      <label for="subject-offering-academic-year">
        Academic Year <span class="required">*</span>
      </label>

      <input
        id="subject-offering-academic-year"
        :value="
          academicYearsLoading
            ? 'Loading Academic Year...'
            : selectedAcademicYear?.name ?? 'No Active Academic Year'
        "
        class="form-input readonly-input"
        type="text"
        readonly
        required
        :disabled="
          academicYearsLoading ||
          !selectedAcademicYear ||
          props.loading
        "
      />
    </div>

    <!-- YEAR LEVEL -->
    <div class="form-group">
      <label for="subject-offering-year-level">
        Year Level <span class="required">*</span>
      </label>

      <select
        id="subject-offering-year-level"
        v-model.number="formData.year_level_id"
        class="form-input"
        required
        :disabled="optionsLoading || props.loading"
      >
        <option :value="0" disabled>
          Select Year Level
        </option>

        <option
          v-for="yearLevel in yearLevels"
          :key="yearLevel.id"
          :value="yearLevel.id"
        >
          {{ yearLevel.name }}
        </option>
      </select>
    </div>

    <!-- MAJOR -->
    <div class="form-group">
      <label for="subject-offering-major">
        Major <span class="required">*</span>
      </label>

      <select
        id="subject-offering-major"
        v-model.number="formData.major_id"
        class="form-input"
        required
        :disabled="optionsLoading || props.loading"
      >
        <option :value="0" disabled>
          Select Major
        </option>

        <option
          v-for="major in majors"
          :key="major.id"
          :value="major.id"
        >
          {{ major.code }} - {{ major.name }}
        </option>
      </select>
    </div>

    <!-- SEMESTER -->
    <div class="form-group">
      <label for="subject-offering-semester">
        Semester <span class="required">*</span>
      </label>

      <select
        id="subject-offering-semester"
        v-model.number="formData.semester_id"
        class="form-input"
        required
        :disabled="optionsLoading || props.loading"
      >
        <option :value="0" disabled>
          Select Semester
        </option>

        <option
          v-for="semester in semesters"
          :key="semester.id"
          :value="semester.id"
        >
          {{ semester.name }}
        </option>
      </select>
    </div>

    <!-- SUBJECT -->
    <div class="form-group">
      <label for="subject-offering-subject">
        Subject <span class="required">*</span>
      </label>

      <!-- READONLY SUBJECT -->
      <input
        v-if="props.lockedSubject"
        id="subject-offering-subject"
        :value="`${props.lockedSubject.subject_code} - ${props.lockedSubject.subject_name}`"
        class="form-input readonly-input"
        type="text"
        readonly
      />

      <!-- NORMAL SUBJECT DROPDOWN -->
      <select
        v-else
        id="subject-offering-subject"
        v-model.number="formData.subject_id"
        class="form-input"
        required
        :disabled="optionsLoading || props.loading"
      >
        <option :value="0" disabled>
          Select Subject
        </option>

        <option
          v-for="subject in subjects"
          :key="subject.id"
          :value="subject.id"
        >
          {{ subject.subject_code }} - {{ subject.subject_name }}
        </option>
      </select>
    </div>

    <!-- ACTIONS -->
    <div class="form-actions">
      <BaseButton
        variant="outline"
        type="button"
        :disabled="props.loading"
        @click="emit('cancel')"
      >
        Cancel
      </BaseButton>

      <BaseButton
        variant="primary"
        type="submit"
        :disabled="
          props.loading ||
          academicYearsLoading ||
          !selectedAcademicYear
        "
      >
        {{ props.loading ? 'Saving...' : props.submitLabel }}
      </BaseButton>
    </div>
  </form>
</template>

<style scoped>
.subject-offering-form {
  display: flex;
  flex-direction: column;
  gap: 13px;
  width: 100%;
  overflow-x: hidden;
  scrollbar-width: none;
}

.subject-offering-form::-webkit-scrollbar {
  display: none;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
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
  min-height: 40px;
  padding: 8px 35px 8px 12px;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  outline: none;
  background-color: #fff;
  color: #302744;
  font-family: inherit;
  font-size: 13px;
  box-sizing: border-box;
  appearance: none;
  cursor: pointer;

  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238a849b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;

  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.form-input:hover:not(:disabled) {
  border-color: #c8b2f2;
}

.form-input:focus {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px rgb(130 51 244 / 10%);
}

.form-input:disabled {
  cursor: not-allowed;
  background-color: #f8f7fa;
  opacity: 0.7;
}

.form-input option {
  color: #302744;
  background: #fff;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 12px;
  border-top: 1px solid #eae6f2;
}

@media (max-width: 540px) {
  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions :deep(button) {
    width: 100%;
  }
}

.readonly-input {
  background-image: none;
  background-color: #f8f7fa;
  color: #7138e5;
  font-weight: 600;
  cursor: not-allowed;
}
</style>
