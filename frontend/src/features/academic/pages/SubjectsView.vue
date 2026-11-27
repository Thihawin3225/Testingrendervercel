<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import {
  Plus,
  Search,
  BookOpen,
  CheckCircle,
  Layers,
} from '@lucide/vue'

import BaseBackButton from '@/components/ui/BaseBackButton.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BasePageMessage from '@/components/ui/BasePageMessage.vue'

import { usePageMessage } from '@/composables/usePageMessage'

import SubjectForm from '../components/subjects/SubjectForm.vue'
import SubjectTable from '../components/subjects/SubjectTable.vue'
import SubjectOfferingForm from '../components/subject-offerings/SubjectOfferingForm.vue'

import { useSubjects } from '../queries/subject.queries'
import { useAcademicYears } from '../queries/academicYear.queries'

import {
  useUpdateSubject,
  useDeleteSubject,
} from '../mutations/subject.mutations'

import {
  useCreateSubjectOffering,
  useCreateSubjectWithOffering,
} from '../mutations/subjectOffering.mutations'

import { getEmptySubjectForm } from '../utils/subject.utils'
import { getEmptySubjectOfferingForm } from '../utils/subjectOffering.utils'

import type {
  Subject,
  SubjectFormData,
  SubjectFilters,
  PaginationMeta,
} from '../interfaces/subject.interface'

import type {
  SubjectOfferingFormData,
} from '../interfaces/subjectOffering.interface'

// ==========================================
// GLOBAL PAGE MESSAGE
// ==========================================

const {
  showSuccess,
  showError,
  clearError,
  clearMessages,
} = usePageMessage()

// ==========================================
// STATE
// ==========================================

const search = ref('')
const currentPage = ref(1)

// Subject Modal
const showModal = ref(false)
const editingId = ref<number | null>(null)

const formData = ref<SubjectFormData>(
  getEmptySubjectForm(),
)

// Subject Offering Modal
const showOfferingModal = ref(false)

const subjectDraft = ref<SubjectFormData | null>(null)

// null means the Subject has not been created yet.
const existingSubjectId = ref<number | null>(null)

const offeringFormData = ref<SubjectOfferingFormData>(
  getEmptySubjectOfferingForm(),
)

// ==========================================
// TANSTACK QUERY
// ==========================================

const filters = computed<SubjectFilters>(() => ({
  search: search.value.trim() || undefined,
  page: currentPage.value,
  per_page: 10,
}))

const {
  data,
  isPending,
  isFetching,
  error,
} = useSubjects(filters)

const subjects = computed<Subject[]>(
  () => data.value?.data ?? [],
)

