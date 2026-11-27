
<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import {
  Plus,
  Tags,
  Package,
  Layers,
} from '@lucide/vue'

import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseModal from '@/components/ui/BaseModal.vue'

import MarketplaceCategoryFilters from '../components/MarketplaceCategoryFilters.vue'
import MarketplaceCategoryTable from '../components/MarketplaceCategoryTable.vue'
import MarketplaceCategoryForm from '../components/MarketplaceCategoryForm.vue'

import { useMarketplaceCategories } from '../composables/useMarketplaceCategories'

import { useCreateMarketplaceCategory } from '../mutations/useCreateMarketplaceCategory'
import { useUpdateMarketplaceCategory } from '../mutations/useUpdateMarketplaceCategory'
import { useDeleteMarketplaceCategory } from '../mutations/useDeleteMarketplaceCategory'

import type {
  MarketplaceCategory,
  MarketplaceCategoryFormData,
} from '../interfaces/marketplaceCategory.interface'

import {
  getEmptyMarketplaceCategoryForm,
} from '../utils/marketplaceCategory.helpers'

import {
  MARKETPLACE_CATEGORY_PAGE_SIZE,
} from '../utils/marketplaceCategory.constants'

// --------------------------------------------------
// State
// --------------------------------------------------

const search = ref('')
const currentPage = ref(1)
const pageSize = MARKETPLACE_CATEGORY_PAGE_SIZE

const showModal = ref(false)
const showDeleteModal = ref(false)

const editingCategory = ref<MarketplaceCategory | null>(null)
const deletingCategory = ref<MarketplaceCategory | null>(null)

const formData = ref<MarketplaceCategoryFormData>(
  getEmptyMarketplaceCategoryForm(),
)

const pageMessage = ref('')
const messageType = ref<'success' | 'error'>('success')

// --------------------------------------------------
// Queries
// --------------------------------------------------

const {
  data: categoriesResponse,
  isLoading,
  error: queryError,
  refetch,
} = useMarketplaceCategories()

const categories = computed<MarketplaceCategory[]>(() => {
  return categoriesResponse.value?.data ?? []
})

// --------------------------------------------------
// Mutations
// --------------------------------------------------

const createCategory = useCreateMarketplaceCategory()
const updateCategory = useUpdateMarketplaceCategory()
const deleteCategory = useDeleteMarketplaceCategory()

const isSubmitting = computed(() => {
  return createCategory.isPending.value ||
    updateCategory.isPending.value
})

const isDeleting = computed(() => {
  return deleteCategory.isPending.value
})

// --------------------------------------------------
// Search and pagination
// --------------------------------------------------

const filteredCategories = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  if (!keyword) {
    return categories.value
  }

  return categories.value.filter((category) => {
    return (
      category.name.toLowerCase().includes(keyword) ||
      (category.description ?? '').toLowerCase().includes(keyword)
    )
  })
})

const totalCategories = computed(() => categories.value.length)

const totalListings = computed(() => {
  return categories.value.reduce(
    (total, category) => total + (category.listings_count ?? 0),
    0,
  )
})

const lastPage = computed(() => {
  return Math.max(1, Math.ceil(filteredCategories.value.length / pageSize))
})

const paginatedCategories = computed(() => {
  const start = (currentPage.value - 1) * pageSize

  return filteredCategories.value.slice(
    start,
    start + pageSize,
  )
})

watch(search, () => {
  currentPage.value = 1
})

watch(lastPage, (newLastPage) => {
  if (currentPage.value > newLastPage) {
    currentPage.value = newLastPage
  }
})

// --------------------------------------------------
// Page message
// --------------------------------------------------

function showMessage(
  message: string,
  type: 'success' | 'error' = 'success',
) {
  pageMessage.value = message
  messageType.value = type
}

function clearMessage() {
  pageMessage.value = ''
}

// --------------------------------------------------
// Create category
// --------------------------------------------------

function openCreateModal() {
  editingCategory.value = null
  formData.value = getEmptyMarketplaceCategoryForm()
  clearMessage()
  showModal.value = true
}

// --------------------------------------------------
// Edit category
// --------------------------------------------------

