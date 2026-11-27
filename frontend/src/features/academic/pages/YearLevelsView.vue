<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import {
  Plus,
  Search,
  School,
  CheckCircle,
  ListOrdered,
} from '@lucide/vue'

import BaseBackButton from '@/components/ui/BaseBackButton.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BasePageMessage from '@/components/ui/BasePageMessage.vue'
import { usePageMessage } from '@/composables/usePageMessage'

import YearLevelForm from '../components/year-levels/YearLevelForm.vue'
import YearLevelTable from '../components/year-levels/YearLevelTable.vue'

import { useYearLevels } from '../queries/yearLevel.queries'

import {
  useCreateYearLevel,
  useUpdateYearLevel,
  useDeleteYearLevel,
} from '../mutations/yearLevel.mutations'

import { getEmptyYearLevelForm } from '../utils/yearLevel.utils'

import type {
  YearLevel,
  YearLevelFormData,
  YearLevelFilters,
  PaginationMeta,
} from '../interfaces/yearLevel.interface'

// ==========================================
// STATE
// ==========================================

const search = ref('')
const currentPage = ref(1)

const showModal = ref(false)
const editingId = ref<number | null>(null)

// Keep the original record to identify an existing Final Year.
const editingYearLevel = ref<YearLevel | null>(null)

const {
  showSuccess,
  showError,
  clearError,
  clearMessages,
} = usePageMessage()

const formData = ref<YearLevelFormData>(
  getEmptyYearLevelForm(),
)

// ==========================================
// TANSTACK QUERY
// ==========================================

const filters = computed<YearLevelFilters>(() => ({
  search: search.value.trim() || undefined,
  page: currentPage.value,
  per_page: 10,
}))

const {
  data,
  isPending,
  isFetching,
  error,
} = useYearLevels(filters)

const yearLevels = computed<YearLevel[]>(
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

watch(error, (queryError) => {
  if (queryError) {
    showError(
      queryError instanceof Error
        ? queryError.message
        : 'Unable to load year levels.',
    )
  }
})

// ==========================================
// TANSTACK MUTATIONS
// ==========================================

const createYearLevel = useCreateYearLevel()
const updateYearLevel = useUpdateYearLevel()
const deleteYearLevelMutation = useDeleteYearLevel()

const saving = computed(
  () =>
    createYearLevel.isPending.value ||
    updateYearLevel.isPending.value,
)

// ==========================================
// COMPUTED
// ==========================================

const highestSortOrder = computed(() => {
  if (yearLevels.value.length === 0) return 0

  return Math.max(
    ...yearLevels.value.map(
      (yearLevel) => yearLevel.sort_order,
    ),
  )
})

const modalTitle = computed(() =>
  editingId.value === null
    ? 'Create Year Level'
    : 'Edit Year Level',
)

const submitLabel = computed(() =>
  editingId.value === null
    ? 'Create Year Level'
    : 'Update Year Level',
)

const isEditMode = computed(
  () => editingId.value !== null,
)

const originalName = computed(
  () => editingYearLevel.value?.name ?? '',
)

// ==========================================
// CREATE MODAL
// ==========================================

function openCreateModal() {
  editingId.value = null
  editingYearLevel.value = null

  formData.value = getEmptyYearLevelForm()

  clearMessages()

  showModal.value = true
}

// ==========================================
// EDIT MODAL
// ==========================================

function openEditModal(yearLevel: YearLevel) {
  editingId.value = yearLevel.id
  editingYearLevel.value = yearLevel

  formData.value = {
    name: yearLevel.name,
    sort_order: yearLevel.sort_order,
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
  editingYearLevel.value = null

  formData.value = getEmptyYearLevelForm()
}

// ==========================================
// CREATE / UPDATE
// ==========================================

async function submitForm() {
  clearError()

  const name = formData.value.name.trim()

  if (!name) {
    showError('Please enter a year level name.')
    return
  }

  const isFinalName = /\bfinal\b/i.test(name)

  const wasAlreadyFinal = editingYearLevel.value
    ? /\bfinal\b/i.test(editingYearLevel.value.name)
    : false

  // The Form asks the user to confirm before emitting submit.
  // This flag is sent only when confirmation is required.
  const requiresFinalConfirmation =
    isFinalName && !wasAlreadyFinal

  // Backend calculates sort_order.
  // Do not send sort_order from the frontend.
  const payload = {
    name,
    ...(requiresFinalConfirmation
      ? { final_year_confirmed: true }
      : {}),
  }

  try {
    if (editingId.value !== null) {
      await updateYearLevel.mutateAsync({
        id: editingId.value,
        data: payload,
      })

      showSuccess('Year level updated successfully.')
    } else {
      await createYearLevel.mutateAsync(payload)

      showSuccess('Year level created successfully.')
    }

    showModal.value = false
    editingId.value = null
    editingYearLevel.value = null

    formData.value = getEmptyYearLevelForm()

    currentPage.value = 1
  } catch (error: unknown) {
    console.error('Failed to save year level:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to save year level. Please try again.',
    )
  }
}

// ==========================================
// DELETE
// ==========================================

async function handleDelete(yearLevel: YearLevel) {
  const confirmed = window.confirm(
    `Are you sure you want to delete "${yearLevel.name}"?`,
  )

  if (!confirmed) return

  clearMessages()

  const wasLastItem =
    yearLevels.value.length === 1 &&
    currentPage.value > 1

  try {
    await deleteYearLevelMutation.mutateAsync(yearLevel.id)

    showSuccess('Year level deleted successfully.')

    if (wasLastItem) {
      currentPage.value -= 1
    }
  } catch (error: unknown) {
    console.error('Failed to delete year level:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to delete year level.',
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
        <h1>Year Levels</h1>
      </div>

      <BaseBackButton to="/admin/academics" />
    </header>

    <!-- GLOBAL PAGE MESSAGE -->
    <BasePageMessage />

    <!-- STATISTICS -->
    <section class="stats-grid">

      <!-- TOTAL -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Total Year Levels</p>
            <h2>{{ pagination.total }}</h2>
          </div>

          <div class="stat-icon total-icon">
            <School :size="23" />
          </div>
        </div>
      </BaseCard>

      <!-- CURRENT PAGE -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Displayed on Current Page</p>
            <h2>{{ yearLevels.length }}</h2>
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

    <!-- YEAR LEVEL LIST -->
    <BaseCard padded class="academic-list-card">

      <!-- SECTION HEADER -->
      <div class="list-header">
        <div class="list-heading">
          <h2>Year Level List</h2>
          <p>View and manage all year levels.</p>
        </div>

        <BaseButton
          type="button"
          class="add-academic-button"
          @click="openCreateModal"
        >
          <Plus :size="18" />
          Add Year Level
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
            placeholder="Search year level..."
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
      <YearLevelTable
        :year-levels="yearLevels"
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
      <YearLevelForm
        v-model="formData"
        :loading="saving"
        :submit-label="submitLabel"
        :is-edit-mode="isEditMode"
        :original-name="originalName"
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
