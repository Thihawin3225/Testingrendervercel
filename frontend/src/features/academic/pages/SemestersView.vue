<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import {
  Plus,
  Search,
  Layers,
  CheckCircle,
  ListOrdered,
  NotebookText,
} from '@lucide/vue'

import BaseBackButton from '@/components/ui/BaseBackButton.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BasePageMessage from '@/components/ui/BasePageMessage.vue'

import { usePageMessage } from '@/composables/usePageMessage'

import SemesterForm from '../components/semesters/SemesterForm.vue'
import SemesterTable from '../components/semesters/SemesterTable.vue'

import { useSemesters } from '../queries/semester.queries'

import {
  useCreateSemester,
  useUpdateSemester,
  useDeleteSemester,
} from '../mutations/semester.mutations'

import { getEmptySemesterForm } from '../utils/semester.utils'

import type {
  Semester,
  SemesterFormData,
  PaginationMeta,
} from '../interfaces/semester.interface'

// ==========================================
// STATE
// ==========================================

const search = ref('')
const currentPage = ref(1)

const showModal = ref(false)
const editingId = ref<number | null>(null)

const {
  showSuccess,
  showError,
  clearError,
  clearMessages,
} = usePageMessage()

const formData = ref<SemesterFormData>(
  getEmptySemesterForm(),
)

// ==========================================
// TANSTACK QUERY
// ==========================================

const filters = computed(() => ({
  search: search.value.trim() || undefined,
  page: currentPage.value,
  per_page: 10,
}))

const {
  data,
  isPending,
  isFetching,
  error,
} = useSemesters(filters)

const semesters = computed<Semester[]>(
  () => data.value?.data ?? [],
)

const pagination = computed<PaginationMeta>(
  () =>
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
// QUERY ERROR
// ==========================================

watch(error, (queryError) => {
  if (queryError) {
    showError(
      queryError instanceof Error
        ? queryError.message
        : 'Unable to load semesters.',
    )
  }
})

// ==========================================
// TANSTACK MUTATIONS
// ==========================================

const createSemester = useCreateSemester()
const updateSemester = useUpdateSemester()
const deleteSemester = useDeleteSemester()

const saving = computed(
  () =>
    createSemester.isPending.value ||
    updateSemester.isPending.value,
)

// ==========================================
// COMPUTED
// ==========================================

const highestSortOrder = computed(() => {
  if (semesters.value.length === 0) return 0

  return Math.max(
    ...semesters.value.map((semester) => semester.sort_order),
  )
})

const modalTitle = computed(() =>
  editingId.value === null
    ? 'Create Semester'
    : 'Edit Semester',
)

// ==========================================
// CREATE MODAL
// ==========================================

function openCreateModal() {
  editingId.value = null
  formData.value = getEmptySemesterForm()

  clearMessages()

  showModal.value = true

  console.log('Add Semester clicked')
  console.log('Modal state:', showModal.value)
}

// ==========================================
// EDIT MODAL
// ==========================================

function openEditModal(semester: Semester) {
  editingId.value = semester.id

  formData.value = {
    name: semester.name,
    sort_order: semester.sort_order,
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
  formData.value = getEmptySemesterForm()
}

// ==========================================
// CREATE / UPDATE
// ==========================================

async function submitForm(data: SemesterFormData) {
  clearError()

  const name = data.name.trim()
  const sortOrder = Number(data.sort_order)

  if (!name || !Number.isInteger(sortOrder)) {
    showError('Please complete all required fields.')
    return
  }

  if (name.length > 30) {
    showError('Semester name cannot exceed 30 characters.')
    return
  }

  if (sortOrder < 1 || sortOrder > 255) {
    showError('Sort order must be between 1 and 255.')
    return
  }

  const payload: SemesterFormData = {
    name,
    sort_order: sortOrder,
  }

  try {
    if (editingId.value !== null) {
      await updateSemester.mutateAsync({
        id: editingId.value,
        data: payload,
      })

      showSuccess('Semester updated successfully.')
    } else {
      await createSemester.mutateAsync(payload)

      showSuccess('Semester created successfully.')
    }

    showModal.value = false
    editingId.value = null
    formData.value = getEmptySemesterForm()

    currentPage.value = 1
  } catch (error: unknown) {
    console.error('Failed to save semester:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to save semester. Please try again.',
    )
  }
}

// ==========================================
// DELETE
// ==========================================

async function handleDelete(semester: Semester) {
  const confirmed = window.confirm(
    `Are you sure you want to delete "${semester.name}"?`,
  )

  if (!confirmed) return

  clearMessages()

  const wasLastItem =
    semesters.value.length === 1 &&
    currentPage.value > 1

  try {
    await deleteSemester.mutateAsync(semester.id)

    showSuccess('Semester deleted successfully.')

    if (wasLastItem) {
      currentPage.value -= 1
    }
  } catch (error: unknown) {
    console.error('Failed to delete semester:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to delete semester.',
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
        <h1>Semesters</h1>
      </div>

      <BaseBackButton to="/admin/academics" />
    </header>

    <!-- GLOBAL SUCCESS / ERROR MESSAGE -->
    <BasePageMessage />

    <!-- STATISTICS -->
    <section class="stats-grid">

      <!-- TOTAL -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Total Semesters</p>
            <h2>{{ pagination.total }}</h2>
          </div>

          <div class="stat-icon total-icon">
            <NotebookText :size="23" />
          </div>
        </div>
      </BaseCard>

      <!-- CURRENT PAGE -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Displayed on Current Page</p>
            <h2>{{ semesters.length }}</h2>
          </div>

          <div class="stat-icon active-icon">
            <CheckCircle :size="23" />
          </div>
        </div>
      </BaseCard>

      <!-- HIGHEST SORT ORDER -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Highest Sort Order (Current Page)</p>
            <h2 class="sort-number">{{ highestSortOrder }}</h2>
          </div>

          <div class="stat-icon inactive-icon">
            <ListOrdered :size="23" />
          </div>
        </div>
      </BaseCard>

    </section>

    <!-- SEMESTER LIST -->
    <BaseCard padded class="academic-list-card">

      <!-- SECTION HEADER -->
      <div class="list-header">

        <div class="list-heading">
          <h2>Semester List</h2>
          <p>View and manage all semesters.</p>
        </div>

        <BaseButton
          type="button"
          class="add-academic-button"
          @click="openCreateModal"
        >
          <Plus :size="18" />
          Add Semester
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
            placeholder="Search semester..."
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

      <!-- TABLE -->
      <SemesterTable
        :semesters="semesters"
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
      <SemesterForm
        :initial-data="formData"
        :is-submitting="saving"
        @submit="submitForm"
        @cancel="closeModal"
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

/* PAGE HEADER - SAME AS ADMIN STUDENT VIEW */

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

.stat-content .sort-number {
  color: #7138e5;
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
