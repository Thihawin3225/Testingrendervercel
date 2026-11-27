
<script setup lang="ts">
import { computed, ref, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import {
  Plus,
  Search,
  Sparkles,
  RotateCcw,
} from '@lucide/vue'

import BaseBackButton from '@/components/ui/BaseBackButton.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BasePageMessage from '@/components/ui/BasePageMessage.vue'

import { usePageMessage } from '@/composables/usePageMessage'

import SubjectOfferingForm from '../components/subject-offerings/SubjectOfferingForm.vue'
import SubjectOfferingTable from '../components/subject-offerings/SubjectOfferingTable.vue'

import { useSubjectOfferings } from '../queries/subjectOffering.queries'
import { useAcademicYears } from '../queries/academicYear.queries'
import { useYearLevels } from '../queries/yearLevel.queries'
import { useSemesters } from '../queries/semester.queries'
import { useMajors } from '../queries/major.queries'

import {
  useCreateSubjectOffering,
  useCreateSubjectWithOffering,
  useUpdateSubjectOffering,
  useDeleteSubjectOffering,
} from '../mutations/subjectOffering.mutations'

import type {
  SubjectDraft,
  SubjectOffering,
  SubjectOfferingFormData,
  SubjectOfferingFilters,
  PaginationMeta,
} from '../interfaces/subjectOffering.interface'

import {
  getEmptySubjectOfferingForm,
  validateSubjectOfferingForm,
} from '../utils/subjectOffering.utils'

import { subjectsApi } from '../api/subjects.api'
import type { Subject } from '../interfaces/subject.interface'

// ==========================================
// TYPES
// ==========================================

type LockedSubject = Pick<
  Subject,
  'subject_code' | 'subject_name'
>

// ==========================================
// ROUTER
// ==========================================

const route = useRoute()
const router = useRouter()

// ==========================================
// STATE
// ==========================================

const SUBJECT_DRAFT_KEY = 'campus-bridge-subject-draft'

const lockedSubject = ref<LockedSubject | null>(null)
const subjectDraft = ref<SubjectDraft | null>(null)

const isSubjectDraft = ref(false)
const loadingSubject = ref(false)

const search = ref('')
const currentPage = ref(1)

const selectedYearLevel = ref<number | ''>('')
const selectedSemester = ref<number | ''>('')
const selectedMajor = ref<number | ''>('')

const showModal = ref(false)
const editingId = ref<number | null>(null)

const {
  showSuccess,
  showError,
  clearError,
  clearMessages,
} = usePageMessage()

const formData = ref<SubjectOfferingFormData>(
  getEmptySubjectOfferingForm(),
)

// ==========================================
// FILTER DROPDOWN QUERIES
// ==========================================

const {
  data: yearLevelsData,
  isPending: yearLevelsLoading,
} = useYearLevels({
  per_page: 100,
})

const {
  data: semestersData,
  isPending: semestersLoading,
} = useSemesters({
  per_page: 100,
})

const {
  data: majorsData,
  isLoading: majorsLoading,
} = useMajors({ per_page: 100 })

const yearLevels = computed(
  () => yearLevelsData.value?.data ?? [],
)

const semesters = computed(
  () => semestersData.value?.data ?? [],
)

const majors = computed(
  () => majorsData.value?.data ?? [],
)

const filterOptionsLoading = computed(
  () =>
    yearLevelsLoading.value ||
    semestersLoading.value ||
    majorsLoading.value,
)

// ==========================================
// ACADEMIC YEAR QUERY
// ==========================================

const {
  data: academicYearsData,
  isPending: academicYearsLoading,
} = useAcademicYears({
  per_page: 100,
})

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
      year => isActiveYear(year.is_active),
    ) ?? null,
)

// ==========================================
// SET ACTIVE ACADEMIC YEAR
// ==========================================

watch(
  activeAcademicYear,
  (year) => {
    if (
      year &&
      showModal.value &&
      editingId.value === null &&
      !formData.value.academic_year_id
    ) {
      formData.value.academic_year_id = year.id
    }
  },
  { immediate: true },
)

