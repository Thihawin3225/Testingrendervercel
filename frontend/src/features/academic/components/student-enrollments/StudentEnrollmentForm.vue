
<script setup lang="ts">
import { computed, watch } from 'vue'
import { useQuery } from '@tanstack/vue-query'

import BaseButton from '@/components/ui/BaseButton.vue'

import type { AdminStudent } from '@/features/admin/api/adminStudents.api'

import { useAcademicYears } from '../../queries/academicYear.queries'
import { useYearLevels } from '../../queries/yearLevel.queries'
import { useMajors } from '../../queries/major.queries'
import { useSemesters } from '../../queries/semester.queries'

import { subjectOfferingsApi } from '../../api/subjectOfferings.api'

import type { StudentEnrollmentFormData } from '../../interfaces/studentEnrollment.interface'
import type {
  SubjectOffering,
  SubjectOfferingFilters,
} from '../../interfaces/subjectOffering.interface'

// ==========================================
// FORM MODEL
// ==========================================

const formData = defineModel<StudentEnrollmentFormData>({
  required: true,
})

// ==========================================
// PROPS
// ==========================================

type EnrollmentStudent = {
  id?: AdminStudent['id']
  name: string
  email: string
}

const props = withDefaults(
  defineProps<{
    loading?: boolean
    submitLabel?: string
    isEditing?: boolean
    deferStudentCreation?: boolean
    student?: EnrollmentStudent | null
  }>(),
  {
    loading: false,
    submitLabel: 'Add Enrollment',
    isEditing: false,
    deferStudentCreation: false,
    student: null,
  },
)

// ==========================================
// EMITS
// ==========================================

const emit = defineEmits<{
  submit: []
  cancel: []
}>()

// ==========================================
// ACADEMIC YEAR
// ==========================================

type AcademicYearOption = {
  id: number | string
  name: string
  is_active?: boolean | number | string
}

const {
  data: academicYearsData,
  isPending: academicYearsLoading,
  isError: academicYearsError,
} = useAcademicYears({
  per_page: 100,
})

// Support different API response formats:
// 1. { data: [...] }
// 2. { data: { data: [...] } }
// 3. [...]
const academicYears = computed<AcademicYearOption[]>(() => {
  const response = academicYearsData.value as unknown

  if (Array.isArray(response)) {
    return response as AcademicYearOption[]
  }

  if (!response || typeof response !== 'object') {
    return []
  }

  const root = response as { data?: unknown }

  if (Array.isArray(root.data)) {
    return root.data as AcademicYearOption[]
  }

  if (root.data && typeof root.data === 'object') {
    const nested = root.data as { data?: unknown }

    if (Array.isArray(nested.data)) {
      return nested.data as AcademicYearOption[]
    }
  }

  return []
})

// Find active academic year.
// Supports true, 1, "1", and "true".
const activeAcademicYear = computed(() => {
  const activeYear = academicYears.value.find((year) => {
    const activeValue = String(year.is_active).toLowerCase()

    return (
      year.is_active === true ||
      activeValue === '1' ||
      activeValue === 'true'
    )
  })

  // Use ID 1 only if the API actually returns that record.
  return (
    activeYear ??
    academicYears.value.find((year) => Number(year.id) === 1) ??
    null
  )
})

// Create: use active academic year.
// Edit: use existing enrollment academic year.
const effectiveAcademicYearId = computed(() => {
  if (props.isEditing) {
    return Number(formData.value.academic_year_id) || 0
  }

  return Number(activeAcademicYear.value?.id) || 0
})

// Automatically assign active academic year when creating.
watch(
  activeAcademicYear,
  (year) => {
    if (!props.isEditing && year) {
      formData.value.academic_year_id = Number(year.id)

      console.log('Active Academic Year assigned:', {
        id: year.id,
        name: year.name,
      })
    }
  },
  { immediate: true },
)

// Find selected academic year.
const selectedAcademicYear = computed(() => {
  const id = effectiveAcademicYearId.value

  if (!id) return null

  return (
    academicYears.value.find(
      (year) => Number(year.id) === id,
    ) ?? null
  )
})

// Display academic year.
const academicYearToDisplay = computed(() => {
  return selectedAcademicYear.value
})

const academicYearName = computed(() => {
  if (academicYearsLoading.value) {
    return 'Loading academic year...'
  }

  if (academicYearsError.value) {
    return 'Failed to load academic year'
  }

  return academicYearToDisplay.value?.name ?? 'No academic year found'
})

// ==========================================
// YEAR LEVEL
// ==========================================

const {
  data: yearLevelsData,
  isPending: yearLevelsLoading,
} = useYearLevels({
  per_page: 100,
})

const yearLevels = computed(
  () => yearLevelsData.value?.data ?? [],
)

// ==========================================
// MAJOR
// ==========================================

const {
  data: majorsData,
  isPending: majorsLoading,
} = useMajors({
  per_page: 100,
})

const majors = computed(
  () => majorsData.value?.data ?? [],
)

