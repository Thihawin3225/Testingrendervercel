<script setup lang="ts">
import { computed, ref } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import {
  ArrowRight,
  BookOpen,
  GraduationCap,
  Plus,
  ShieldCheck,
  UserRoundCheck,
  UserRoundX,
} from '@lucide/vue'
import { RouterLink } from 'vue-router'

import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import { useToast } from '@/composables/useToast'

import {
  fetchAllStudents,
  createStudentWithEnrollment,
} from '@/features/admin/api/adminStudents.api'

import type {
  CreateStudentPayload,
  CreateStudentWithEnrollmentPayload,
} from '@/features/admin/api/adminStudents.api'

import CreateStudentForm from '@/features/admin/components/CreateStudentForm.vue'
import StudentEnrollmentForm from '@/features/academic/components/student-enrollments/StudentEnrollmentForm.vue'

import type { StudentEnrollmentFormData } from '@/features/academic/interfaces/studentEnrollment.interface'

// ==========================================
// STATE
// ==========================================

const queryClient = useQueryClient()
const toast = useToast()

const createDialog = ref<HTMLDialogElement | null>(null)

// Form visibility
const showCreateStudentForm = ref(false)
const showEnrollmentForm = ref(false)

// Used to reset CreateStudentForm when starting a new workflow
const formKey = ref(0)

// Temporary student data
const pendingStudentPayload = ref<CreateStudentPayload | null>(null)

const enrollmentStudent = ref<{
  name: string
  email: string
} | null>(null)

// Enrollment form data
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

// ==========================================
// STUDENT + ENROLLMENT MUTATION
// ==========================================

const createStudentWithEnrollmentMutation = useMutation({
  mutationFn: (payload: CreateStudentWithEnrollmentPayload) =>
    createStudentWithEnrollment(payload),

  onSuccess: async (response) => {
    await Promise.all([
      queryClient.invalidateQueries({
        queryKey: ['admin-students'],
      }),
      queryClient.invalidateQueries({
        queryKey: ['admin-dashboard-students'],
      }),
    ])

    toast.success(response.message)

    closeEnrollmentDialog()
  },
})

// ==========================================
// STUDENT QUERY
// ==========================================

const {
  data,
  isLoading,
  isError,
} = useQuery({
  queryKey: ['admin-dashboard-students'],
  queryFn: fetchAllStudents,
})

const students = computed(() => data.value ?? [])

const activeStudents = computed(() =>
  students.value.filter((student) => student.status === 'active').length,
)

const suspendedStudents = computed(() =>
  students.value.filter((student) => student.status === 'suspended').length,
)

const totalStudents = computed(() => students.value.length)

const quickActions = [
  {
    title: 'Manage students',
    description: 'Review accounts, filter by status and keep records current.',
    to: { name: 'admin-students' },
  },
  {
    title: 'Academic setup',
    description: 'Open academic sections for programs, year levels and offerings.',
    to: { name: 'admin-academics' },
  },
]

// ==========================================
// DIALOG
// ==========================================

function openCreateDialog(): void {
  resetWorkflow()

  // Show Create Student Form first
  showCreateStudentForm.value = true

  createDialog.value?.showModal()
}

function closeCreateDialog(): void {
  createDialog.value?.close()
}

function handleDialogClose(): void {
  resetWorkflow()
}

// ==========================================
// RESET WORKFLOW
// ==========================================

function resetWorkflow(): void {
  // Reset Create Student Form
  formKey.value += 1

  // Clear temporary student information
  pendingStudentPayload.value = null

  enrollmentStudent.value = null

  // Hide both forms
  showCreateStudentForm.value = false
  showEnrollmentForm.value = false

  // Reset enrollment form
  enrollmentFormData.value = emptyEnrollmentForm()

  // Reset mutation state
  createStudentWithEnrollmentMutation.reset()
}

// ==========================================
// STEP 1: CREATE STUDENT FORM SUBMIT
// ==========================================

