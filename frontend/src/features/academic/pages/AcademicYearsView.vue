<script setup lang="ts">
import { computed, ref } from 'vue'

import {
  Plus,
  Search,
  CalendarRange,
  CheckCircle,
  Layers,
} from '@lucide/vue'

import BaseBackButton from '@/components/ui/BaseBackButton.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BasePageMessage from '@/components/ui/BasePageMessage.vue'
import { usePageMessage } from '@/composables/usePageMessage'

import AcademicYearForm from '../components/academic-years/AcademicYearForm.vue'
import AcademicYearTable from '../components/academic-years/AcademicYearTable.vue'

import { useAcademicYears } from '../queries/academicYear.queries'
import { academicYearsApi } from '../api/academicYears.api'

import {
  useCreateAcademicYear,
  useUpdateAcademicYear,
} from '../mutations/academicYear.mutations'

import { getEmptyAcademicYearForm } from '../utils/academicYear.utils'

import type {
  AcademicYear,
  AcademicYearFormData,
  AcademicYearFilters,
  PaginationMeta,
} from '../interfaces/academicYear.interface'

// ==========================================
// STATE
// ==========================================

const search = ref('')
const currentPage = ref(1)

const showModal = ref(false)
const editingId = ref<number | null>(null)

const latestStartDate = ref<string | null>(null)
const originalStartDate = ref<string | null>(null)

const latestStartDateLoading = ref(false)

const {
  showSuccess,
  showError,
  clearSuccess,
  clearError,
  clearMessages,
} = usePageMessage()

const formData = ref<AcademicYearFormData>(
  getEmptyAcademicYearForm(),
)

// ==========================================
// TANSTACK QUERY
// ==========================================

const filters = computed<AcademicYearFilters>(() => ({
  search: search.value.trim() || undefined,
  page: currentPage.value,
  per_page: 10,
}))

const {
  data,
  isPending,
  isFetching,
} = useAcademicYears(filters)

