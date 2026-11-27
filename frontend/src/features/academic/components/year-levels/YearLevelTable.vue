
<script setup lang="ts">
import {
  Pencil,
  Trash2,
  School,
} from '@lucide/vue'

import BaseButton from '../../../../components/ui/BaseButton.vue'
import BaseEmptyState from '../../../../components/ui/BaseEmptyState.vue'

import type {
  YearLevel,
  PaginationMeta,
} from '../../interfaces/yearLevel.interface'

import { formatYearLevelDate } from '../../utils/yearLevel.utils'

defineProps<{
  yearLevels: YearLevel[]
  loading: boolean
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  edit: [yearLevel: YearLevel]
  delete: [yearLevel: YearLevel]
  page: [page: number]
}>()
</script>

<template>
  <div class="year-level-table">
    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading year levels...</p>
    </div>

    <template v-else>
      <!-- Empty State -->
      <div v-if="yearLevels.length === 0" class="empty-wrapper">
        <BaseEmptyState
          title="No Year Levels Found"
          description="There are currently no year levels to display."
        >
          <template #icon>
            <School :size="36" />
          </template>
        </BaseEmptyState>
      </div>

      <!-- Table -->
      <template v-else>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Year Level</th>
                <th>Sort Order</th>
                <th>Created Date</th>
                <th class="actions-heading">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="yearLevel in yearLevels"
                :key="yearLevel.id"
              >
                <td>
                  <div class="year-level-name">
                    <span class="year-level-icon">
                      <School :size="17" />
                    </span>

                    <span>{{ yearLevel.name }}</span>
                  </div>
                </td>

                <td>
                  <span class="sort-order-badge">
                    {{ yearLevel.sort_order }}
                  </span>
                </td>

                <td>
                  {{ formatYearLevelDate(yearLevel.created_at) }}
                </td>

                <td>
                  <div class="action-buttons">
                    <BaseButton
                      variant="ghost"
                      size="sm"
                      @click="emit('edit', yearLevel)"
                    >
                      <Pencil :size="15" />
                      Edit
                    </BaseButton>

                    <BaseButton
                      variant="danger"
                      size="sm"
                      @click="emit('delete', yearLevel)"
                    >
                      <Trash2 :size="15" />
                      Delete
                    </BaseButton>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <p>
            Showing page {{ pagination.current_page }}
            of {{ pagination.last_page }}
            ({{ pagination.total }} records)
          </p>

          <div class="pagination-buttons">
            <BaseButton
              variant="outline"
              size="sm"
              :disabled="pagination.current_page <= 1 || loading"
              @click="emit('page', pagination.current_page - 1)"
            >
              Previous
            </BaseButton>

            <BaseButton
              variant="outline"
              size="sm"
              :disabled="
                pagination.current_page >= pagination.last_page ||
                loading
              "
              @click="emit('page', pagination.current_page + 1)"
            >
              Next
            </BaseButton>
          </div>
        </div>
      </template>
    </template>
  </div>
</template>

<style scoped>
.year-level-table {
  width: 100%;
}

.table-wrapper {
  width: 100%;
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

thead {
  background: #faf8ff;
}

th {
  padding: 14px 16px;
  color: #817993;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

td {
  padding: 15px 16px;
  border-top: 1px solid #f0edf6;
  color: #5e566f;
  font-size: 13px;
  white-space: nowrap;
}

tbody tr:hover {
  background: #fcfbff;
}

.year-level-name {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #302744;
  font-weight: 700;
}

.year-level-icon {
  display: grid;
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 9px;
  background: #f2ecff;
  color: #8233f4;
}

.sort-order-badge {
  display: inline-flex;
  min-width: 30px;
  justify-content: center;
  padding: 5px 10px;
  border-radius: 20px;
  background: #f2ecff;
  color: #7138e5;
  font-size: 12px;
  font-weight: 700;
}

.actions-heading {
  text-align: center;
}

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 7px;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #f0edf6;
}

.pagination p {
  margin: 0;
  color: #8a849b;
  font-size: 12px;
}

.pagination-buttons {
  display: flex;
  gap: 8px;
}

.loading-state {
  display: flex;
  min-height: 180px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #8a849b;
  font-size: 13px;
}

.loading-state p {
  margin: 0;
}

.loading-spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #eee8fa;
  border-top-color: #8233f4;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.empty-wrapper {
  padding: 15px 0;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 640px) {
  .pagination {
    align-items: flex-start;
    flex-direction: column;
  }

  .action-buttons {
    justify-content: flex-start;
  }
}
</style>