// ==========================================
// SEMESTER
// ==========================================

const {
  data: semestersData,
  isPending: semestersLoading,
} = useSemesters({
  per_page: 100,
})

const semesters = computed(
  () => semestersData.value?.data ?? [],
)

// ==========================================
// SUBJECT OFFERINGS
// ==========================================

// Convert IDs to numbers before sending to API.
const subjectOfferingFilters = computed<SubjectOfferingFilters>(() => ({
  academic_year_id: effectiveAcademicYearId.value || undefined,
  year_level_id: Number(formData.value.year_level_id) || undefined,
  major_id: Number(formData.value.major_id) || undefined,
  semester_id: Number(formData.value.semester_id) || undefined,
  per_page: 100,
}))

// Load subjects only when all required IDs are available.
const canLoadSubjects = computed(() => {
  return (
    effectiveAcademicYearId.value > 0 &&
    Number(formData.value.year_level_id) > 0 &&
    Number(formData.value.major_id) > 0 &&
    Number(formData.value.semester_id) > 0
  )
})

// Fetch subject offerings.
const {
  data: subjectOfferingsData,
  isPending: subjectsLoading,
  isFetching: subjectsFetching,
  isError: subjectsError,
  error: subjectsErrorMessage,
} = useQuery({
  queryKey: computed(() => [
    'subject-offerings',
    subjectOfferingFilters.value.academic_year_id,
    subjectOfferingFilters.value.year_level_id,
    subjectOfferingFilters.value.major_id,
    subjectOfferingFilters.value.semester_id,
  ]),

  queryFn: () =>
    subjectOfferingsApi.getAll(subjectOfferingFilters.value),

  enabled: canLoadSubjects,
})

// Extract unique subjects.
const subjects = computed(() => {
  const offerings = subjectOfferingsData.value?.data ?? []

  const uniqueSubjects = new Map<
    number,
    SubjectOffering['subject']
  >()

  offerings.forEach((offering) => {
    if (offering.subject?.id) {
      uniqueSubjects.set(
        Number(offering.subject.id),
        offering.subject,
      )
    }
  })

  return Array.from(uniqueSubjects.values())
})

// Subject API error message.
const subjectErrorText = computed(() => {
  const error = subjectsErrorMessage.value

  if (error instanceof Error) {
    return error.message
  }

  return 'Unable to load subjects. Please try again.'
})

// ==========================================
// LOADING
// ==========================================

const optionsLoading = computed(
  () =>
    academicYearsLoading.value ||
    yearLevelsLoading.value ||
    majorsLoading.value ||
    semestersLoading.value,
)

const subjectLoading = computed(
  () => subjectsFetching.value,
)

// ==========================================
// SUBMIT
// ==========================================

function handleSubmit() {
  // Student name and email are required in both workflows.
  if (!props.student?.name || !props.student?.email) {
    console.error('Student information is missing:', props.student)
    return
  }

  // Active academic year is required.
  if (!effectiveAcademicYearId.value) {
    console.error('Active Academic Year is missing.')
    return
  }

  // In the normal enrollment page, the student must already exist.
  // In the dashboard's combined workflow, the backend creates the student
  // and enrollment together, so an ID is not available yet.
  if (!props.deferStudentCreation && !props.student?.id) {
    console.error('Student ID is missing:', props.student)
    return
  }

  // Assign active academic year ID.
  formData.value.academic_year_id = effectiveAcademicYearId.value

  // Only assign an ID when the student already exists.
  if (props.student?.id) {
    formData.value.user_id = Number(props.student.id)
  } else {
    formData.value.user_id = 0
  }

  // Set default status when creating enrollment.
  if (!props.isEditing) {
    formData.value.status = 'active'
  }

  console.log('Enrollment form data:', {
    ...formData.value,
  })

  emit('submit')
}
</script>

