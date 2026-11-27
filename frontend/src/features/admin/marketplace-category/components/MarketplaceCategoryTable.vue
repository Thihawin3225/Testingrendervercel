<script setup lang="ts">
import {
  Pencil,
  Trash2,
  Tag,
} from '@lucide/vue'

import BaseEmptyState from '@/components/ui/BaseEmptyState.vue'

import type {
  MarketplaceCategory,
} from '../interfaces/marketplaceCategory.interface'

import {
  formatMarketplaceCategoryDate,
} from '../utils/marketplaceCategory.helpers'

const props = defineProps<{
  categories: MarketplaceCategory[]
  loading: boolean
  currentPage: number
  lastPage: number
  total: number
}>()

const emit = defineEmits<{
  edit: [category: MarketplaceCategory]
  delete: [category: MarketplaceCategory]
  page: [page: number]
}>()

const PAGE_SIZE = 10

function previousPage() {
  if (props.currentPage > 1) {
    emit('page', props.currentPage - 1)
  }
}

function nextPage() {
  if (props.currentPage < props.lastPage) {
    emit('page', props.currentPage + 1)
  }
}

function getRowNumber(index: number): number {
  return (props.currentPage - 1) * PAGE_SIZE + index + 1
}

function getStartNumber(): number {
  if (props.total === 0) return 0

  return (props.currentPage - 1) * PAGE_SIZE + 1
}

function getEndNumber(): number {
  return Math.min(
    props.currentPage * PAGE_SIZE,
    props.total,
  )
}
</script>

<template>
  <div class="category-table-wrapper">

    <!-- Loading -->
    <div v-if="loading" class="table-loading">
      <div class="loading-spinner" />
      <p>Loading marketplace categories...</p>
    </div>

    <template v-else>

      <!-- Table -->
      <div v-if="categories.length > 0" class="table-scroll">
        <table class="category-table">

          <thead>
            <tr>
              <th class="number-column">
                No.
              </th>

              <th>
                Category Name
              </th>

              <th>
                Description
              </th>

              <th class="listings-column">
                Listings
              </th>

              <th class="date-column">
                Created At
              </th>

              <th class="actions-column">
                Actions
              </th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(category, index) in categories"
              :key="category.id"
            >

              <!-- Number -->
              <td class="number-cell">
                {{ getRowNumber(index) }}
              </td>

              <!-- Category Name -->
              <td>
                <div class="category-name">
                  <div class="category-icon">
                    <Tag :size="16" />
                  </div>

                  <span>
                    {{ category.name }}
                  </span>
                </div>
              </td>

              <!-- Description -->
              <td>
                <span
                  class="description-text"
                  :title="category.description || ''"
                >
                  {{ category.description || '-' }}
                </span>
              </td>

              <!-- Listings -->
              <td class="listings-cell">
                <span class="listings-badge">
                  {{ category.listings_count ?? 0 }}
                </span>
              </td>

              <!-- Created At -->
              <td class="date-cell">
                {{ formatMarketplaceCategoryDate(category.created_at) }}
              </td>

              <!-- Actions -->
              <td>
                <div class="action-buttons">

                  <!-- Edit -->
                  <button
                    type="button"
                    class="action-button edit-button"
                    title="Edit category"
                    aria-label="Edit category"
                    @click="emit('edit', category)"
                  >
                    <Pencil :size="16" />
                  </button>

                  <!-- Delete -->
                  <button
                    type="button"
                    class="action-button delete-button"
                    title="Delete category"
                    aria-label="Delete category"
                    @click="emit('delete', category)"
                  >
                    <Trash2 :size="16" />
                  </button>

                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <BaseEmptyState
        v-else
        title="No marketplace categories found"
        description="There are no marketplace categories to display."
      />

      <!-- Pagination -->
      <div
        v-if="total > 0"
        class="pagination-wrapper"
      >

        <!-- Pagination Information -->
        <p class="pagination-info">
          Showing
          <strong>{{ getStartNumber() }}</strong>
          to
          <strong>{{ getEndNumber() }}</strong>
          of
          <strong>{{ total }}</strong>
          categories
        </p>

        <!-- Pagination Actions -->
        <div class="pagination-actions">

          <button
            type="button"
            class="pagination-button"
            :disabled="currentPage <= 1"
            @click="previousPage"
          >
            Previous
          </button>

          <span class="page-number">
            {{ currentPage }} / {{ lastPage }}
          </span>

          <button
            type="button"
            class="pagination-button"
            :disabled="currentPage >= lastPage"
            @click="nextPage"
          >
            Next
          </button>

        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.category-table-wrapper {
  width: 100%;
  min-width: 0;
}

