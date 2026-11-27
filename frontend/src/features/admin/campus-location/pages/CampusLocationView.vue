<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import {
  Plus,
  Search,
  MapPin,
  CheckCircle,
  Building2,
} from '@lucide/vue'

import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BasePageMessage from '@/components/ui/BasePageMessage.vue'

import { usePageMessage } from '@/composables/usePageMessage'

import CampusLocationForm from '../components/CampusLocationForm.vue'
import CampusLocationTable from '../components/CampusLocationTable.vue'

import { useCampusLocations } from '../queries/campusLocation.queries'

import {
  useCreateCampusLocation,
  useUpdateCampusLocation,
  useDeleteCampusLocation,
  useDeactivateCampusLocation,
} from '../mutations/campusLocation.mutations'

import {
  getEmptyCampusLocationForm,
} from '../utils/campusLocation.utils'

import type {
  CampusLocation,
  CampusLocationFormData,
} from '../interfaces/campusLocation.interface'

// --------------------------------------------------
// STATE
// --------------------------------------------------

const search = ref('')
const currentPage = ref(1)
const pageSize = 10

const showModal = ref(false)
const editingId = ref<number | null>(null)

const formData = ref<CampusLocationFormData>(
  getEmptyCampusLocationForm(),
)

const {
  showSuccess,
  showError,
  clearError,
  clearMessages,
} = usePageMessage()

// --------------------------------------------------
// QUERY
// --------------------------------------------------

const {
  data,
  isPending,
  isFetching,
  error,
} = useCampusLocations()

const allLocations = computed<CampusLocation[]>(
  () => data.value?.data ?? [],
)

const loading = computed(
  () => isPending.value || isFetching.value,
)

// --------------------------------------------------
// SEARCH
// --------------------------------------------------

const filteredLocations = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  if (!keyword) {
    return allLocations.value
  }

  return allLocations.value.filter((location) => {
    return (
      location.name.toLowerCase().includes(keyword) ||
      (location.building ?? '').toLowerCase().includes(keyword) ||
      (location.display_name ?? '').toLowerCase().includes(keyword) ||
      (location.description ?? '').toLowerCase().includes(keyword)
    )
  })
})

// --------------------------------------------------
// STATISTICS
// --------------------------------------------------

const totalLocations = computed(
  () => allLocations.value.length,
)

const activeLocations = computed(
  () => allLocations.value.filter(
    (location) => location.is_active,
  ).length,
)

const inactiveLocations = computed(
  () => allLocations.value.filter(
    (location) => !location.is_active,
  ).length,
)

// --------------------------------------------------
// PAGINATION
// --------------------------------------------------

const totalPages = computed(() =>
  Math.max(
    1,
    Math.ceil(filteredLocations.value.length / pageSize),
  ),
)

const locations = computed(() => {
  const start = (currentPage.value - 1) * pageSize

  return filteredLocations.value.slice(
    start,
    start + pageSize,
  )
})

watch(search, () => {
  currentPage.value = 1
})

watch(totalPages, (lastPage) => {
  if (currentPage.value > lastPage) {
    currentPage.value = lastPage
  }
})

function handlePageChange(page: number) {
  currentPage.value = page
}

function handleSearch() {
  currentPage.value = 1
}

// --------------------------------------------------
// QUERY ERROR
// --------------------------------------------------

watch(error, (queryError) => {
  if (queryError) {
    showError(
      queryError instanceof Error
        ? queryError.message
        : 'Unable to load campus locations.',
    )
  }
})

// --------------------------------------------------
// MUTATIONS
// --------------------------------------------------

const createLocation = useCreateCampusLocation()
const updateLocation = useUpdateCampusLocation()
const deleteLocation = useDeleteCampusLocation()
const deactivateLocation = useDeactivateCampusLocation()

const saving = computed(
  () =>
    createLocation.isPending.value ||
    updateLocation.isPending.value,
)

const processing = computed(
  () =>
    saving.value ||
    deleteLocation.isPending.value ||
    deactivateLocation.isPending.value,
)

// --------------------------------------------------
// MODAL
// --------------------------------------------------

const modalTitle = computed(() =>
  editingId.value === null
    ? 'Create Campus Location'
    : 'Edit Campus Location',
)

function openCreateModal() {
  editingId.value = null

  formData.value = getEmptyCampusLocationForm()

  clearMessages()
  showModal.value = true
}

function openEditModal(location: CampusLocation) {
  editingId.value = location.id

  formData.value = {
    name: location.name,
    building: location.building ?? '',
    description: location.description ?? '',
    is_active: location.is_active,
  }

  clearMessages()
  showModal.value = true
}

function closeModal() {
  if (saving.value) return

  showModal.value = false
  editingId.value = null

  formData.value = getEmptyCampusLocationForm()
}

// --------------------------------------------------
// CREATE / UPDATE
// --------------------------------------------------