function openEditModal(category: MarketplaceCategory) {
  editingCategory.value = category

  formData.value = {
    name: category.name,
    description: category.description ?? '',
  }

  clearMessage()
  showModal.value = true
}

// --------------------------------------------------
// Submit form
// --------------------------------------------------

async function handleSubmit(data: MarketplaceCategoryFormData) {
  try {
    clearMessage()

    if (editingCategory.value) {
      await updateCategory.mutateAsync({
        id: editingCategory.value.id,
        data,
      })

      showMessage('Marketplace category updated successfully.')
    } else {
      await createCategory.mutateAsync(data)

      showMessage('Marketplace category created successfully.')
    }

    showModal.value = false
    editingCategory.value = null
    formData.value = getEmptyMarketplaceCategoryForm()

    await refetch()
  } catch (error: unknown) {
    const message =
      error instanceof Error
        ? error.message
        : 'Something went wrong. Please try again.'

    showMessage(message, 'error')
  }
}

// --------------------------------------------------
// Delete category
// --------------------------------------------------

function openDeleteModal(category: MarketplaceCategory) {
  deletingCategory.value = category
  showDeleteModal.value = true
}

async function handleDelete() {
  if (!deletingCategory.value) return

  try {
    clearMessage()

    await deleteCategory.mutateAsync(deletingCategory.value.id)

    showDeleteModal.value = false
    deletingCategory.value = null

    showMessage('Marketplace category deleted successfully.')

    await refetch()
  } catch (error: unknown) {
    const message =
      error instanceof Error
        ? error.message
        : 'Unable to delete this category.'

    showDeleteModal.value = false
    showMessage(message, 'error')
  }
}

// --------------------------------------------------
// Pagination
// --------------------------------------------------

function handlePageChange(page: number) {
  currentPage.value = page
}

// --------------------------------------------------
// Query error
// --------------------------------------------------

watch(
  queryError,
  (error) => {
    if (error) {
      showMessage(
        'Failed to load marketplace categories. Please try again.',
        'error',
      )
    }
  },
)
</script>


<template>
  <div class="marketplace-category-page">

    <!-- Page Heading -->
    <header class="page-header">
      <div class="page-heading-content">
        <div>
          <p class="eyebrow">ADMINISTRATION</p>
          <h1 id="marketplace-categories-title">
            Marketplace Categories
          </h1>
        </div>
      </div>

      <BaseButton
        variant="primary"
        :disabled="isSubmitting || isDeleting"
        @click="openCreateModal"
      >
        <Plus :size="18" />
        Add Category
      </BaseButton>
    </header>

    <!-- Page Message -->
    <div
      v-if="pageMessage"
      class="page-message"
      :class="messageType"
    >
      <span>{{ pageMessage }}</span>

      <button
        type="button"
        class="message-close"
        @click="clearMessage"
      >
        ×
      </button>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">

      <BaseCard class="stat-card">
        <div class="stat-content">
          <div>
            <p class="stat-label">Total Categories</p>
            <h2 class="stat-value">
              {{ totalCategories }}
            </h2>
          </div>

          <div class="stat-icon purple">
            <Tags :size="22" />
          </div>
        </div>
      </BaseCard>

      <BaseCard class="stat-card">
        <div class="stat-content">
          <div>
            <p class="stat-label">Total Listings</p>
            <h2 class="stat-value">
              {{ totalListings }}
            </h2>
          </div>

          <div class="stat-icon blue">
            <Package :size="22" />
          </div>
        </div>
      </BaseCard>

      <BaseCard class="stat-card">
        <div class="stat-content">
          <div>
            <p class="stat-label">Categories With Listings</p>
            <h2 class="stat-value">
              {{
                categories.filter(
                  (category) => (category.listings_count ?? 0) > 0,
                ).length
              }}
            </h2>
          </div>

          <div class="stat-icon lavender">
            <Layers :size="22" />
          </div>
        </div>
      </BaseCard>

    </div>

    <!-- Category List -->
    <BaseCard class="category-list-card">

      <div class="list-header">
        <div>
          <h2 class="section-title">
            Category List
          </h2>

          <p class="section-description">
            View, create, edit, and manage marketplace categories.
          </p>
        </div>

        <span class="total-badge">
          {{ filteredCategories.length }} Categories
        </span>
      </div>

      <!-- Filters -->
      <div class="filters-wrapper">
        <MarketplaceCategoryFilters
          v-model="search"
        />
      </div>

      <!-- Table -->
      <MarketplaceCategoryTable
        :categories="paginatedCategories"
        :loading="isLoading"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="filteredCategories.length"
        @edit="openEditModal"
        @delete="openDeleteModal"
        @page="handlePageChange"
      />

    </BaseCard>

    <!-- Create / Edit Modal -->
    <BaseModal
      v-model="showModal"
      :title="
        editingCategory
          ? 'Edit Marketplace Category'
          : 'Add Marketplace Category'
      "
    >
      <MarketplaceCategoryForm
        :initial-data="formData"
        :is-submitting="isSubmitting"
        @submit="handleSubmit"
        @cancel="showModal = false"
      />
    </BaseModal>

    <!-- Delete Confirmation Modal -->
    <BaseModal
      v-model="showDeleteModal"
      title="Delete Marketplace Category"
      subtitle="Please confirm before deleting this category."
    >
      <div class="delete-content">
        <p>
          Are you sure you want to delete
          <strong>{{ deletingCategory?.name }}</strong>?
        </p>

        <p class="delete-warning">
          Categories that are being used by marketplace listings
          cannot be deleted.
        </p>

        <div class="delete-actions">
          <button
            type="button"
            :disabled="isDeleting"
            class="rounded-xl border border-[#e5e0ed] px-5 py-2.5 text-sm font-semibold text-[#5d536d] transition hover:bg-[#f8f6fb] disabled:cursor-not-allowed disabled:opacity-50"
            @click="showDeleteModal = false"
          >
            Cancel
          </button>

          <BaseButton
            variant="danger"
            :disabled="isDeleting"
            @click="handleDelete"
          >
            {{ isDeleting ? 'Deleting...' : 'Delete Category' }}
          </BaseButton>
        </div>
      </div>
    </BaseModal>

  </div>
