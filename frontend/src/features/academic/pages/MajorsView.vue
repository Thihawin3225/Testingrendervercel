<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import {
  Plus,
  Search,
  GraduationCap,
  CheckCircle,
  Code,
} from '@lucide/vue'

// import BaseButton from '@/components/ui/BaseButton.vue'
import BaseBackButton from '@/components/ui/BaseBackButton.vue'
import BaseButton from '../../../components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BasePageMessage from '@/components/ui/BasePageMessage.vue'

import { usePageMessage } from '@/composables/usePageMessage'

import MajorForm from '../components/majors/MajorForm.vue'
import MajorTable from '../components/majors/MajorTable.vue'

import { useMajors } from '../queries/major.queries'

import {
  useCreateMajor,
  useUpdateMajor,
  useDeleteMajor,
} from '../mutations/major.mutations'

import { getEmptyMajorForm } from '../utils/major.utils'

import type {
  Major,
  MajorFormData,
  PaginationMeta,
} from '../interfaces/major.interface'

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

const formData = ref<MajorFormData>(
  getEmptyMajorForm(),
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
} = useMajors(filters)

const majors = computed<Major[]>(
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
        : 'Unable to load majors.',
    )
  }
})

// ==========================================
// TANSTACK MUTATIONS
// ==========================================

const createMajor = useCreateMajor()
const updateMajor = useUpdateMajor()
const deleteMajor = useDeleteMajor()

const saving = computed(
  () =>
    createMajor.isPending.value ||
    updateMajor.isPending.value,
)

// ==========================================
// COMPUTED
// ==========================================

const modalTitle = computed(() =>
  editingId.value === null
    ? 'Create Major'
    : 'Edit Major',
)

// ==========================================
// CREATE MODAL
// ==========================================

function openCreateModal() {
  editingId.value = null
  formData.value = getEmptyMajorForm()

  clearMessages()

  showModal.value = true
}

// ==========================================
// EDIT MODAL
// ==========================================

function openEditModal(major: Major) {
  editingId.value = major.id

  formData.value = {
    code: major.code,
    name: major.name,
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
  formData.value = getEmptyMajorForm()
}

// ==========================================
// CREATE / UPDATE
// ==========================================

async function submitForm(data: MajorFormData) {
  clearError()

  const code = data.code.trim()
  const name = data.name.trim()

  if (!code || !name) {
    showError('Please complete all required fields.')
    return
  }

  if (code.length > 10) {
    showError(
      'Major code cannot exceed 10 characters.',
    )
    return
  }

  if (name.length > 100) {
    showError(
      'Major name cannot exceed 100 characters.',
    )
    return
  }

  const payload: MajorFormData = {
    code,
    name,
  }

  try {
    if (editingId.value !== null) {
      await updateMajor.mutateAsync({
        id: editingId.value,
        data: payload,
      })

      showSuccess('Major updated successfully.')
    } else {
      await createMajor.mutateAsync(payload)

      showSuccess('Major created successfully.')
    }

    showModal.value = false
    editingId.value = null
    formData.value = getEmptyMajorForm()

    currentPage.value = 1
  } catch (error: unknown) {
    console.error('Failed to save major:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to save major. Please try again.',
    )
  }
}

// ==========================================
// DELETE
// ==========================================

async function handleDelete(major: Major) {
  const confirmed = window.confirm(
    `Are you sure you want to delete "${major.name}"?`,
  )

  if (!confirmed) return

  clearMessages()

  const wasLastItem =
    majors.value.length === 1 &&
    currentPage.value > 1

  try {
    await deleteMajor.mutateAsync(major.id)

    showSuccess('Major deleted successfully.')

    if (wasLastItem) {
      currentPage.value -= 1
    }
  } catch (error: unknown) {
    console.error('Failed to delete major:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to delete major.',
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
        <h1>Majors</h1>
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
            <p>Total Majors</p>
            <h2>{{ pagination.total }}</h2>
          </div>

          <div class="stat-icon total-icon">
            <GraduationCap :size="23" />
          </div>
        </div>
      </BaseCard>

      <!-- CURRENT PAGE -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Displayed on Current Page</p>
            <h2>{{ majors.length }}</h2>
          </div>

          <div class="stat-icon active-icon">
            <CheckCircle :size="23" />
          </div>
        </div>
      </BaseCard>

      <!-- PER PAGE -->
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Majors Per Page</p>
            <h2 class="count-number">
              {{ pagination.per_page }}
            </h2>
          </div>

          <div class="stat-icon inactive-icon">
            <Code :size="23" />
          </div>
        </div>
      </BaseCard>

    </section>

    <!-- MAJOR LIST -->
    <BaseCard padded class="academic-list-card">

      <!-- SECTION HEADER -->
      <div class="list-header">

        <div class="list-heading">
          <h2>Major List</h2>
          <p>View and manage all majors.</p>
        </div>

        <BaseButton
          type="button"
          class="add-academic-button"
          @click="openCreateModal"
        >
          <Plus :size="18" />
          Add Major
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
            placeholder="Search major..."
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
      <MajorTable
        :majors="majors"
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
      <MajorForm
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

.stat-content .count-number {
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
