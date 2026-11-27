<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useQuery } from '@tanstack/vue-query'

import {
  Search,
  RotateCcw,
} from '@lucide/vue'

import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BasePageMessage from '@/components/ui/BasePageMessage.vue'

import { usePageMessage } from '@/composables/usePageMessage'

import StudentEnrollmentForm from '../components/student-enrollments/StudentEnrollmentForm.vue'
import StudentEnrollmentTable from '../components/student-enrollments/StudentEnrollmentTable.vue'

import { useStudentEnrollments } from '../queries/studentEnrollment.queries'

import {
  useCreateStudentEnrollment,
  useUpdateStudentEnrollment,
  useDeleteStudentEnrollment,
} from '../mutations/studentEnrollment.mutations'

import {
  getEmptyStudentEnrollmentForm,
  validateStudentEnrollmentForm,
} from '../utils/studentEnrollment.utils'

import type {
  StudentEnrollment,
  StudentEnrollmentFormData,
  StudentEnrollmentFilters,
  PaginationMeta,
  EnrollmentStatus,
} from '../interfaces/studentEnrollment.interface'

import type { AdminStudent } from '@/features/admin/api/adminStudents.api'

// ==========================================
// PROPS
// ==========================================

const props = withDefaults(
  defineProps<{
    student?: Pick<AdminStudent, 'id' | 'name' | 'email'> | null
  }>(),
  {
    student: null,
  },
)

const emit = defineEmits<{
  cancel: []
  completed: []
}>()

// ==========================================
// STATE
// ==========================================

const search = ref('')
const statusFilter = ref<EnrollmentStatus | ''>('')
const yearLevelFilter = ref<number | ''>('')
const majorFilter = ref<number | ''>('')
const semesterFilter = ref<number | ''>('')

const currentPage = ref(1)

const showModal = ref(false)
const editingId = ref<number | null>(null)

const {
  showSuccess,
  showError,
  clearError,
  clearMessages,
} = usePageMessage()

const formData = ref<StudentEnrollmentFormData>(
  getEmptyStudentEnrollmentForm(),
)

// ==========================================
// FILTER OPTIONS
// ==========================================

const { data: yearLevelsData } = useQuery({
  queryKey: ['year-levels', 'enrollment-filter'],
  queryFn: async () => {
    const { yearLevelsApi } = await import('../api/yearLevels.api')
    return yearLevelsApi.getAll({ per_page: 100 })
  },
})

const { data: majorsData } = useQuery({
  queryKey: ['majors', 'enrollment-filter'],
  queryFn: async () => {
    const { majorsApi } = await import('../api/majors.api')
    return majorsApi.getAll({ per_page: 100 })
  },
})

const { data: semestersData } = useQuery({
  queryKey: ['semesters', 'enrollment-filter'],
  queryFn: async () => {
    const { semestersApi } = await import('../api/semesters.api')
    return semestersApi.getAll({ per_page: 100 })
  },
})

const yearLevels = computed(
  () => yearLevelsData.value?.data ?? [],
)

const majors = computed(
  () => majorsData.value?.data ?? [],
)

const semesters = computed(
  () => semestersData.value?.data ?? [],
)

// ==========================================
// QUERY
// ==========================================

const filters = computed<StudentEnrollmentFilters>(() => ({
  search: search.value.trim() || undefined,
  status: statusFilter.value || undefined,

  year_level_id:
    yearLevelFilter.value !== ''
      ? Number(yearLevelFilter.value)
      : undefined,

  major_id:
    majorFilter.value !== ''
      ? Number(majorFilter.value)
      : undefined,

  semester_id:
    semesterFilter.value !== ''
      ? Number(semesterFilter.value)
      : undefined,

  page: currentPage.value,
  per_page: 10,
}))

const {
  data,
  isPending,
  isFetching,
  error,
} = useStudentEnrollments(filters)

const enrollments = computed<StudentEnrollment[]>(
  () => data.value?.data ?? [],
)

const pagination = computed<PaginationMeta>(
  () => data.value?.meta ?? {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  },
)

const loading = computed(
  () => isPending.value || isFetching.value,
)

// ==========================================
// TOTAL ENROLLMENTS
// ==========================================

const totalEnrollments = computed(
  () => pagination.value.total,
)

// ==========================================
// QUERY ERROR
// ==========================================

