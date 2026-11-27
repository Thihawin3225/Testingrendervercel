<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { Search, ShieldX, Trash2, UserCog } from '@lucide/vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'

import BaseButton from '@/components/ui/BaseButton.vue'
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import { useToast } from '@/composables/useToast'

import {
  createStudentWithEnrollment,
  deleteStudent,
  fetchAllStudents,
  updateStudent,
} from '@/features/admin/api/adminStudents.api'

import type {
  AdminStudent,
  CreateStudentPayload,
  CreateStudentWithEnrollmentPayload,
  StudentUpdatePayload,
} from '@/features/admin/api/adminStudents.api'

import CreateStudentForm from '../components/CreateStudentForm.vue'
import StudentEnrollmentForm from '@/features/academic/components/student-enrollments/StudentEnrollmentForm.vue'

import type {
  StudentEnrollmentFormData,
} from '@/features/academic/interfaces/studentEnrollment.interface'

// INITIALIZATION
const route = useRoute()
const queryClient = useQueryClient()
const toast = useToast()

// PAGE STATE
const searchQuery = ref('')
const statusFilter = ref('all')

const allowedStatusFilters = [
  'all',
  'active',
  'suspended',
  'inactive',
]

function isAllowedStatus(value: unknown): value is string {
  return (
    typeof value === 'string' &&
    allowedStatusFilters.includes(value)
  )
}

if (isAllowedStatus(route.query.status)) {
  statusFilter.value = route.query.status
}

watch(
  () => route.query.status,
  (value) => {
    if (isAllowedStatus(value)) {
      statusFilter.value = value
    }
  },
)

const selectedStudentId = ref<number | null>(null)

// CREATE STUDENT + ENROLLMENT STATE
const pendingStudentPayload = ref<CreateStudentPayload | null>(null)

const enrollmentStudent = ref<{
  name: string
  email: string
} | null>(null)

const showCreateStudentForm = ref(false)
const showEnrollmentForm = ref(false)

const emptyEnrollmentForm = (): StudentEnrollmentFormData => ({
  user_id: 0,
  academic_year_id: 0,
  year_level_id: 0,
  major_id: 0,
  semester_id: 0,
  status: 'active',
})

const enrollmentFormData = ref<StudentEnrollmentFormData>(
  emptyEnrollmentForm(),
)

// EDIT DIALOG STATE
const editDialog = ref<HTMLDialogElement | null>(null)
const deleteConfirmOpen = ref(false)
const statusConfirmOpen = ref(false)

const statusAction = ref<
  'suspend' | 'reactivate' | 'mark-inactive' | null
>(null)

// EDIT FORM VALIDATION
const editStudentSchema = toTypedSchema(
  z.object({
    name: z.string()
      .trim()
      .min(1, 'Enter the student name.')
      .max(120, 'Name is too long.'),

    email: z.string()
      .trim()
      .min(1, 'Enter the student email.')
      .email('Enter a valid university email address.')
      .refine(
        (value) =>
          /^[A-Za-z0-9._%+-]+@ucspyay\.edu\.mm$/i.test(value),
        {
          message: 'Use a valid @ucspyay.edu.mm university email.',
        },
      ),
  }),
)

const {
  errors,
  resetForm,
  defineField,
  handleSubmit,
} = useForm({
  validationSchema: editStudentSchema,
  initialValues: {
    name: '',
    email: '',
  },
})

const [name, nameAttrs] = defineField('name')
const [email, emailAttrs] = defineField('email')

// FETCH STUDENTS
const {
  data,
  isLoading,
  isError,
} = useQuery({
  queryKey: ['admin-students'],
  queryFn: fetchAllStudents,
})

const students = computed(() => data.value ?? [])

const filteredStudents = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return students.value.filter((student) => {
    const matchesSearch =
      !query ||
      student.name.toLowerCase().includes(query) ||
      student.email.toLowerCase().includes(query) ||
      (student.student_number ?? '').toLowerCase().includes(query)

    const matchesStatus =
      statusFilter.value === 'all' ||
      student.status === statusFilter.value

    return matchesSearch && matchesStatus
  })
})

const selectedStudent = computed(
  () =>
    students.value.find(
      (student) => student.id === selectedStudentId.value,
    ) ?? null,
)