// ==========================================
// FILTERS
// ==========================================

const filters = computed<SubjectOfferingFilters>(() => ({
  search: search.value.trim() || undefined,

  year_level_id:
    selectedYearLevel.value !== ''
      ? Number(selectedYearLevel.value)
      : undefined,

  semester_id:
    selectedSemester.value !== ''
      ? Number(selectedSemester.value)
      : undefined,

  major_id:
    selectedMajor.value === ''
      ? undefined
      : Number(selectedMajor.value),

  page: currentPage.value,
  per_page: 10,
}))

watch(
  [
    search,
    selectedYearLevel,
    selectedSemester,
    selectedMajor,
  ],
  () => {
    currentPage.value = 1
  },
)

// ==========================================
// SUBJECT OFFERINGS QUERY
// ==========================================

const {
  data,
  isPending,
  isFetching,
  error,
} = useSubjectOfferings(filters)

const subjectOfferings = computed<SubjectOffering[]>(
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

const totalSubjectOfferings = computed(
  () => pagination.value.total,
)

// ==========================================
// TANSTACK MUTATIONS
// ==========================================

const createSubjectOffering = useCreateSubjectOffering()

const createSubjectWithOffering =
  useCreateSubjectWithOffering()

const updateSubjectOffering = useUpdateSubjectOffering()

const deleteSubjectOfferingMutation =
  useDeleteSubjectOffering()

const saving = computed(
  () =>
    createSubjectOffering.isPending.value ||
    createSubjectWithOffering.isPending.value ||
    updateSubjectOffering.isPending.value,
)

// ==========================================
// AUTO OPEN MODAL AFTER SUBJECT FORM
// ==========================================

watch(
  () => [
    route.query.open,
    route.query.draft,
    route.query.subject_id,
  ],
  async ([open, draft, subjectId]) => {
    const openValue = Array.isArray(open) ? open[0] : open
    const draftValue = Array.isArray(draft) ? draft[0] : draft
    const subjectIdValue = Array.isArray(subjectId)
      ? subjectId[0]
      : subjectId

    if (openValue !== 'create') return

    clearMessages()

    editingId.value = null
    lockedSubject.value = null
    subjectDraft.value = null
    isSubjectDraft.value = false

    formData.value = {
      ...getEmptySubjectOfferingForm(),
      academic_year_id: activeAcademicYear.value?.id ?? 0,
    }

    // NEW SUBJECT DRAFT
    if (draftValue === '1') {
      const savedDraft = sessionStorage.getItem(
        SUBJECT_DRAFT_KEY,
      )

      if (!savedDraft) {
        showError(
          'Subject draft not found. Please create the subject again.',
        )
        return
      }

      try {
        const parsedDraft = JSON.parse(
          savedDraft,
        ) as SubjectDraft

        subjectDraft.value = parsedDraft

        lockedSubject.value = {
          subject_code: parsedDraft.subject_code,
          subject_name: parsedDraft.subject_name,
        }

        isSubjectDraft.value = true

        showModal.value = true
        await nextTick()

      } catch (error) {
        console.error('Invalid subject draft:', error)
        showError('Unable to read subject draft.')
      }

      return
    }

    // EXISTING SUBJECT ID FLOW
    const id = Number(subjectIdValue)

    if (!Number.isInteger(id) || id <= 0) {
      showError('Invalid subject ID.')
      return
    }

    try {
      loadingSubject.value = true

      showModal.value = true
      await nextTick()

      const subject = await subjectsApi.getById(id)

      if (Number(route.query.subject_id) !== id) return

      lockedSubject.value = {
        subject_code: subject.subject_code,
        subject_name: subject.subject_name,
      }

      subjectDraft.value = null
      isSubjectDraft.value = false

      formData.value = {
        ...getEmptySubjectOfferingForm(),
        academic_year_id: activeAcademicYear.value?.id ?? 0,
        subject_id: subject.id,
      }

      showModal.value = true

      await nextTick()

    } catch (error: unknown) {
      console.error('Failed to load subject:', error)

      showModal.value = false

      showError(
        error instanceof Error
          ? error.message
          : 'Unable to load subject details.',
      )
    } finally {
      loadingSubject.value = false
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
        : 'Unable to load subject offerings.',
    )
  }
})