watch(error, (queryError) => {
  if (queryError) {
    showError(
      queryError instanceof Error
        ? queryError.message
        : 'Unable to load student enrollments.',
    )
  }
})

// ==========================================
// MUTATIONS
// ==========================================

const createEnrollment = useCreateStudentEnrollment()
const updateEnrollment = useUpdateStudentEnrollment()
const deleteEnrollmentMutation = useDeleteStudentEnrollment()

const saving = computed(
  () =>
    createEnrollment.isPending.value ||
    updateEnrollment.isPending.value,
)

// ==========================================
// EDIT MODAL
// ==========================================

const modalTitle = computed(() =>
  editingId.value !== null
    ? 'Edit Student Enrollment'
    : 'Add Student Enrollment',
)

const submitLabel = computed(() =>
  editingId.value !== null
    ? 'Update Enrollment'
    : 'Add Enroll',
)

const modalStudent = computed(() => {
  if (editingId.value !== null) {
    const enrollment = enrollments.value.find(
      item => item.id === editingId.value,
    )

    return {
      id: enrollment?.user?.id ?? 0,
      name: enrollment?.user?.name ?? '',
      email: enrollment?.user?.email ?? '',
    }
  }

  return props.student
})

function openCreateModal() {
  if (!props.student?.id) {
    showError('Please create a student before creating an enrollment.')
    return
  }

  editingId.value = null

  formData.value = {
    ...getEmptyStudentEnrollmentForm(),
    user_id: Number(props.student.id),
    status: 'active',
  }

  clearMessages()
  showModal.value = true
}

function openEditModal(enrollment: StudentEnrollment) {
  editingId.value = enrollment.id

  formData.value = {
    user_id: Number(enrollment.user?.id ?? 0),
    academic_year_id: Number(enrollment.academic_year?.id ?? 0),
    year_level_id: Number(enrollment.year_level?.id ?? 0),
    major_id: Number(enrollment.major?.id ?? 0),
    semester_id: Number(enrollment.semester?.id ?? 0),
    status: enrollment.status,
  }

  clearMessages()
  showModal.value = true
}

// ==========================================
// CLOSE MODAL
// ==========================================

function closeModal() {
  if (saving.value) return

  showModal.value = false
  editingId.value = null
  formData.value = getEmptyStudentEnrollmentForm()
}

// ==========================================
// API ERROR MESSAGE
// ==========================================

function getApiErrorMessage(error: unknown): string {
  const response = (
    error as {
      response?: {
        data?: {
          message?: string
          errors?: Record<string, string[]>
        }
      }
    }
  )?.response?.data

  if (response?.errors) {
    const firstError = Object.values(response.errors)[0]?.[0]

    if (firstError) return firstError
  }

  return response?.message
    ?? (error instanceof Error
      ? error.message
      : 'Something went wrong.')
}

// ==========================================
// CREATE / UPDATE
// ==========================================

async function submitForm() {
  clearError()

  if (editingId.value === null && !props.student?.id) {
    showError('Please create a student before creating an enrollment.')
    return
  }

  const payload: StudentEnrollmentFormData = {
    user_id:
      editingId.value !== null
        ? Number(formData.value.user_id)
        : Number(props.student!.id),

    academic_year_id: Number(formData.value.academic_year_id),
    year_level_id: Number(formData.value.year_level_id),
    major_id: Number(formData.value.major_id),
    semester_id: Number(formData.value.semester_id),

    status:
      editingId.value !== null
        ? formData.value.status
        : 'active',
  }

  const validationError =
    validateStudentEnrollmentForm(payload)

  if (validationError) {
    console.error('Enrollment validation failed:', payload)
    showError(validationError)
    return
  }

  console.log('Enrollment payload:', payload)

  try {
    if (editingId.value !== null) {
      await updateEnrollment.mutateAsync({
        id: editingId.value,
        data: payload,
      })

      showSuccess('Student enrollment updated successfully.')
    } else {
      await createEnrollment.mutateAsync(payload)

      showSuccess('Student enrollment created successfully.')
    }

    showModal.value = false
    editingId.value = null
    formData.value = getEmptyStudentEnrollmentForm()

    currentPage.value = 1

    if (props.student) {
      emit('completed')
    }
  } catch (error: unknown) {
    console.error('Failed to save enrollment:', error)
    showError(getApiErrorMessage(error))
  }
}