</template>

<style scoped>
.marketplace-category-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
  width: 100%;
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

/* Page Message */

.page-message {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 14px;
}

.page-message.success {
  background: #ecfdf3;
  color: #16834a;
}

.page-message.error {
  background: #fff1f1;
  color: #d13b3b;
}

.message-close {
  border: none;
  background: transparent;
  color: inherit;
  cursor: pointer;
  font-size: 20px;
}

/* Statistics */

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.stat-card {
  padding: 20px;
}

.stat-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.stat-label {
  margin: 0 0 10px;
  color: #858092;
  font-size: 13px;
  font-weight: 500;
}

.stat-value {
  margin: 0;
  color: #302744;
  font-size: 27px;
  font-weight: 700;
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 46px;
  height: 46px;
  border-radius: 13px;
}

.stat-icon.purple {
  background: #f0eaff;
  color: #7138e5;
}

.stat-icon.blue {
  background: #eaf3ff;
  color: #397bd8;
}

.stat-icon.lavender {
  background: #f5edff;
  color: #9252d8;
}

/* Category List */

.category-list-card {
  padding: 22px;
}

.list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.section-title {
  margin: 0;
  color: #302744;
  font-size: 18px;
  font-weight: 700;
}

.section-description {
  margin: 6px 0 0;
  color: #858092;
  font-size: 13px;
}

.total-badge {
  padding: 7px 12px;
  border-radius: 20px;
  background: #f0eaff;
  color: #7138e5;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.filters-wrapper {
  margin-bottom: 18px;
}

/* Delete Modal */

.delete-content {
  padding: 8px 0;
}

.delete-content p {
  margin: 0 0 12px;
  color: #302744;
  font-size: 14px;
  line-height: 1.6;
}

.delete-warning {
  color: #a16a21 !important;
  font-size: 13px !important;
}

.delete-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 24px;
}

/* Responsive */

@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .page-header {
    align-items: flex-start;
  }

  .page-title {
    font-size: 20px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .list-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .category-list-card {
    padding: 16px;
  }
}
</style>
