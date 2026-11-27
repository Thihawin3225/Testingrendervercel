<script setup lang="ts">
import { Pencil, Trash2, CalendarRange } from '@lucide/vue'

import BaseButton from '../../../../components/ui/BaseButton.vue'
import BaseEmptyState from '../../../../components/ui/BaseEmptyState.vue'

import type {
  AcademicYear,
  PaginationMeta,
} from '../../interfaces/academicYear.interface'

import { formatAcademicYearDate } from '../../utils/academicYear.utils'

defineProps<{
  academicYears: AcademicYear[]
  loading: boolean
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  edit: [academicYear: AcademicYear]
  delete: [academicYear: AcademicYear]
  page: [page: number]
}>()
</script>

<template>
  <div class="academic-table">
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading academic years...</p>
    </div>

    <template v-else>
      <div v-if="academicYears.length === 0" class="empty-wrapper">
        <BaseEmptyState
          title="No Academic Years Found"
          description="There are currently no academic years to display."
        >
          <template #icon>
            <CalendarRange :size="36" />
          </template>
        </BaseEmptyState>
      </div>

      <template v-else>
        <div class="table-wrapper">
          <table>
            <colgroup>
              <col class="col-academic-year" />
              <col class="col-start-date" />
              <col class="col-end-date" />
              <col class="col-status" />
              <col class="col-actions" />
            </colgroup>

            <thead>
              <tr>
                <th>Academic Year</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th class="actions-heading">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="year in academicYears"
                :key="year.id"
              >
                <td>
                  <div class="year-name">
                    <span class="year-icon">
                      <CalendarRange :size="17" />
                    </span>
                    <span>{{ year.name }}</span>
                  </div>
                </td>

                <td>{{ formatAcademicYearDate(year.start_date) }}</td>

                <td>{{ formatAcademicYearDate(year.end_date) }}</td>

                <td>
                  <span
                    class="status-badge"
                    :class="year.is_active ? 'status-active' : 'status-inactive'"
                  >
                    {{ year.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>

                <td>
                  <div class="action-buttons">
                    <BaseButton
                      v-if="year.is_active"
                      variant="ghost"
                      size="sm"
                      @click="emit('edit', year)"
                    >
                      <Pencil :size="15" />
                      Edit
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
              :disabled="pagination.current_page <= 1"
              @click="emit('page', pagination.current_page - 1)"
            >
              Previous
            </BaseButton>

            <BaseButton
              variant="outline"
              size="sm"
              :disabled="pagination.current_page >= pagination.last_page"
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
.academic-table {
  width: 100%;
}

.table-wrapper {
  width: 100%;
  overflow-x: auto;
}

table {
  width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
  text-align: left;
}

.col-academic-year {
  width: 25%;
}

.col-start-date {
  width: 20%;
}

.col-end-date {
  width: 20%;
}

.col-status {
  width: 15%;
}

.col-actions {
  width: 20%;
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

.year-name {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #302744;
  font-weight: 700;
}

.year-icon {
  display: grid;
  width: 34px;
  height: 34px;
  place-items: center;
  border-radius: 9px;
  background: #f2ecff;
  color: #8233f4;
}

.status-badge {
  display: inline-flex;
  padding: 5px 11px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
}

.status-active {
  background: #e5f8ef;
  color: #16845b;
}

.status-inactive {
  background: #f1eff5;
  color: #817993;
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