function handleCreateStudent(payload: CreateStudentPayload): void {
  // Keep student information temporarily.
  // Do not save to database at this step.

  pendingStudentPayload.value = payload

  enrollmentStudent.value = {
    name: payload.name,
    email: payload.email,
  }

  // Reset enrollment fields
  enrollmentFormData.value = emptyEnrollmentForm()

  // Hide Create Student Form
  showCreateStudentForm.value = false

  // Show Enrollment Form
  showEnrollmentForm.value = true
}

// ==========================================
// STEP 2: ADD ENROLLMENT
// ==========================================

async function handleCreateEnrollment(): Promise<void> {
  const student = pendingStudentPayload.value

  if (!student) {
    toast.error('Student information is missing.')
    return
  }

  if (
    !enrollmentFormData.value.academic_year_id ||
    !enrollmentFormData.value.year_level_id ||
    !enrollmentFormData.value.major_id ||
    !enrollmentFormData.value.semester_id
  ) {
    toast.error('Please complete all enrollment fields.')
    return
  }

  const payload: CreateStudentWithEnrollmentPayload = {
    ...student,
    academic_year_id: enrollmentFormData.value.academic_year_id,
    year_level_id: enrollmentFormData.value.year_level_id,
    major_id: enrollmentFormData.value.major_id,
    semester_id: enrollmentFormData.value.semester_id,
    status: enrollmentFormData.value.status,
  }

  try {
    await createStudentWithEnrollmentMutation.mutateAsync(payload)
  } catch (error) {
    console.error('Student enrollment creation failed:', error)
  }
}

// ==========================================
// CANCEL ENROLLMENT
// ==========================================

function cancelEnrollment(): void {
  // Hide Enrollment Form
  showEnrollmentForm.value = false

  // Show Create Student Form again
  showCreateStudentForm.value = true

  // IMPORTANT:
  // Do not clear pendingStudentPayload.
  // Do not reset CreateStudentForm.
  // The previous student information must remain.
}

// ==========================================
// CLOSE AFTER SUCCESS
// ==========================================

function closeEnrollmentDialog(): void {
  resetWorkflow()
  closeCreateDialog()
}

// ==========================================
// ERROR MESSAGE
// ==========================================

const createErrorMessage = computed(() => {
  const error = createStudentWithEnrollmentMutation.error.value

  if (!error) return ''

  if (
    typeof error === 'object' &&
    'response' in error &&
    error.response &&
    typeof error.response === 'object' &&
    'data' in error.response &&
    error.response.data &&
    typeof error.response.data === 'object' &&
    'message' in error.response.data &&
    typeof error.response.data.message === 'string'
  ) {
    return error.response.data.message
  }

  return 'The student and enrollment could not be created. Please try again.'
})
</script>