async function submitForm(data: CampusLocationFormData) {
  clearError()

  const name = data.name.trim()
  const building = data.building.trim()
  const description = data.description.trim()

  if (!name || !building) {
    showError('Please complete all required fields.')
    return
  }

  if (name.length > 150 || building.length > 150) {
    showError(
      'Location name and building cannot exceed 150 characters.',
    )
    return
  }

  const payload: CampusLocationFormData = {
    name,
    building,
    description,
    is_active: data.is_active,
  }

  try {
    if (editingId.value !== null) {
      await updateLocation.mutateAsync({
        id: editingId.value,
        data: payload,
      })

      showSuccess('Campus location updated successfully.')
    } else {
      await createLocation.mutateAsync(payload)

      showSuccess('Campus location created successfully.')
    }

    showModal.value = false
    editingId.value = null
    formData.value = getEmptyCampusLocationForm()
    currentPage.value = 1
  } catch (error: unknown) {
    console.error('Failed to save campus location:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to save campus location. Please try again.',
    )
  }
}

// --------------------------------------------------
// DELETE
// --------------------------------------------------

async function handleDelete(location: CampusLocation) {
  const confirmed = window.confirm(
    `Are you sure you want to delete "${location.name}"?`,
  )

  if (!confirmed) return

  clearMessages()

  try {
    await deleteLocation.mutateAsync(location.id)

    showSuccess('Campus location deleted successfully.')
  } catch (error: unknown) {
    console.error('Failed to delete campus location:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to delete campus location.',
    )
  }
}

// --------------------------------------------------
// DEACTIVATE
// --------------------------------------------------

async function handleDeactivate(location: CampusLocation) {
  const confirmed = window.confirm(
    `Are you sure you want to deactivate "${location.name}"?`,
  )

  if (!confirmed) return

  clearMessages()

  try {
    await deactivateLocation.mutateAsync(location.id)

    showSuccess('Campus location deactivated successfully.')
  } catch (error: unknown) {
    console.error('Failed to deactivate campus location:', error)

    showError(
      error instanceof Error
        ? error.message
        : 'Unable to deactivate campus location.',
    )
  }
}
</script>

<template>
  <section class="campus-locations-page space-y-6">
    
    <!-- Page Heading -->
    <header class="page-header">
      <div>
        <p class="eyebrow">ADMINISTRATION</p>
        <h1 id="campus-locations-title">
          Campus Locations
        </h1>
      </div>
    </header>

    <!-- Page Messages -->
    <BasePageMessage />

    <!-- Statistics -->
    <section class="stats-grid">
      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Total Locations</p>
            <h2>{{ totalLocations }}</h2>
          </div>

          <div class="stat-icon total-icon">
            <MapPin :size="23" />
          </div>
        </div>
      </BaseCard>

      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Active Locations</p>
            <h2>{{ activeLocations }}</h2>
          </div>

          <div class="stat-icon active-icon">
            <CheckCircle :size="23" />
          </div>
        </div>
      </BaseCard>

      <BaseCard padded class="stat-card">
        <div class="stat-content">
          <div>
            <p>Inactive Locations</p>
            <h2 class="count-number">
              {{ inactiveLocations }}
            </h2>
          </div>

          <div class="stat-icon inactive-icon">
            <Building2 :size="23" />
          </div>
        </div>
      </BaseCard>
    </section>

    <!-- Campus Location List -->
    <BaseCard padded class="campus-list-card">
      <div class="list-header">
        <div class="list-heading">
          <h2>Campus Location List</h2>
          <p>
            View and manage all campus locations.
          </p>
        </div>

        <BaseButton
          type="button"
          class="add-location-button"
          :disabled="processing"
          @click="openCreateModal"
        >
          <Plus :size="18" />
          Add Location
        </BaseButton>
      </div>

      <!-- Search -->
      <form
        class="search-form"
        @submit.prevent="handleSearch"
      >
        <div class="search-input-wrapper">
          <Search :size="17" />

          <input
            v-model="search"
            type="search"
            placeholder="Search location or building..."
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

      <!-- Table -->
      <CampusLocationTable
        :locations="locations"
        :loading="loading"
        :current-page="currentPage"
        :last-page="totalPages"
        :total="filteredLocations.length"
        @edit="openEditModal"
        @delete="handleDelete"
        @deactivate="handleDeactivate"
        @page="handlePageChange"
      />
    </BaseCard>

    <!-- Global Modal -->
    <BaseModal
      v-model="showModal"
      :title="modalTitle"
      @close="closeModal"
    >
      <CampusLocationForm
        :initial-data="formData"
        :is-submitting="saving"
        @submit="submitForm"
        @cancel="closeModal"
      />
    </BaseModal>
  </section>
</template>

<style scoped>
.campus-locations-page {
  width: 100%;
  min-width: 0;
  color: #302744;
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

.page-header h1 {
  margin: 0;
  font-size: clamp(28px, 2.7vw, 36px);
}

@media (max-width: 760px) {
  .page-header {
    align-items: flex-start;
  }
}

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

.campus-list-card {
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

.add-location-button {
  flex-shrink: 0;
  white-space: nowrap;
}

.search-form {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 9px;
  margin-bottom: 19px;
}

.search-input-wrapper {
  display: flex;
  width: 280px;
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

@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .heading-content h1 {
    font-size: 22px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .list-header {
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .add-location-button {
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