// ERROR HANDLING
function isSafeErrorMessage(message: unknown): message is string {
  return (
    typeof message === 'string' &&
    message.length <= 240 &&
    !/(?:stack trace|sqlstate|vendor\/|#\d+\s| in \S+\.php:\d+)/i.test(message)
  )
}

function getErrorMessage(error: unknown, fallback: string): string {
  if (typeof error !== 'object' || error === null || !('response' in error)) {
    return fallback
  }

  const response = (
    error as {
      response?: {
        status?: number
        data?: Record<string, unknown>
      }
    }
  ).response

  if (!response || response.status === undefined) {
    return fallback
  }

  if (response.status >= 500) {
    return fallback
  }

  if (isSafeErrorMessage(response.data?.message)) {
    return response.data.message
  }

  const validationErrors = response.data?.errors

  if (typeof validationErrors === 'object' && validationErrors !== null) {
    const firstError = Object.values(validationErrors)[0]

    if (
      Array.isArray(firstError) &&
      firstError.length > 0 &&
      isSafeErrorMessage(firstError[0])
    ) {
      return firstError[0]
    }
  }

  return fallback
}

// EDIT DIALOG
function resetEditForm(student?: AdminStudent) {
  resetForm({
    values: student
      ? {
          name: student.name,
          email: student.email,
        }
      : {
          name: '',
          email: '',
        },
  })
}

function clearConfirmationState() {
  deleteConfirmOpen.value = false
  statusConfirmOpen.value = false
  statusAction.value = null
}

function openEditModal(student: AdminStudent) {
  selectedStudentId.value = student.id
  resetEditForm(student)
  clearConfirmationState()
  editDialog.value?.showModal()
}

function closeEditModal() {
  editDialog.value?.close()
}

function handleEditDialogClose() {
  selectedStudentId.value = null
  resetEditForm()
  clearConfirmationState()
}

function handleDialogClick(event: MouseEvent) {
  if (event.target === editDialog.value) {
    closeEditModal()
  }
}

// MUTATIONS
const createStudentWithEnrollmentMutation = useMutation({
  mutationFn: (payload: CreateStudentWithEnrollmentPayload) =>
    createStudentWithEnrollment(payload),
})

const updateMutation = useMutation({
  mutationFn: ({
    studentId,
    payload,
  }: {
    studentId: number
    payload: StudentUpdatePayload
  }) => updateStudent(studentId, payload),
})

const deleteMutation = useMutation({
  mutationFn: (studentId: number) => deleteStudent(studentId),
})

// REFRESH QUERIES
async function refreshStudentQueries() {
  await Promise.all([
    queryClient.invalidateQueries({
      queryKey: ['admin-students'],
    }),
    queryClient.invalidateQueries({
      queryKey: ['admin-dashboard-students'],
    }),
  ])
}

// RESET ENROLLMENT DATA
function resetEnrollmentData() {
  pendingStudentPayload.value = null
  enrollmentStudent.value = null
  enrollmentFormData.value = emptyEnrollmentForm()
}

// CREATE STUDENT - STEP 1
function handleCreateStudent(payload: CreateStudentPayload) {
  // Temporarily store student data. No API request yet.
  pendingStudentPayload.value = payload

  enrollmentStudent.value = {
    name: payload.name,
    email: payload.email,
  }

  enrollmentFormData.value = emptyEnrollmentForm()

  showCreateStudentForm.value = false
  showEnrollmentForm.value = true
}

// CREATE STUDENT + ENROLLMENT - STEP 2
async function handleCreateEnrollment() {
  const studentPayload = pendingStudentPayload.value

  if (!studentPayload || !enrollmentStudent.value) {
    toast.error('Student information is missing. Please try again.')
    return
  }

  if (createStudentWithEnrollmentMutation.isPending.value) {
    return
  }

  const payload: CreateStudentWithEnrollmentPayload = {
    ...studentPayload,
    academic_year_id: enrollmentFormData.value.academic_year_id,
    year_level_id: enrollmentFormData.value.year_level_id,
    major_id: enrollmentFormData.value.major_id,
    semester_id: enrollmentFormData.value.semester_id,
    status: enrollmentFormData.value.status,
  }

  try {
    const response =
      await createStudentWithEnrollmentMutation.mutateAsync(payload)

    showEnrollmentForm.value = false
    resetEnrollmentData()

    await refreshStudentQueries()

    toast.success(response.message)
  } catch (error) {
    toast.error(
      getErrorMessage(
        error,
        'The student and enrollment could not be created. Please try again.',
      ),
    )
  }
}

// CANCEL CREATE / ENROLLMENT
function cancelEnrollment() {
  showEnrollmentForm.value = false
  resetEnrollmentData()
}

function openCreateStudentForm() {
  resetEnrollmentData()
  showEnrollmentForm.value = false
  showCreateStudentForm.value = true
}

function cancelCreateStudent() {
  showCreateStudentForm.value = false
  resetEnrollmentData()
}

// UPDATE STUDENT
const saveStudentChanges = handleSubmit(async (values) => {
  const student = selectedStudent.value

  if (!student) return

  try {
    await updateMutation.mutateAsync({
      studentId: student.id,
      payload: {
        name: values.name.trim(),
        email: values.email.trim(),
      },
    })

    closeEditModal()
    await refreshStudentQueries()

    toast.success('Student information updated successfully.')
  } catch (error) {
    toast.error(
      getErrorMessage(
        error,
        'The student could not be updated. Please try again.',
      ),
    )
  }
})

// STUDENT STATUS
function getStatusActionTarget(
  action: 'suspend' | 'reactivate' | 'mark-inactive',
) {
  if (action === 'suspend') return 'suspended'
  if (action === 'mark-inactive') return 'inactive'
  return 'active'
}

async function updateStudentStatus(
  student: AdminStudent,
  action: 'suspend' | 'reactivate' | 'mark-inactive',
) {
  const nextStatus = getStatusActionTarget(action)

  try {
    await updateMutation.mutateAsync({
      studentId: student.id,
      payload: {
        status: nextStatus,
      },
    })

    const successMessage =
      nextStatus === 'suspended'
        ? `${student.email} has been suspended successfully.`
        : nextStatus === 'inactive'
          ? `${student.email} has been marked as inactive successfully.`
          : `${student.email} has been reactivated successfully.`

    closeEditModal()
    await refreshStudentQueries()
    toast.success(successMessage)
  } catch (error) {
    const fallback =
      nextStatus === 'suspended'
        ? 'The student could not be suspended. Please try again.'
        : nextStatus === 'inactive'
          ? 'The student could not be marked inactive. Please try again.'
          : 'The student could not be reactivated. Please try again.'

    toast.error(getErrorMessage(error, fallback))
  }
}

function getStatusConfirmationMessage(student: AdminStudent) {
  if (statusAction.value === 'suspend') {
    return `Are you sure you want to suspend ${student.email}?`
  }

  if (statusAction.value === 'mark-inactive') {
    return `Are you sure you want to mark ${student.email} as inactive? This student will no longer be able to log in.`
  }

  return `Are you sure you want to reactivate ${student.email}?`
}

function openStatusConfirmation(
  student: AdminStudent,
  action: 'suspend' | 'reactivate' | 'mark-inactive',
) {
  selectedStudentId.value = student.id
  statusAction.value = action
  statusConfirmOpen.value = true
}

async function confirmStatusAction() {
  const student = selectedStudent.value
  const action = statusAction.value

  if (!student || !action) return

  statusConfirmOpen.value = false
  await updateStudentStatus(student, action)
  statusAction.value = null
}

// DELETE STUDENT
async function handleDelete(student: AdminStudent) {
  if (!deleteConfirmOpen.value) {
    deleteConfirmOpen.value = true
    return
  }

  try {
    await deleteMutation.mutateAsync(student.id)

    deleteConfirmOpen.value = false
    closeEditModal()

    await refreshStudentQueries()

    toast.success(`${student.email} has been deleted successfully.`)
  } catch (error) {
    toast.error(
      getErrorMessage(
        error,
        'The student could not be deleted. Please try again.',
      ),
    )
  }
}

// STATUS FILTER OPTIONS
const statusOptions = [
  { value: 'all', label: 'All statuses' },
  { value: 'active', label: 'Active' },
  { value: 'suspended', label: 'Suspended' },
  { value: 'inactive', label: 'Inactive' },
]
</script>

<template>
  <section class="students-page" aria-labelledby="admin-students-title">
    <header class="page-header">
      <div>
        <p class="eyebrow">ADMINISTRATION</p>
        <h1 id="admin-students-title">Student management</h1>
      </div>
    </header>

    <!-- SEARCH AND FILTER -->
    <div class="toolbar" aria-label="Student filters">
      <label class="search-box">
        <Search :size="16" aria-hidden="true" />

        <input
          v-model="searchQuery"
          type="search"
          placeholder="Search by name, email or roll no"
          aria-label="Search students by name, email or roll number"
        />

        <button
          v-if="searchQuery"
          type="button"
          class="clear-btn"
          aria-label="Clear search"
          @click="searchQuery = ''"
        >
          Clear
        </button>
      </label>

      <label class="status-filter">
        <span>Status</span>

        <select v-model="statusFilter" aria-label="Filter students by account status">
          <option
            v-for="option in statusOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
      </label>
    </div>

    <!-- LOADING -->
    <div v-if="isLoading" class="empty-state-wrap">
      <BaseEmptyState
        title="Loading students"
        description="Fetching active records from the admin API."
      >
        <template #icon>
          <UserCog :size="42" />
        </template>
      </BaseEmptyState>
    </div>

    <!-- ERROR -->
    <div v-else-if="isError" class="empty-state-wrap">
      <BaseEmptyState
        title="Unable to load students"
        description="The admin student list is temporarily unavailable. Please refresh and try again."
      >
        <template #icon>
          <ShieldX :size="42" />
        </template>
      </BaseEmptyState>
    </div>

    <!-- EMPTY -->
    <div v-else-if="!filteredStudents.length" class="empty-state-wrap">
      <BaseEmptyState
        title="No matching students"
        description="Try a different search term or change the status filter."
      >
        <template #icon>
          <Search :size="42" />
        </template>
      </BaseEmptyState>
    </div>

    <!-- STUDENT TABLE -->
    <div v-else class="student-list-wrap" aria-label="Student list">
      <div class="student-table-wrap">
        <table class="student-table">
          <thead>
            <tr>
              <th>Roll No</th>
              <th>Name</th>
              <th>Email</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="student in filteredStudents"
              :key="student.id"
              :class="{ active: selectedStudentId === student.id }"
            >
              <td>{{ student.student_number ?? '—' }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.email }}</td>

              <td>
                <span class="status-pill" :class="`status-${student.status}`">
                  {{ student.status }}
                </span>
              </td>

              <td>
                <button
                  type="button"
                  class="row-action"
                  @click="openEditModal(student)"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- CREATE STUDENT MODAL -->
    <Teleport to="body">
      <div
        v-if="showCreateStudentForm"
        class="fixed inset-0 z-100 flex items-center justify-center bg-black/50 p-4"
        @click.self="cancelCreateStudent"
      >
        <div
          class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl"
          role="dialog"
          aria-modal="true"
          aria-labelledby="create-student-modal-title"
        >
          <header class="mb-6 flex items-start justify-between border-b border-gray-200 pb-4">
            <div>
              <p class="eyebrow">NEW STUDENT</p>
              <h2 id="create-student-modal-title">Create Student</h2>
              <p class="section-description">
                Enter the new student's information.
              </p>
            </div>

            <button
              type="button"
              class="rounded-lg px-3 py-1 text-2xl text-gray-500 hover:bg-gray-100"
              aria-label="Close create student modal"
              @click="cancelCreateStudent"
            >
              &times;
            </button>
          </header>

          <CreateStudentForm
            :submitting="false"
            @submit="handleCreateStudent"
          />
        </div>
      </div>
    </Teleport>

    <!-- ENROLLMENT MODAL -->
    <Teleport to="body">
      <div
        v-if="showEnrollmentForm && enrollmentStudent"
        class="fixed inset-0 z-110 flex items-center justify-center bg-black/50 p-4"
        @click.self="cancelEnrollment"
      >
        <div
          class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl"
          role="dialog"
          aria-modal="true"
          aria-labelledby="student-enrollment-modal-title"
        >
          <header class="mb-6 flex items-start justify-between border-b border-gray-200 pb-4">
            <div>
              <p class="eyebrow">STUDENT REGISTRATION</p>
              <h2 id="student-enrollment-modal-title">Student Enrollment</h2>
              <p class="section-description">
                Complete the academic information for {{ enrollmentStudent.name }}.
              </p>
            </div>

            <button
              type="button"
              class="rounded-lg px-3 py-1 text-2xl text-gray-500 hover:bg-gray-100"
              aria-label="Close enrollment modal"
              @click="cancelEnrollment"
            >
              &times;
            </button>
          </header>

          <StudentEnrollmentForm
            v-model="enrollmentFormData"
            :student="enrollmentStudent"
            :loading="createStudentWithEnrollmentMutation.isPending.value"
            @submit="handleCreateEnrollment"
            @cancel="cancelEnrollment"
          />
        </div>
      </div>
    </Teleport>

    <!-- EDIT STUDENT DIALOG -->
    <dialog
      ref="editDialog"
      class="edit-dialog"
      aria-labelledby="student-edit-title"
      @click="handleDialogClick"
      @close="handleEditDialogClose"
    >
      <div v-if="selectedStudent" class="edit-dialog__card">
        <header class="dialog-header">
          <div>
            <p class="eyebrow">Account overview</p>
            <h2 id="student-edit-title">{{ selectedStudent.name }}</h2>
          </div>

          <BaseButton
            variant="ghost"
            size="sm"
            aria-label="Close student editor"
            @click="closeEditModal"
          >
            Close
          </BaseButton>
        </header>

        <div class="detail-meta">
          <p><strong>ID:</strong> {{ selectedStudent.id }}</p>
          <p><strong>Role:</strong> {{ selectedStudent.role }}</p>
          <p>
            <strong>Member since:</strong>
            {{ new Date(selectedStudent.created_at).toLocaleDateString() }}
          </p>
        </div>

        <!-- EDIT FIELDS -->
        <div class="detail-fields">
          <label>
            <span>Name</span>
            <input
              v-model="name"
              v-bind="nameAttrs"
              type="text"
              :aria-invalid="Boolean(errors.name)"
            />
            <small v-if="errors.name" class="field-error" role="alert">
              {{ errors.name }}
            </small>
          </label>

          <label>
            <span>Email</span>
            <input
              v-model="email"
              v-bind="emailAttrs"
              type="email"
              :aria-invalid="Boolean(errors.email)"
            />
            <small v-if="errors.email" class="field-error" role="alert">
              {{ errors.email }}
            </small>
          </label>

          <label>
            <span>Roll No</span>
            <input
              :value="selectedStudent.student_number ?? '—'"
              type="text"
              readonly
            />
          </label>

          <label>
            <span>Status</span>
            <input
              :value="selectedStudent.status"
              type="text"
              readonly
            />
          </label>
        </div>

        <!-- ACTIONS -->
        <div class="actions">
          <BaseButton
            type="button"
            :disabled="updateMutation.isPending.value || deleteMutation.isPending.value"
            @click="saveStudentChanges"
          >
            {{ updateMutation.isPending.value ? 'Saving...' : 'Save changes' }}
          </BaseButton>

          <template v-if="selectedStudent.status === 'active'">
            <BaseButton
              type="button"
              variant="outline"
              :disabled="updateMutation.isPending.value || deleteMutation.isPending.value"
              @click="openStatusConfirmation(selectedStudent, 'suspend')"
            >
              Suspend
            </BaseButton>

            <BaseButton
              type="button"
              variant="outline"
              :disabled="updateMutation.isPending.value || deleteMutation.isPending.value"
              @click="openStatusConfirmation(selectedStudent, 'mark-inactive')"
            >
              Mark Inactive
            </BaseButton>
          </template>

          <template v-else>
            <BaseButton
              type="button"
              variant="outline"
              :disabled="updateMutation.isPending.value || deleteMutation.isPending.value"
              @click="openStatusConfirmation(selectedStudent, 'reactivate')"
            >
              Reactivate
            </BaseButton>
          </template>

          <BaseButton
            type="button"
            variant="danger"
            :disabled="deleteMutation.isPending.value || updateMutation.isPending.value"
            @click="handleDelete(selectedStudent)"
          >
            <Trash2 :size="16" aria-hidden="true" />
            Delete
          </BaseButton>
        </div>

        <!-- DELETE CONFIRMATION -->
        <ConfirmDialog
          :open="deleteConfirmOpen"
          title="Delete student"
          :message="`Are you sure you want to delete ${selectedStudent.email}? This action cannot be undone.`"
          confirm-label="Delete"
          confirm-variant="danger"
          :busy="deleteMutation.isPending.value"
          @confirm="handleDelete(selectedStudent)"
          @cancel="deleteConfirmOpen = false"
        />

        <!-- STATUS CONFIRMATION -->
        <ConfirmDialog
          :open="statusConfirmOpen"
          title="Confirm status change"
          :message="getStatusConfirmationMessage(selectedStudent)"
          confirm-label="Confirm"
          confirm-variant="outline"
          :busy="updateMutation.isPending.value"
          @confirm="confirmStatusAction"
          @cancel="clearConfirmationState"
        />
      </div>
    </dialog>
  </section>