<template>
  <section class="dashboard" aria-labelledby="admin-dashboard-title">
    <header class="hero">
      <div class="hero-copy">
        <div class="eyebrow">UCS PYAY · ADMIN PORTAL</div>

        <h1 id="admin-dashboard-title">
          Welcome back, Admin.
        </h1>

        <p>
          Monitor student activity, maintain account health, and manage academic
          operations from one place.
        </p>
      </div>

      <ShieldCheck
        class="hero-art"
        :size="72"
        stroke-width="1.7"
        aria-hidden="true"
      />
    </header>

    <!-- STUDENT STATISTICS -->
    <section class="stats" aria-label="Student statistics">
      <BaseCard v-if="isLoading" as="article" class="stat stat--loading">
        <div class="placeholder-line short" />
        <div class="placeholder-line" />
      </BaseCard>

      <BaseCard v-else-if="isError" as="article" class="stat stat--error">
        <p>Student statistics could not be loaded.</p>
      </BaseCard>

      <template v-else>
        <RouterLink
          class="stat-link"
          :to="{ name: 'admin-students', query: { status: 'all' } }"
        >
          <BaseCard as="article" class="stat">
            <span class="stat-icon stat-icon--purple">
              <GraduationCap :size="22" aria-hidden="true" />
            </span>

            <div>
              <strong>{{ totalStudents }}</strong>
              <span class="small">Total students</span>
            </div>
          </BaseCard>
        </RouterLink>

        <RouterLink
          class="stat-link"
          :to="{ name: 'admin-students', query: { status: 'active' } }"
        >
          <BaseCard as="article" class="stat">
            <span class="stat-icon stat-icon--green">
              <UserRoundCheck :size="22" aria-hidden="true" />
            </span>

            <div>
              <strong>{{ activeStudents }}</strong>
              <span class="small">Active accounts</span>
            </div>
          </BaseCard>
        </RouterLink>

        <RouterLink
          class="stat-link"
          :to="{ name: 'admin-students', query: { status: 'suspended' } }"
        >
          <BaseCard as="article" class="stat">
            <span class="stat-icon stat-icon--amber">
              <UserRoundX :size="22" aria-hidden="true" />
            </span>

            <div>
              <strong>{{ suspendedStudents }}</strong>
              <span class="small">Suspended students</span>
            </div>
          </BaseCard>
        </RouterLink>

        <RouterLink
          class="stat-link"
          :to="{ name: 'admin-students', query: { status: 'inactive' } }"
        >
          <BaseCard as="article" class="stat">
            <span class="stat-icon stat-icon--lavender">
              <BookOpen :size="22" aria-hidden="true" />
            </span>

            <div>
              <strong>
                {{ Math.max(totalStudents - activeStudents - suspendedStudents, 0) }}
              </strong>
              <span class="small">Inactive / pending</span>
            </div>
          </BaseCard>
        </RouterLink>
      </template>
    </section>

    <!-- QUICK ACTIONS -->
    <div class="bar">
      <div>
        <h2>Quick actions</h2>
        <span class="fine">Student and academic management</span>
      </div>

      <BaseButton type="button" @click="openCreateDialog">
        <Plus :size="17" aria-hidden="true" />
        Create Student
      </BaseButton>
    </div>

    <section class="quick-actions" aria-label="Quick actions">
      <RouterLink
        v-for="action in quickActions"
        :key="action.title"
        class="quick-action"
        :to="action.to"
      >
        <div class="quick-action__icon" aria-hidden="true">
          <ArrowRight :size="20" />
        </div>

        <div>
          <strong>{{ action.title }}</strong>
          <small>{{ action.description }}</small>
        </div>
      </RouterLink>
    </section>

    <!-- CREATE STUDENT / ENROLLMENT DIALOG -->
    <dialog
      ref="createDialog"
      class="create-dialog"
      aria-labelledby="create-student-title"
      @cancel.prevent
      @close="handleDialogClose"
    >
      <BaseCard as="section" padded class="dialog-card">

        <!-- STEP 1: CREATE STUDENT FORM -->
        <div v-show="showCreateStudentForm">
          <header class="dialog-heading">
            <div>
              <h2 id="create-student-title">Create Student</h2>
              <p>Enter student account information.</p>
            </div>

            <BaseButton
              variant="ghost"
              size="sm"
              aria-label="Close form"
              @click="closeCreateDialog"
            >
              Close
            </BaseButton>
          </header>

          <CreateStudentForm
            :key="formKey"
            :submitting="false"
            @submit="handleCreateStudent"
          />
        </div>

        <!-- STEP 2: STUDENT ENROLLMENT FORM -->
        <div v-show="showEnrollmentForm">
          <header class="dialog-heading">
            <div>
              <h2 id="enrollment-title">Student Enrollment</h2>
              <p>Complete academic information to create the student account.</p>
            </div>
          </header>

          <p
            v-if="createStudentWithEnrollmentMutation.isError.value"
            class="error-message"
            role="alert"
          >
            {{ createErrorMessage }}
          </p>

          <StudentEnrollmentForm
            v-model="enrollmentFormData"
            :student="enrollmentStudent"
            :defer-student-creation="true"
            :loading="createStudentWithEnrollmentMutation.isPending.value"
            @submit="handleCreateEnrollment"
            @cancel="cancelEnrollment"
          />
        </div>

      </BaseCard>
    </dialog>
  </section>
</template>

<style scoped>
.dashboard {
  color: var(--text);
}