const pagination = computed<PaginationMeta>(() =>
  data.value?.meta ?? {
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
// ACADEMIC YEARS
// ==========================================

const {
  data: academicYearsData,
  isPending: academicYearsLoading,
} = useAcademicYears({ per_page: 100 })

const academicYears = computed(
  () => academicYearsData.value?.data ?? [],
)

function isActiveYear(value: unknown): boolean {
  return (
    value === true ||
    value === 1 ||
    value === '1' ||
    value === 'true'
  )
}

const activeAcademicYear = computed(
  () =>
    academicYears.value.find(
      (year) => isActiveYear(year.is_active),
    ) ?? null,
)

// Automatically select active academic year.
watch(
  activeAcademicYear,
  (year) => {
    if (
      year &&
      showOfferingModal.value &&
      !offeringFormData.value.academic_year_id
    ) {
      offeringFormData.value.academic_year_id = year.id
    }
  },
  { immediate: true },
)

// ==========================================
// QUERY ERROR
// ==========================================

watch(error, (queryError) => {
  if (queryError) {
    showError(
      queryError instanceof Error
        ? queryError.message
        : 'Unable to load subjects.',
    )
  }
})

// ==========================================
// TANSTACK MUTATIONS
// ==========================================

const updateSubject = useUpdateSubject()
const deleteSubject = useDeleteSubject()

const createSubjectOffering = useCreateSubjectOffering()
const createSubjectWithOffering = useCreateSubjectWithOffering()

const saving = computed(
  () =>
    updateSubject.isPending.value ||
    createSubjectOffering.isPending.value ||
    createSubjectWithOffering.isPending.value,
)

// ==========================================
// COMPUTED
// ==========================================

const modalTitle = computed(() =>
  editingId.value === null
    ? 'Create Subject'
    : 'Edit Subject',
)

const submitLabel = computed(() =>
  editingId.value === null
    ? 'Continue to Offering'
    : 'Update Subject',
)

const lockedSubject = computed(() => {
  if (!subjectDraft.value) return null

  return {
    subject_code: subjectDraft.value.subject_code,
    subject_name: subjectDraft.value.subject_name,
  }
})

// ==========================================
// CREATE SUBJECT MODAL
// ==========================================

function openCreateModal() {
  editingId.value = null

  formData.value = getEmptySubjectForm()

  subjectDraft.value = null
  existingSubjectId.value = null

  clearMessages()

  showModal.value = true
}

// ==========================================
// EDIT SUBJECT MODAL
// ==========================================

function openEditModal(subject: Subject) {
  editingId.value = subject.id

  formData.value = {
    subject_code: subject.subject_code,
    subject_name: subject.subject_name,
    description: subject.description ?? '',
  }

  clearMessages()

  showModal.value = true
}

// ==========================================
// CLOSE SUBJECT MODAL
// ==========================================

function closeModal() {
  if (saving.value) return

  showModal.value = false

  editingId.value = null
  formData.value = getEmptySubjectForm()
}

// ==========================================
// OPEN OFFERING MODAL FOR NEW SUBJECT
// ==========================================

function openOfferingForNewSubject(
  subject: SubjectFormData,
) {
  subjectDraft.value = {
    subject_code: subject.subject_code,
    subject_name: subject.subject_name,
    description: subject.description,
  }

  existingSubjectId.value = null

  offeringFormData.value = {
    ...getEmptySubjectOfferingForm(),
    academic_year_id: activeAcademicYear.value?.id ?? 0,
  }

  showModal.value = false
  showOfferingModal.value = true
}

// ==========================================
// CREATE / UPDATE SUBJECT
// ==========================================

async function submitForm() {
  clearError()

  if (
    !formData.value.subject_code.trim() ||
    !formData.value.subject_name.trim()
  ) {
    showError('Please complete all required fields.')
    return
  }

  const payload: SubjectFormData = {
    subject_code: formData.value.subject_code.trim(),
    subject_name: formData.value.subject_name.trim(),
    description: formData.value.description.trim(),
  }

  try {
    if (editingId.value !== null) {
      await updateSubject.mutateAsync({
        id: editingId.value,
        data: payload,
      })

      showSuccess('Subject updated successfully.')

      showModal.value = false
      editingId.value = null
      formData.value = getEmptySubjectForm()

      currentPage.value = 1

      return
    }

    // Keep the new Subject temporarily.
    // The backend creates it together with the first offering.
    openOfferingForNewSubject(payload)

  } catch (error: unknown) {
    console.error('Failed to submit subject:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to save subject. Please try again.',
    )
  }
}

// ==========================================
// OPEN OFFERING FOR EXISTING SUBJECT
// ==========================================

function handleAddOffering(subject: Subject) {
  clearMessages()

  subjectDraft.value = {
    subject_code: subject.subject_code,
    subject_name: subject.subject_name,
    description: subject.description ?? '',
  }

  existingSubjectId.value = subject.id

  offeringFormData.value = {
    ...getEmptySubjectOfferingForm(),
    academic_year_id: activeAcademicYear.value?.id ?? 0,
    subject_id: subject.id,
  }

  showOfferingModal.value = true
}

// ==========================================
// RESET OFFERING FIELDS FOR ANOTHER OFFERING
// ==========================================

function prepareAnotherOffering() {
  offeringFormData.value = {
    ...getEmptySubjectOfferingForm(),

    // Preserve the selected Academic Year.
    academic_year_id:
      offeringFormData.value.academic_year_id,

    // Reuse the same Subject.
    subject_id: existingSubjectId.value ?? 0,
  }

  clearError()
}

// ==========================================
// FINISH OFFERING WORKFLOW
// ==========================================

function finishOfferingWorkflow() {
  showOfferingModal.value = false
  showModal.value = false

  subjectDraft.value = null
  existingSubjectId.value = null

  formData.value = getEmptySubjectForm()
  offeringFormData.value = getEmptySubjectOfferingForm()

  editingId.value = null
  currentPage.value = 1
}

// ==========================================
// SUBMIT SUBJECT + OFFERING
// ==========================================

async function submitOfferingForm() {
  clearError()

  if (
    existingSubjectId.value === null &&
    !subjectDraft.value
  ) {
    showError('Subject information is missing.')
    return
  }

  const {
    academic_year_id,
    year_level_id,
    major_id,
    semester_id,
  } = offeringFormData.value

  if (
    !academic_year_id ||
    !year_level_id ||
    !major_id ||
    !semester_id
  ) {
    showError('Please complete all required offering fields.')
    return
  }

  const offeringData = {
    academic_year_id,
    year_level_id,
    major_id,
    semester_id,
  }

  try {
    // CASE 1: CREATE NEW SUBJECT + FIRST OFFERING
    if (existingSubjectId.value === null) {
      if (!subjectDraft.value) return

      const createdOffering =
        await createSubjectWithOffering.mutateAsync({
          subject: {
            subject_code: subjectDraft.value.subject_code,
            subject_name: subjectDraft.value.subject_name,
            description: subjectDraft.value.description,
          },
          offering: offeringData,
        })

      // IMPORTANT:
      // Save the created Subject ID.
      // All additional offerings will reuse this ID.
      existingSubjectId.value = createdOffering.subject.id

      showSuccess(
        'Subject and first offering created successfully.',
      )
    }

    // CASE 2: CREATE ANOTHER OFFERING FOR THE SAME SUBJECT
    else {
      await createSubjectOffering.mutateAsync({
        ...offeringData,
        subject_id: existingSubjectId.value,
      })

      showSuccess('Subject offering created successfully.')
    }

    // Ask after every successful offering.
    const createAnother = window.confirm(
      'Subject Offering Created Successfully!\n\n' +
      'The subject has been successfully added. ' +
      'Would you like to offer this subject to another major?',
    )

    if (createAnother) {
      // Keep Subject ID and Subject details.
      // Reset Year Level, Major and Semester.
      prepareAnotherOffering()

      // Keep the Offering Modal open.
      showOfferingModal.value = true

      return
    }

    // User selected No.
    finishOfferingWorkflow()

  } catch (error: unknown) {
    console.error('Failed to create subject offering:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to create subject and offering. Please try again.',
    )
  }
}

// ==========================================
// CANCEL OFFERING MODAL
// ==========================================

function cancelOffering() {
  if (saving.value) return

  showOfferingModal.value = false

  // If this is a new Subject that has not been saved,
  // return to Subject Form with previous data.
  if (
    existingSubjectId.value === null &&
    subjectDraft.value
  ) {
    formData.value = {
      subject_code: subjectDraft.value.subject_code,
      subject_name: subjectDraft.value.subject_name,
      description: subjectDraft.value.description,
    }

    showModal.value = true

    return
  }

  // Existing Subject or already-created Subject.
  subjectDraft.value = null
  existingSubjectId.value = null

  offeringFormData.value = getEmptySubjectOfferingForm()
}

// ==========================================
// DELETE SUBJECT
// ==========================================

async function handleDelete(subject: Subject) {
  const confirmed = window.confirm(
    `Are you sure you want to delete "${subject.subject_name}"?`,
  )

  if (!confirmed) return

  clearMessages()

  const wasLastItem =
    subjects.value.length === 1 &&
    currentPage.value > 1

  try {
    await deleteSubject.mutateAsync(subject.id)

    showSuccess('Subject deleted successfully.')

    if (wasLastItem) {
      currentPage.value -= 1
    }
  } catch (error: unknown) {
    console.error('Failed to delete subject:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to delete subject.',
    )
  }
}

// ==========================================
// SEARCH
// ==========================================

function handleSearch() {
  clearError()
  currentPage.value = 1
}

// ==========================================
// PAGINATION
// ==========================================

function handlePageChange(page: number) {
  currentPage.value = page
}
</script>

<template>
  <section class="academic-years-page space-y-6">

    <!-- PAGE HEADING -->
    <header class="page-header">
      <div>
        <p class="eyebrow">ACADEMIC OPERATIONS</p>
        <h1>Subjects</h1>
      </div>

      <BaseBackButton to="/admin/academics" />
    </header>

    <!-- GLOBAL PAGE MESSAGE -->
    <BasePageMessage />

    <!-- STATISTICS -->
    <section class="stats-grid">

      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Total Subjects</p>
            <h2>{{ pagination.total }}</h2>
          </div>

          <div class="stat-icon total-icon">
            <BookOpen :size="23" />
          </div>
        </div>
      </BaseCard>

      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Displayed (Current Page)</p>
            <h2 class="active-number">
              {{ subjects.length }}
            </h2>
          </div>

          <div class="stat-icon active-icon">
            <CheckCircle :size="23" />
          </div>
        </div>
      </BaseCard>

      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Subjects Per Page</p>
            <h2 class="inactive-number">
              {{ pagination.per_page }}
            </h2>
          </div>

          <div class="stat-icon inactive-icon">
            <Layers :size="23" />
          </div>
        </div>
      </BaseCard>

    </section>

    <!-- SUBJECT LIST -->
    <BaseCard padded class="academic-list-card">

      <!-- SECTION HEADER -->
      <div class="list-header">
        <div class="list-heading">
          <h2>Subject List</h2>
          <p>View and manage all subjects.</p>
        </div>

        <BaseButton
          type="button"
          class="add-academic-button"
          @click="openCreateModal"
        >
          <Plus :size="18" />
          Add Subject
        </BaseButton>
      </div>

      <!-- SEARCH -->
      <form
        class="search-form"
        @submit.prevent="handleSearch"
      >
        <div class="search-input-wrapper">
          <Search :size="17" />

          <input
            v-model="search"
            type="search"
            placeholder="Search subject..."
          />
        </div>

        <BaseButton
          type="submit"
          variant="outline"
          class="search-button"
        >
          Search
        </BaseButton>
      </form>

      <SubjectTable
        :subjects="subjects"
        :loading="loading"
        :pagination="pagination"
        @edit="openEditModal"
        @delete="handleDelete"
        @page="handlePageChange"
        @add-offering="handleAddOffering"
      />

    </BaseCard>

    <!-- CREATE / EDIT SUBJECT MODAL -->
    <BaseModal
      v-model="showModal"
      :title="modalTitle"
      @close="closeModal"
    >
      <SubjectForm
        v-model="formData"
        :loading="saving"
        :submit-label="submitLabel"
        @submit="submitForm"
        @cancel="closeModal"
      />
    </BaseModal>

    <!-- SUBJECT OFFERING MODAL -->
    <BaseModal
      v-model="showOfferingModal"
      title="Create Subject Offering"
      @close="cancelOffering"
    >
      <SubjectOfferingForm
        v-model="offeringFormData"
        :loading="saving || academicYearsLoading"
        :locked-subject="lockedSubject"
        :submit-label="
          existingSubjectId === null
            ? 'Create Subject and Offering'
            : 'Create Subject Offering'
        "
        @submit="submitOfferingForm"
        @cancel="cancelOffering"
      />
    </BaseModal>

  </section>
</template>

<style scoped>
.academic-years-page {
  width: 100%;
  min-width: 0;
  color: #302744;
}

/* PAGE HEADER */

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

/* STATISTICS */

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.stat-card {
  min-width: 0;
}

.stat-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.stat-content p {
  margin: 0;
  color: #8a849b;
  font-size: 13px;
}

.stat-content h2 {
  margin: 9px 0 0;
  color: #302744;
  font-size: 26px;
  font-weight: 750;
}

.stat-content .active-number {
  color: #16845b;
}

.stat-content .inactive-number {
  color: #817993;
}

.stat-icon {
  display: grid;
  width: 46px;
  height: 46px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 12px;
}

.total-icon {
  background: #f2ecff;
  color: #8233f4;
}

.active-icon {
  background: #e5f8ef;
  color: #16845b;
}

.inactive-icon {
  background: #f1eff5;
  color: #817993;
}

/* ACADEMIC LIST CARD */

.academic-list-card {
  width: 100%;
  min-width: 0;
  margin-top: 12px;
}

/* LIST HEADER */

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

.add-academic-button {
  flex-shrink: 0;
  white-space: nowrap;
}

/* SEARCH */

.search-form {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 9px;
  margin-bottom: 19px;
}

.search-input-wrapper {
  display: flex;
  width: 260px;
  height: 39px;
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

.search-button {
  flex-shrink: 0;
}

/* RESPONSIVE */

@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .page-header {
    flex-wrap: wrap;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .list-header {
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .add-academic-button {
    width: 100%;
  }

  .search-form {
    width: 100%;
  }

  .search-input-wrapper {
    width: auto;
    flex: 1;
  }
}
</style>