</template>

<style scoped>
.students-page {
  color: var(--text);
}

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

h1,
h2 {
  margin: 0;
}

h1 {
  font-size: clamp(28px, 2.7vw, 36px);
}

.section-description {
  margin: 8px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.toolbar {
  display: flex;
  align-items: end;
  gap: 14px;
  margin-bottom: 18px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-height: 44px;
  padding: 0 12px;
  border: 1px solid #e2ddef;
  border-radius: 12px;
  background: #fff;
  color: #8233f4;
  transition: all 0.2s ease;
}

.search-box:focus-within {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px rgba(130, 51, 244, 0.1);
}

.search-box input {
  flex: 1;
  min-width: 0;
  border: 0;
  outline: 0;
  background: transparent;
  color: #302744;
  font-size: 13px;
}

.search-box input::placeholder {
  color: #aaa3b8;
}

.clear-btn {
  padding: 6px 12px;
  border: 1px solid #e2ddef;
  border-radius: 8px;
  background: #fff;
  color: #6b5b85;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.clear-btn:hover {
  border-color: #8233f4;
  background: #f7f2ff;
  color: #8233f4;
}

.clear-btn:focus-visible {
  outline: 2px solid #8233f4;
  outline-offset: 2px;
}

.status-filter {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 44px;
  padding: 0 12px;
  border: 1px solid #e0dbf3;
  border-radius: 12px;
  background: #fff;
  color: var(--muted);
}

.status-filter select {
  border: 0;
  background: transparent;
  color: var(--text);
  font: inherit;
  outline: none;
}

.student-list-wrap {
  width: 100%;
}

.student-table-wrap {
  overflow-x: auto;
  border: 1px solid #e5e0fb;
  border-radius: 16px;
  background: #fff;
}

.student-table {
  width: 100%;
  min-width: 760px;
  border-collapse: collapse;
  color: var(--text);
}

.student-table th,
.student-table td {
  padding: 16px 14px;
  border-bottom: 1px solid #f0ebff;
  text-align: left;
  vertical-align: middle;
  font-size: 13px;
}

.student-table th {
  background: #faf8ff;
  color: var(--muted);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.student-table td:nth-child(3) {
  min-width: 260px;
}

.student-table tbody tr {
  transition: background 0.2s ease;
}

.student-table tbody tr:hover {
  background: #faf7ff;
}

.student-table tbody tr.active {
  background: #f6f0ff;
}

.row-action {
  padding: 8px 12px;
  border: 1px solid #ddd6f7;
  border-radius: 9px;
  background: #fff;
  color: var(--text);
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 82px;
  padding: 7px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: capitalize;
}

.status-active {
  background: #daf8ea;
  color: #0b7b58;
}

.status-suspended {
  background: #ffe7d7;
  color: #b05d0b;
}

.status-inactive {
  background: #eceaf9;
  color: #4c4b66;
}

.edit-dialog {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: min(680px, calc(100vw - 24px));
  max-width: calc(100vw - 24px);
  max-height: calc(100vh - 24px);
  margin: auto;
  padding: 0;
  border: 0;
  border-radius: 18px;
  background: transparent;
  overflow: auto;
}

.edit-dialog::backdrop {
  background: rgba(31, 20, 52, 0.48);
}

.edit-dialog__card {
  display: grid;
  gap: 18px;
  width: 100%;
  max-height: calc(100vh - 24px);
  padding: 22px;
  border: 1px solid #e7ddff;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 24px 50px rgba(54, 32, 96, 0.15);
  overflow-y: auto;
}

.dialog-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.detail-fields {
  display: grid;
  gap: 14px;
}

.detail-fields label {
  display: grid;
  gap: 8px;
  color: var(--muted);
  font-size: 13px;
}

.detail-fields input {
  width: 100%;
  min-height: 42px;
  padding: 0 12px;
  border: 1px solid #ddd6f7;
  border-radius: 10px;
  background: #fff;
  color: var(--text);
}

.detail-fields input[readonly] {
  background: #f7f5ff;
}

.detail-meta {
  display: grid;
  gap: 8px;
  color: var(--muted);
}

.detail-meta p {
  margin: 0;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.field-error {
  color: var(--danger);
  font-size: 11px;
  font-weight: 700;
}

.empty-state-wrap {
  margin-top: 20px;
}

@media (max-width: 760px) {
  .page-header {
    align-items: flex-start;
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .actions {
    flex-direction: column;
  }

  .edit-dialog__card {
    padding: 18px 14px;
  }
}
</style>