.hero {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  min-height: 150px;
  margin-bottom: 22px;
  padding: 28px 33px;
  overflow: hidden;
  border: 1px solid #e2d9fc;
  border-radius: 19px;
  background: linear-gradient(106deg, #f2ecff, #ece1ff 57%, #d1cdfc);
}

.hero::after {
  position: absolute;
  right: -130px;
  bottom: -240px;
  width: 410px;
  height: 310px;
  border-radius: 50%;
  background: #fff6;
  content: '';
}

.hero-copy {
  position: relative;
  z-index: 1;
}

.eyebrow {
  color: #6a43cc;
  font-size: 10px;
  font-weight: 850;
  letter-spacing: 0.17em;
  text-transform: uppercase;
}

.hero h1 {
  margin: 8px 0 0;
  font-size: clamp(28px, 3vw, 38px);
}

.hero p {
  margin: 10px 0 0;
  color: #545475;
  font-size: 14px;
}

.hero-art {
  z-index: 1;
  flex: none;
  color: #6c2dea;
  filter: drop-shadow(0 9px 13px #6e42c540);
}

.stats {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 15px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 13px;
  min-height: 118px;
  padding: 19px;
}

.stat-link {
  display: block;
  min-width: 0;
  color: inherit;
  text-decoration: none;
  border-radius: 16px;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.stat-link:hover .stat,
.stat-link:focus-visible .stat {
  box-shadow: 0 12px 24px #e7ddff;
}

.stat-link:hover .stat {
  transform: translateY(-2px);
}

.stat-link:focus-visible {
  outline: 2px solid var(--purple);
  outline-offset: 2px;
}

.stat-link:hover,
.stat-link:focus-visible {
  text-decoration: none;
}

.stat-icon {
  display: grid;
  place-items: center;
  width: 46px;
  height: 46px;
  border-radius: 14px;
  color: white;
}

.stat-icon--purple { background: #6c2dea; }
.stat-icon--green { background: #1aa66d; }
.stat-icon--amber { background: #d8921a; }
.stat-icon--lavender { background: #8b62f5; }

.stat strong {
  display: block;
  font-size: 29px;
  line-height: 1;
}

.small {
  display: block;
  margin-top: 7px;
  color: var(--muted);
  font-size: 12px;
}

.bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 26px;
  margin-bottom: 14px;
}

.bar > div {
  display: grid;
  gap: 4px;
}

.bar h2 {
  margin: 0;
  font-size: 20px;
}

.fine {
  color: var(--muted);
  font-size: 12px;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 15px;
}

.quick-action {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 20px 18px;
  border: 1px solid #e4ddff;
  border-radius: 16px;
  background: #fff;
  color: var(--text);
  text-decoration: none;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.quick-action:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 24px #e7ddff;
}

.quick-action__icon {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #f2ebff;
  color: var(--purple);
}

.quick-action strong {
  display: block;
  margin-bottom: 4px;
  font-size: 16px;
}

.quick-action small {
  color: var(--muted);
}

.placeholder-line {
  height: 12px;
  border-radius: 999px;
  background: #eee9ff;
}

.placeholder-line.short {
  width: 44%;
  margin-bottom: 10px;
}

.stat--loading,
.stat--error {
  min-height: 118px;
}

.error-message {
  margin-bottom: 14px;
  padding: 12px 14px;
  border: 1px solid #ffc0c9;
  border-radius: 10px;
  background: #fff6f6;
  color: #b4233b;
  font-size: 13px;
}

.create-dialog {
  position: fixed;
  top: 50%;
  left: 50%;
  right: auto;
  bottom: auto;
  transform: translate(-50%, -50%);
  margin: 0;
  width: min(560px, calc(100vw - 32px));
  max-height: 90dvh;
  padding: 0;
  overflow-y: auto;
  border: none;
  border-radius: 18px;
  background: transparent;
}

.create-dialog::backdrop {
  background: rgba(33, 26, 55, 0.65);
  backdrop-filter: blur(3px);
}

.dialog-card {
  background: #fff;
}

.dialog-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 18px;
}

.dialog-heading h2 {
  margin: 0;
  font-size: 21px;
}

.dialog-heading p {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 13px;
}

@media (max-width: 760px) {
  .stats,
  .quick-actions {
    grid-template-columns: 1fr;
  }

  .hero {
    padding: 22px 20px;
  }

  .bar {
    align-items: flex-start;
  }
}
</style>