// ==========================================
// DELETE
// ==========================================

async function handleDelete(enrollment: StudentEnrollment) {
  const studentName =
    enrollment.user?.name ?? 'this student'

  const confirmed = window.confirm(
    `Are you sure you want to delete the enrollment for "${studentName}"?`,
  )

  if (!confirmed) return

  clearMessages()

  const wasLastItem =
    enrollments.value.length === 1 &&
    currentPage.value > 1

  try {
    await deleteEnrollmentMutation.mutateAsync(
      enrollment.id,
    )

    showSuccess('Student enrollment deleted successfully.')

    if (wasLastItem) {
      currentPage.value -= 1
    }
  } catch (error: unknown) {
    console.error('Failed to delete enrollment:', error)
    showError(getApiErrorMessage(error))
  }
}

// ==========================================
// SEARCH AND FILTERS
// ==========================================

function handleSearch() {
  clearError()
  currentPage.value = 1
}

function handleFilterChange() {
  clearError()
  currentPage.value = 1
}

function clearFilters() {
  search.value = ''
  statusFilter.value = ''
  yearLevelFilter.value = ''
  majorFilter.value = ''
  semesterFilter.value = ''

  currentPage.value = 1
  clearError()
}

// ==========================================
// PAGINATION
// ==========================================

function handlePageChange(page: number) {
  currentPage.value = page
}
</script>

<template>
  <section class="student-enrollments-page space-y-6">

    
<!-- Page Heading -->

    <header class="page-header">
      <div>
        <p class="eyebrow">ADMINISTRATION</p>
        <h1 id="campus-locations-title">
          Student Enrollments
        </h1>
      </div>
    </header>

    <BasePageMessage />

    <BaseCard padded class="academic-list-card">

      <div class="list-header">
        <div class="list-heading">
          <h2>Student Enrollment List</h2>
          <p>
            View and manage all student academic enrollments.
          </p>
        </div>

        <div class="list-header-actions">
          <div class="total-count">
            <span>Total Enrollments</span>
            <strong>{{ totalEnrollments }}</strong>
          </div>

          <button
            v-if="student"
            type="button"
            class="add-enroll-button"
            @click="openCreateModal"
          >
            + Add Enroll
          </button>
        </div>
      </div>

      <form
        class="filters-toolbar"
        @submit.prevent="handleSearch"
      >

        <div class="search-input-wrapper">
          <Search :size="17" />

          <input
            v-model="search"
            type="search"
            placeholder="Search student name or email..."
          />
        </div>

        <div class="filter-group">
          <select
            v-model="statusFilter"
            class="filter-input"
            aria-label="Filter by status"
            @change="handleFilterChange"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="withdrawn">Withdrawn</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>

        <div class="filter-group">
          <select
            v-model="yearLevelFilter"
            class="filter-input"
            aria-label="Filter by year level"
            @change="handleFilterChange"
          >
            <option value="">All Year Levels</option>

            <option
              v-for="yearLevel in yearLevels"
              :key="yearLevel.id"
              :value="yearLevel.id"
            >
              {{ yearLevel.name }}
            </option>
          </select>
        </div>

        <div class="filter-group">
          <select
            v-model="majorFilter"
            class="filter-input"
            aria-label="Filter by major"
            @change="handleFilterChange"
          >
            <option value="">All Majors</option>

            <option
              v-for="major in majors"
              :key="major.id"
              :value="major.id"
            >
              {{ major.code }} - {{ major.name }}
            </option>
          </select>
        </div>

        <div class="filter-group">
          <select
            v-model="semesterFilter"
            class="filter-input"
            aria-label="Filter by semester"
            @change="handleFilterChange"
          >
            <option value="">All Semesters</option>

            <option
              v-for="semester in semesters"
              :key="semester.id"
              :value="semester.id"
            >
              {{ semester.name }}
            </option>
          </select>
        </div>

        <button
          type="submit"
          class="search-button"
        >
          <Search :size="16" />
          Search
        </button>

        <button
          type="button"
          class="clear-filter-button"
          @click="clearFilters"
        >
          <RotateCcw :size="15" />
          Clear
        </button>

      </form>

      <StudentEnrollmentTable
        :enrollments="enrollments"
        :loading="loading"
        :pagination="pagination"
        @edit="openEditModal"
        @delete="handleDelete"
        @page="handlePageChange"
      />

    </BaseCard>

    <BaseModal
      v-model="showModal"
      :title="modalTitle"
      @close="closeModal"
    >
      <StudentEnrollmentForm
        v-model="formData"
        :student="modalStudent"
        :loading="saving"
        :submit-label="submitLabel"
        :is-editing="editingId !== null"
        @submit="submitForm"
        @cancel="closeModal"
      />
    </BaseModal>

  </section>