.table-scroll {
  width: 100%;
  overflow-x: auto;
}

.category-table {
  width: 100%;
  min-width: 850px;
  border-collapse: collapse;
  text-align: left;
}

.category-table thead {
  background: #f8f6fb;
}

.category-table th {
  padding: 13px 14px;
  color: #817991;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
  border-bottom: 1px solid #eeeaf3;
}

.category-table td {
  padding: 14px;
  color: #514963;
  font-size: 12px;
  border-bottom: 1px solid #f0edf4;
  vertical-align: middle;
}

.category-table tbody tr {
  transition: background 0.15s;
}

.category-table tbody tr:hover {
  background: #fcfbfe;
}

.number-column {
  width: 55px;
}

.number-cell {
  color: #9991a8 !important;
}

.category-name {
  display: flex;
  align-items: center;
  gap: 9px;
  min-width: 160px;
  color: #302744;
  font-weight: 650;
}

.category-icon {
  display: grid;
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 8px;
  background: #f2ecff;
  color: #8233f4;
}

.description-text {
  display: block;
  max-width: 210px;
  overflow: hidden;
  color: #8a849b;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.listings-column {
  width: 100px;
  text-align: center;
}

.listings-cell {
  text-align: center;
}

.listings-badge {
  display: inline-flex;
  min-width: 28px;
  align-items: center;
  justify-content: center;
  padding: 5px 9px;
  border-radius: 20px;
  background: #f2ecff;
  color: #7138e5;
  font-size: 10px;
  font-weight: 650;
}

.date-column {
  width: 135px;
}

.date-cell {
  color: #8a849b !important;
  white-space: nowrap;
}

.actions-column {
  width: 110px;
  text-align: center;
}

.action-buttons {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
}

.action-button {
  display: grid;
  width: 31px;
  height: 31px;
  place-items: center;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  cursor: pointer;
  transition: 0.2s;
}

.edit-button {
  border-color: #e9ddff;
  background: #f5efff;
  color: #7138e5;
}

.edit-button:hover {
  background: #7138e5;
  color: #fff;
}

.delete-button {
  border-color: #f8dada;
  background: #fff1f1;
  color: #dc4545;
}

.delete-button:hover {
  background: #dc4545;
  color: #fff;
}

.table-loading {
  display: flex;
  min-height: 220px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #8a849b;
  font-size: 12px;
}

.loading-spinner {
  width: 30px;
  height: 30px;
  border: 3px solid #eee7fa;
  border-top-color: #8233f4;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  padding-top: 17px;
  margin-top: 5px;
}

.pagination-info {
  margin: 0;
  color: #8a849b;
  font-size: 11px;
}

.pagination-info strong {
  color: #514963;
}

.pagination-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pagination-button {
  padding: 7px 11px;
  border: 1px solid #e4deed;
  border-radius: 7px;
  background: #fff;
  color: #514963;
  font-size: 11px;
  cursor: pointer;
  transition: 0.2s;
}

.pagination-button:hover:not(:disabled) {
  border-color: #8233f4;
  color: #7138e5;
}

.pagination-button:disabled {
  cursor: not-allowed;
  opacity: 0.45;
}

.page-number {
  color: #514963;
  font-size: 11px;
  font-weight: 650;
  white-space: nowrap;
}

@media (max-width: 640px) {
  .pagination-wrapper {
    align-items: flex-start;
    flex-direction: column;
  }

  .pagination-actions {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