const academicYears = computed<AcademicYear[]>(
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
// TANSTACK MUTATIONS
// ==========================================

const createAcademicYear = useCreateAcademicYear()
const updateAcademicYear = useUpdateAcademicYear()

const saving = computed(
  () =>
    createAcademicYear.isPending.value ||
    updateAcademicYear.isPending.value,
)

// ==========================================
// COMPUTED
// ==========================================

const activeCount = computed(
  () => academicYears.value.filter((year) => year.is_active).length,
)

const inactiveCount = computed(
  () => academicYears.value.filter((year) => !year.is_active).length,
)

const modalTitle = computed(() =>
  editingId.value === null
    ? 'Create Academic Year'
    : 'Edit Academic Year',
)

const submitLabel = computed(() =>
  editingId.value === null
    ? 'Create Academic Year'
    : 'Update Academic Year',
)

// ==========================================
// CREATE MODAL
// ==========================================

async function openCreateModal() {
  if (latestStartDateLoading.value || saving.value) return

  editingId.value = null
  originalStartDate.value = null
  latestStartDate.value = null

  formData.value = getEmptyAcademicYearForm()

  clearMessages()

  latestStartDateLoading.value = true

  try {
    latestStartDate.value =
      await academicYearsApi.getLatestStartDate()

    showModal.value = true
  } catch (error) {
    showError(
      error instanceof Error
        ? error.message
        : 'Unable to load the latest academic year. Please try again.',
    )
  } finally {
    latestStartDateLoading.value = false
  }
}

// ==========================================
// EDIT MODAL
// ==========================================

function openEditModal(year: AcademicYear) {
  if (saving.value) return

  editingId.value = year.id

  // Keep the original start date before editing
  originalStartDate.value = year.start_date

  formData.value = {
    name: year.name,
    start_date: year.start_date,
    end_date: year.end_date,
    is_active: year.is_active,
  }

  latestStartDate.value = null

  clearMessages()

  showModal.value = true
}

// ==========================================
// CLOSE MODAL
// ==========================================

function closeModal() {
  if (saving.value || latestStartDateLoading.value) return

  showModal.value = false
  editingId.value = null

  latestStartDate.value = null
  originalStartDate.value = null

  formData.value = getEmptyAcademicYearForm()

  clearMessages()
}

// ==========================================
// CREATE / UPDATE
// ==========================================

async function submitForm() {
  clearError()

  if (
    !formData.value.name.trim() ||
    !formData.value.start_date ||
    !formData.value.end_date
  ) {
    showError('Please complete all required fields.')
    return
  }

  if (formData.value.end_date < formData.value.start_date) {
    showError('End date must be after or equal to start date.')
    return
  }

  const payload: AcademicYearFormData = {
    ...formData.value,
    name: formData.value.name.trim(),
  }

  try {
    if (editingId.value !== null) {
      await updateAcademicYear.mutateAsync({
        id: editingId.value,
        data: payload,
      })

      showSuccess('Academic year updated successfully.')
    } else {
      await createAcademicYear.mutateAsync(payload)

      showSuccess('Academic year created successfully.')
    }

    closeModal()

    currentPage.value = 1
  } catch (error) {
    showError(
      error instanceof Error
        ? error.message
        : 'Unable to save academic year. Please try again.',
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
        <h1>Academic Years</h1>
      </div>

      <BaseBackButton to="/admin/academics" />
    </header>

    <!-- PAGE MESSAGE -->
    <BasePageMessage />

    <!-- STATISTICS -->
    <section class="stats-grid">

      <!-- TOTAL -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Total Academic Years</p>
            <h2>{{ pagination.total }}</h2>
          </div>

          <div class="stat-icon total-icon">
            <CalendarRange :size="23" />
          </div>
        </div>
      </BaseCard>

      <!-- ACTIVE -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Active (Current Page)</p>
            <h2 class="active-number">{{ activeCount }}</h2>
          </div>

          <div class="stat-icon active-icon">
            <CheckCircle :size="23" />
          </div>
        </div>
      </BaseCard>

      <!-- INACTIVE -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Inactive (Current Page)</p>
            <h2 class="inactive-number">{{ inactiveCount }}</h2>
          </div>

          <div class="stat-icon inactive-icon">
            <Layers :size="23" />
          </div>
        </div>
      </BaseCard>

    </section>

    <!-- ACADEMIC YEAR LIST -->
    <BaseCard padded class="academic-list-card">

      <!-- SECTION HEADER -->
      <div class="list-header">

        <div class="list-heading">
          <h2>Academic Year List</h2>
          <p>View and manage all academic years.</p>
        </div>

        <BaseButton
          type="button"
          class="add-academic-button"
          :disabled="latestStartDateLoading || saving"
          @click="openCreateModal"
        >
          <Plus :size="18" />
          {{ latestStartDateLoading ? 'Loading...' : 'Add Academic Year' }}
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
            placeholder="Search academic year..."
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
      <AcademicYearTable
        :academic-years="academicYears"
        :loading="loading"
        :pagination="pagination"
        @edit="openEditModal"
        @page="handlePageChange"
      />

    </BaseCard>

    <!-- CREATE / EDIT MODAL -->
    <BaseModal
      v-model="showModal"
      :title="modalTitle"
      @close="closeModal"
    >
      <AcademicYearForm
        v-model="formData"
        :loading="saving"
        :submit-label="submitLabel"
        :latest-start-date="latestStartDate"
        :is-edit-mode="editingId !== null"
        :original-start-date="originalStartDate"
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

/* MESSAGES */

.message {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 12px 15px;
  border: 1px solid;
  border-radius: 11px;
  font-size: 13px;
}

.success-message {
  border-color: #bcebd3;
  background: #effbf4;
  color: #16845b;
}

.error-message {
  border-color: #ffc8ce;
  background: #fff4f4;
  color: #c92f49;
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
