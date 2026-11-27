
<script setup lang="ts">
import {
  Pencil,
  Trash2,
  GraduationCap,
} from '@lucide/vue'

import BaseButton from '../../../../components/ui/BaseButton.vue'
import BaseEmptyState from '../../../../components/ui/BaseEmptyState.vue'

import type {
  Major,
  PaginationMeta,
} from '../../interfaces/major.interface'

import { formatMajorDate } from '../../utils/major.utils'

defineProps<{
  majors: Major[]
  loading: boolean
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  edit: [major: Major]
  delete: [major: Major]
  page: [page: number]
}>()
</script>

<template>
  <div class="major-table">
    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading majors...</p>
    </div>

    <template v-else>
      <!-- Empty State -->
      <div v-if="majors.length === 0" class="empty-wrapper">
        <BaseEmptyState
          title="No Majors Found"
          description="There are currently no majors to display."
        >
          <template #icon>
            <GraduationCap :size="36" />
          </template>
        </BaseEmptyState>
      </div>

      <!-- Table -->
      <template v-else>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Major</th>
                <th>Major Code</th>
                <th>Created Date</th>
                <th class="actions-heading">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="major in majors"
                :key="major.id"
              >
                <td>
                  <div class="major-name">
                    <span class="major-icon">
                      <GraduationCap :size="17" />
                    </span>

                    <span>{{ major.name }}</span>
                  </div>
                </td>

                <td>
                  <span class="code-badge">
                    {{ major.code }}
                  </span>
                </td>

                <td>
                  {{ formatMajorDate(major.created_at) }}
                </td>

                <td>
                  <div class="action-buttons">
                    <BaseButton
                      variant="ghost"
                      size="sm"
                      @click="emit('edit', major)"
                    >
                      <Pencil :size="15" />
                      Edit
                    </BaseButton>

                    <BaseButton
                      variant="danger"
                      size="sm"
                      @click="emit('delete', major)"
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
            ({{ pagination.total }} majors)
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
.major-table {
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

.major-name {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #302744;
  font-weight: 700;
}

.major-icon {
  display: grid;
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 9px;
  background: #f2ecff;
  color: #8233f4;
}

.code-badge {
  display: inline-flex;
  align-items: center;
  padding: 5px 11px;
  border-radius: 20px;
  background: #f2ecff;
  color: #7138e5;
  font-size: 11px;
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