// ==========================================
// MODAL COMPUTED
// ==========================================

const modalTitle = computed(() =>
  editingId.value === null
    ? 'Create Subject Offering'
    : 'Edit Subject Offering',
)

const submitLabel = computed(() =>
  editingId.value === null
    ? 'Create Subject Offering'
    : 'Update Subject Offering',
)

// ==========================================
// CREATE MODAL
// ==========================================

function openCreateModal() {
  editingId.value = null

  lockedSubject.value = null
  subjectDraft.value = null
  isSubjectDraft.value = false

  formData.value = {
    ...getEmptySubjectOfferingForm(),
    academic_year_id: activeAcademicYear.value?.id ?? 0,
  }

  clearMessages()
  showModal.value = true
}

// ==========================================
// EDIT MODAL
// ==========================================

function openEditModal(offering: SubjectOffering) {
  editingId.value = offering.id

  lockedSubject.value = null
  subjectDraft.value = null
  isSubjectDraft.value = false

  formData.value = {
    academic_year_id: offering.academic_year?.id ?? 0,
    year_level_id: offering.year_level?.id ?? 0,
    major_id: offering.major?.id ?? 0,
    semester_id: offering.semester?.id ?? 0,
    subject_id: offering.subject?.id ?? 0,
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

  lockedSubject.value = null
  subjectDraft.value = null
  isSubjectDraft.value = false

  formData.value = getEmptySubjectOfferingForm()

  sessionStorage.removeItem(SUBJECT_DRAFT_KEY)
}

// ==========================================
// CANCEL SUBJECT OFFERING
// ==========================================

function cancelSubjectOffering() {
  if (saving.value) return

  if (isSubjectDraft.value) {
    showModal.value = false
    router.back()
    return
  }

  closeModal()
}

// ==========================================
// CLEAR FILTERS
// ==========================================

function clearFilters() {
  search.value = ''
  selectedYearLevel.value = ''
  selectedSemester.value = ''
  selectedMajor.value = ''
  currentPage.value = 1
}

// ==========================================
// VALIDATE DRAFT OFFERING
// ==========================================

function validateDraftOffering(): string | null {
  if (!formData.value.academic_year_id) {
    return 'Please select an Academic Year.'
  }

  if (!formData.value.year_level_id) {
    return 'Please select a Year Level.'
  }

  if (!formData.value.major_id) {
    return 'Please select a Major.'
  }

  if (!formData.value.semester_id) {
    return 'Please select a Semester.'
  }

  return null
}

// ==========================================
// CREATE / UPDATE
// ==========================================

async function submitForm() {
  clearError()

  if (
    editingId.value === null &&
    !formData.value.academic_year_id
  ) {
    showError('Please select an academic year.')
    return
  }

  let validationError: string | null = null

  if (isSubjectDraft.value) {
    validationError = validateDraftOffering()
  } else {
    validationError = validateSubjectOfferingForm(formData.value)
  }

  if (validationError) {
    showError(validationError)
    return
  }

  const offeringData = {
    academic_year_id: Number(formData.value.academic_year_id),
    year_level_id: Number(formData.value.year_level_id),
    major_id: Number(formData.value.major_id),
    semester_id: Number(formData.value.semester_id),
  }

  const payload: SubjectOfferingFormData = {
    ...offeringData,
    subject_id: Number(formData.value.subject_id),
  }

  try {
    // UPDATE EXISTING OFFERING
    if (editingId.value !== null) {
      await updateSubjectOffering.mutateAsync({
        id: editingId.value,
        data: payload,
      })

      showSuccess('Subject offering updated successfully.')

      showModal.value = false
      editingId.value = null
      lockedSubject.value = null
      subjectDraft.value = null
      isSubjectDraft.value = false
      formData.value = getEmptySubjectOfferingForm()
      currentPage.value = 1

      return
    }

    // CREATE NEW SUBJECT + FIRST OFFERING
    if (isSubjectDraft.value && subjectDraft.value) {
      await createSubjectWithOffering.mutateAsync({
        subject: {
          subject_code: subjectDraft.value.subject_code,
          subject_name: subjectDraft.value.subject_name,
          description: subjectDraft.value.description,
        },
        offering: offeringData,
      })

      sessionStorage.removeItem(SUBJECT_DRAFT_KEY)

      showSuccess('Subject and offering created successfully.')

      showModal.value = false
      editingId.value = null
      lockedSubject.value = null
      subjectDraft.value = null
      isSubjectDraft.value = false
      formData.value = getEmptySubjectOfferingForm()
      currentPage.value = 1

      return
    }

    // ==========================================
    // CREATE ANOTHER OFFERING FOR EXISTING SUBJECT
    // ==========================================

    const currentSubjectId = Number(
      formData.value.subject_id,
    )

    const currentAcademicYearId = Number(
      formData.value.academic_year_id,
    )

    await createSubjectOffering.mutateAsync(payload)

    showSuccess('Subject offering created successfully.')

    // Reset only Year Level, Major and Semester.
    // Preserve selected Subject and Academic Year.
    formData.value = {
      ...getEmptySubjectOfferingForm(),
      academic_year_id: currentAcademicYearId,
      subject_id: currentSubjectId,
    }

    // Keep lockedSubject unchanged.
    // This keeps the selected subject visible in the form.

    currentPage.value = 1

    // Keep the modal open for the next offering.
    showModal.value = true

    return

  } catch (error: unknown) {
    console.error('Subject offering save error:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Failed to save subject offering.',
    )
  }
}

// ==========================================
// DELETE
// ==========================================

async function handleDelete(offering: SubjectOffering) {
  const subjectName =
    offering.subject?.subject_name ?? 'this subject offering'

  const confirmed = window.confirm(
    `Are you sure you want to delete the subject offering for "${subjectName}"?`,
  )

  if (!confirmed) return

  clearMessages()

  const wasLastItem =
    subjectOfferings.value.length === 1 &&
    currentPage.value > 1

  try {
    await deleteSubjectOfferingMutation.mutateAsync(
      offering.id,
    )

    showSuccess('Subject offering deleted successfully.')

    if (wasLastItem) {
      currentPage.value -= 1
    }
  } catch (error: unknown) {
    console.error('Failed to delete subject offering:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to delete subject offering.',
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
  <section class="subject-offerings-page space-y-6">

    <!-- PAGE HEADING -->
    <header class="page-header">
      <div>
        <p class="eyebrow">ACADEMIC OPERATIONS</p>
        <h1>Subject Offerings</h1>
      </div>

      <BaseBackButton to="/admin/academics" />
    </header>

    <!-- GLOBAL SUCCESS / ERROR MESSAGE -->
    <BasePageMessage />

    <!-- SUBJECT OFFERING LIST -->
    <BaseCard padded class="academic-list-card">

      <!-- SECTION HEADER -->
      <div class="list-header">
        <div class="list-heading">
          <h2>Subject Offering List</h2>
          <p>View and manage all subject offerings.</p>
        </div>

        <div class="list-header-actions">
          <div class="total-count">
            <span>Total Offerings</span>
            <strong>{{ totalSubjectOfferings }}</strong>
          </div>
        </div>
      </div>

      <!-- SEARCH + FILTERS -->
      <form
        class="filters-toolbar"
        @submit.prevent="handleSearch"
      >

        <!-- SEARCH INPUT -->
        <div class="search-input-wrapper">
          <Search :size="17" />

          <input
            v-model="search"
            type="search"
            placeholder="Search subject offering..."
          />
        </div>

        <!-- YEAR LEVEL FILTER -->
        <div class="filter-group">
          <select
            id="filter-year-level"
            v-model.number="selectedYearLevel"
            class="filter-input"
            :disabled="filterOptionsLoading"
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

        <!-- SEMESTER FILTER -->
        <div class="filter-group">
          <select
            id="filter-semester"
            v-model.number="selectedSemester"
            class="filter-input"
            :disabled="filterOptionsLoading"
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

        <!-- MAJOR FILTER -->
        <div class="filter-group">
          <select
            id="filter-major"
            v-model.number="selectedMajor"
            class="filter-input"
            :disabled="filterOptionsLoading"
          >
            <option value="">All Majors</option>

            <option
              v-for="major in majors"
              :key="major.id"
              :value="major.id"
            >
              {{ major.name }}
            </option>
          </select>
        </div>

        <!-- SEARCH BUTTON -->
        <BaseButton
          type="submit"
          variant="outline"
          class="search-button"
        >
          <Search :size="16" />
          Search
        </BaseButton>

        <!-- CLEAR FILTERS -->
        <BaseButton
          type="button"
          variant="outline"
          class="clear-filter-button"
          @click="clearFilters"
        >
          <RotateCcw :size="15" />
          Clear
        </BaseButton>

      </form>

      <!-- TABLE -->
      <SubjectOfferingTable
        :subject-offerings="subjectOfferings"
        :loading="loading"
        :pagination="pagination"
        @edit="openEditModal"
        @delete="handleDelete"
        @page="handlePageChange"
      />

    </BaseCard>

    <!-- CREATE / EDIT MODAL -->
    <BaseModal
      v-model="showModal"
      :title="modalTitle"
      @close="closeModal"
    >
      <SubjectOfferingForm
        v-model="formData"
        :loading="
          saving ||
          loadingSubject ||
          academicYearsLoading
        "
        :locked-subject="lockedSubject"
        :submit-label="submitLabel"
        @submit="submitForm"
        @cancel="cancelSubjectOffering"
      />
    </BaseModal>

  </section>
</template>

<style scoped>
.subject-offerings-page {
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

/* LIST CARD */

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

/* TOP RIGHT ACTIONS */

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

.add-academic-button {
  flex-shrink: 0;
  white-space: nowrap;
}

/* FILTER TOOLBAR */

.filters-toolbar {
  display: grid;
  grid-template-columns:
    minmax(180px, 1.5fr)
    minmax(130px, 1fr)
    minmax(130px, 1fr)
    minmax(130px, 1fr)
    auto
    auto;
  align-items: center;
  gap: 10px;
  width: 100%;
  margin-bottom: 20px;
}

/* SEARCH INPUT */

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

/* FILTER DROPDOWNS */

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

.filter-input:hover:not(:disabled) {
  border-color: #c8b2f2;
}

.filter-input:focus {
  border-color: #8233f4;
  box-shadow: 0 0 0 3px #8233f41a;
}

.filter-input:disabled {
  cursor: not-allowed;
  background: #f8f7fa;
  opacity: 0.7;
}

/* SEARCH AND CLEAR BUTTONS */

.search-button,
.clear-filter-button {
  height: 39px;
  flex-shrink: 0;
  white-space: nowrap;
}

.search-button {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.clear-filter-button {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* RESPONSIVE */

@media (max-width: 1200px) {
  .filters-toolbar {
    grid-template-columns:
      minmax(180px, 1.5fr)
      repeat(3, minmax(110px, 1fr))
      auto
      auto;
    gap: 8px;
  }

  .filter-input {
    padding: 0 8px;
    font-size: 11px;
  }
}

@media (max-width: 950px) {
  .filters-toolbar {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .search-input-wrapper {
    grid-column: span 2;
  }
}

@media (max-width: 640px) {
  .page-header {
    flex-wrap: wrap;
  }

  .list-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .list-header-actions {
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .add-academic-button {
    flex: 1;
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
    justify-content: center;
  }
}
</style>