</template>

<style scoped>

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 18px;
}

.eyebrow {
  margin: 0 0 8px;
  color: #6a43cc;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.15em;
  text-transform: uppercase;
}

.page-header h1 {
  margin: 0;
  font-size: clamp(28px, 2.7vw, 36px);
}

@media (max-width: 760px) {
  .page-header {
    align-items: flex-start;
  }
}

.student-enrollments-page {
  width: 100%;
  min-width: 0;
  color: #302744;
}

.academic-list-card {
  width: 100%;
  min-width: 0;
  margin-top: 12px;
}

.list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 22px;
}

.list-heading {
  min-width: 0;
}

.list-heading h2 {
  margin: 0;
  color: #302744;
  font-size: 18px;
  font-weight: 750;
}

.list-heading p {
  margin: 5px 0 0;
  color: #8a849b;
  font-size: 12px;
}

.list-header-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 16px;
}

.total-count {
  display: flex;
  align-items: center;
  gap: 9px;
  white-space: nowrap;
}

.total-count span {
  color: #8a849b;
  font-size: 12px;
  font-weight: 600;
}

.total-count strong {
  display: grid;
  min-width: 34px;
  height: 34px;
  padding: 0 9px;
  place-items: center;
  border: 1px solid #e8ddff;
  border-radius: 9px;
  background: #f5efff;
  color: #7138e5;
  font-size: 15px;
  font-weight: 750;
}

.add-enroll-button {
  display: inline-flex;
  height: 39px;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 0 15px;
  border: 1px solid #7138e5;
  border-radius: 9px;
  background: #7138e5;
  color: #fff;
  font-family: inherit;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s;
}

.add-enroll-button:hover {
  background: #5e2bc5;
  border-color: #5e2bc5;
}

.filters-toolbar {
  display: grid;
  grid-template-columns:
    minmax(180px, 1.5fr)
    minmax(120px, 1fr)
    minmax(120px, 1fr)
    minmax(120px, 1fr)
    minmax(120px, 1fr)
    auto
    auto;
  align-items: center;
  gap: 10px;
  width: 100%;
  margin-bottom: 20px;
}

.search-input-wrapper {
  display: flex;
  width: 100%;
  height: 39px;
  min-width: 0;
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

.filter-group {
  width: 100%;
  min-width: 0;
}

.filter-input {
  width: 100%;
  height: 39px;
  padding: 0 10px;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  outline: none;
  background: #fff;
  color: #49415d;
  font-family: inherit;
  font-size: 12px;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.filter-input:hover {
  border-color: #c8b2f2;
}

.filter-input:focus {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px #8233f41a;
}

.search-button,
.clear-filter-button {
  display: inline-flex;
  height: 39px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 0 13px;
  border: 1px solid #e2ddef;
  border-radius: 9px;
  background: #fff;
  color: #5e566f;
  font-family: inherit;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s;
}

.search-button:hover,
.clear-filter-button:hover {
  border-color: #8233f4;
  background: #f7f2ff;
  color: #8233f4;
}

@media (max-width: 1200px) {
  .filters-toolbar {
    grid-template-columns:
      minmax(180px, 1.5fr)
      repeat(4, minmax(100px, 1fr))
      auto
      auto;
    gap: 8px;
  }

  .filter-input {
    padding: 0 8px;
    font-size: 11px;
  }

  .search-button,
  .clear-filter-button {
    padding: 0 10px;
  }
}

@media (max-width: 1050px) {
  .filters-toolbar {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .search-input-wrapper {
    grid-column: span 2;
  }
}

@media (max-width: 640px) {
  .list-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .list-header-actions {
    width: 100%;
    justify-content: flex-end;
  }

  .filters-toolbar {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .search-input-wrapper {
    grid-column: span 2;
  }

  .filter-group {
    grid-column: span 1;
  }

  .search-button,
  .clear-filter-button {
    width: 100%;
  }
}
</style>