<template>
  <form
    class="student-enrollment-form"
    @submit.prevent="handleSubmit"
  >
    <!-- STUDENT NAME -->
    <div class="form-group">
      <label for="enrollment-student-name">
        Student Name
      </label>

      <input
        id="enrollment-student-name"
        :value="props.student?.name ?? ''"
        class="form-input readonly-input"
        type="text"
        readonly
      />
    </div>

    <!-- STUDENT EMAIL -->
    <div class="form-group">
      <label for="enrollment-student-email">
        Student Email
      </label>

      <input
        id="enrollment-student-email"
        :value="props.student?.email ?? ''"
        class="form-input readonly-input"
        type="email"
        readonly
      />
    </div>

    <!-- ACADEMIC YEAR -->
    <div class="form-group">
      <label for="enrollment-academic-year">
        Academic Year <span class="required">*</span>
      </label>

      <input
        id="enrollment-academic-year"
        :value="academicYearName"
        class="form-input readonly-input"
        type="text"
        readonly
        required
      />

      <p
        v-if="!academicYearsLoading && !academicYearToDisplay"
        class="field-hint"
      >
        No academic year found. Please check the academic year settings.
      </p>
    </div>

    <!-- YEAR LEVEL -->
    <div class="form-group">
      <label for="enrollment-year-level">
        Year Level <span class="required">*</span>
      </label>

      <select
        id="enrollment-year-level"
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
      <label for="enrollment-major">
        Major <span class="required">*</span>
      </label>

      <select
        id="enrollment-major"
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
      <label for="enrollment-semester">
        Semester <span class="required">*</span>
      </label>

      <select
        id="enrollment-semester"
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

    <!-- SUBJECTS -->
    <div class="form-group subject-section">
      <label>Available Subjects</label>

      <div
        v-if="!effectiveAcademicYearId"
        class="subject-message empty-subjects"
      >
        No active academic year found.
      </div>

      <div
        v-else-if="!canLoadSubjects"
        class="subject-message"
      >
        Please select Year Level, Major and Semester to view available subjects.
      </div>

      <div
        v-else-if="subjectLoading"
        class="subject-message"
      >
        <span class="loading-spinner"></span>
        Loading available subjects...
      </div>

      <div
        v-else-if="subjectsError"
        class="subject-message error-message"
      >
        {{ subjectErrorText }}
      </div>

      <div
        v-else-if="subjects.length > 0"
        class="subject-list"
      >
        <div
          v-for="subject in subjects"
          :key="subject.id"
          class="subject-item"
        >
          <div class="subject-info">
            <span class="subject-code">
              {{ subject.subject_code }}
            </span>

            <span class="subject-name">
              {{ subject.subject_name }}
            </span>
          </div>

          <span class="subject-indicator">
            Available
          </span>
        </div>
      </div>

      <div
        v-else
        class="subject-message empty-subjects"
      >
        No subjects available for the selected academic information.
      </div>
    </div>

    <!-- STATUS -->
    <div v-if="props.isEditing" class="form-group">
      <label for="enrollment-status">
        Status <span class="required">*</span>
      </label>

      <select
        id="enrollment-status"
        v-model="formData.status"
        class="form-input"
        required
        :disabled="props.loading"
      >
        <option value="active">Active</option>
        <option value="completed">Completed</option>
        <option value="withdrawn">Withdrawn</option>
        <option value="suspended">Suspended</option>
      </select>
    </div>

    <!-- LOADING OPTIONS -->
    <div
      v-if="optionsLoading"
      class="loading-message"
    >
      <span class="loading-spinner"></span>
      Loading enrollment options...
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
          optionsLoading ||
          subjectLoading ||
          !academicYearToDisplay ||
          !formData.year_level_id ||
          !formData.major_id ||
          !formData.semester_id ||
          !props.student?.name ||
          !props.student?.email ||
          (!props.deferStudentCreation && !props.student?.id) ||
          (props.isEditing && !props.student?.id)
        "
      >
        {{ props.loading ? 'Saving...' : props.submitLabel }}
      </BaseButton>
    </div>
  </form>
</template>

<style scoped>
.student-enrollment-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
  width: 100%;
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
  min-height: 42px;
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
  transition: border-color 0.2s, box-shadow 0.2s;
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

.readonly-input {
  cursor: not-allowed;
  background-color: #f8f7fa;
  color: #7138e5;
  font-weight: 650;
  background-image: none;
}

.field-hint {
  margin: 0;
  color: #c17a26;
  font-size: 11px;
}

.subject-section {
  margin-top: 2px;
}

.subject-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 220px;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: none;
  -ms-overflow-style: none;
  padding: 10px;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  background: #fcfbff;
}

.subject-list::-webkit-scrollbar {
  display: none;
  width: 0;
  height: 0;
}

.subject-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px;
  border: 1px solid #eee9f8;
  border-radius: 8px;
  background: #fff;
}

.subject-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.subject-code {
  color: #7138e5;
  font-size: 11px;
  font-weight: 750;
}

.subject-name {
  color: #49415d;
  font-size: 13px;
  font-weight: 600;
  overflow-wrap: anywhere;
}

.subject-indicator {
  flex-shrink: 0;
  padding: 4px 8px;
  border-radius: 20px;
  background: #f0eaff;
  color: #7138e5;
  font-size: 10px;
  font-weight: 650;
}

.subject-message {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 45px;
  padding: 10px 12px;
  border: 1px dashed #dcd2ef;
  border-radius: 9px;
  background: #faf8ff;
  color: #8a849b;
  font-size: 12px;
  line-height: 1.5;
}

.empty-subjects {
  color: #c17a26;
}

.error-message {
  border-color: #f1b8c0;
  background: #fff7f8;
  color: #c03950;
}

.loading-message {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  border: 1px solid #e9ddff;
  border-radius: 9px;
  background: #faf7ff;
  color: #7138e5;
  font-size: 12px;
}

.loading-spinner {
  width: 15px;
  height: 15px;
  flex-shrink: 0;
  border: 2px solid #d9c8ff;
  border-top-color: #7138e5;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 13px;
  border-top: 1px solid #eae6f2;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
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
