<script setup lang="ts">
import {
  Pencil,
  Trash2,
  ClipboardList,
} from '@lucide/vue'

import BaseButton from '../../../../components/ui/BaseButton.vue'
import BaseEmptyState from '../../../../components/ui/BaseEmptyState.vue'

import type {
  StudentEnrollment,
  PaginationMeta,
} from '../../interfaces/studentEnrollment.interface'

defineProps<{
  enrollments: StudentEnrollment[]
  loading: boolean
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  edit: [enrollment: StudentEnrollment]
  delete: [enrollment: StudentEnrollment]
  page: [page: number]
}>()

function getStatusClass(status: string) {
  return `status-${status}`
}
</script>

<template>
  <div class="student-enrollment-table">
    <!-- LOADING -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading student enrollments...</p>
    </div>

    <template v-else>
      <!-- EMPTY STATE -->
      <div
        v-if="enrollments.length === 0"
        class="empty-wrapper"
      >
        <BaseEmptyState
          title="No Student Enrollments Found"
          description="There are currently no student enrollments to display."
        >
          <template #icon>
            <ClipboardList :size="36" />
          </template>
        </BaseEmptyState>
      </div>

      <template v-else>
        <!-- TABLE -->
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Student</th>
                <th>Academic Year</th>
                <th>Year Level</th>
                <th>Major</th>
                <th>Semester</th>
                <th>Status</th>
                <th class="actions-heading">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="enrollment in enrollments"
                :key="enrollment.id"
              >
                <!-- STUDENT -->
                <td>
                  <div class="student-info">
                    <span class="student-name">
                      {{ enrollment.user?.name ?? '-' }}
                    </span>

                    <span class="student-email">
                      {{ enrollment.user?.email ?? '-' }}
                    </span>
                  </div>
                </td>

                <!-- ACADEMIC YEAR -->
                <td>
                  <span class="academic-year">
                    {{ enrollment.academic_year?.name ?? '-' }}
                  </span>
                </td>

                <!-- YEAR LEVEL -->
                <td>
                  <span class="year-level-badge">
                    {{ enrollment.year_level?.name ?? '-' }}
                  </span>
                </td>

                <!-- MAJOR -->
                <td>
                  <span class="major-name">
                    {{ enrollment.major?.name ?? '-' }}
                  </span>
                </td>

                <!-- SEMESTER -->
                <td>
                  <span class="semester-badge">
                    {{ enrollment.semester?.name ?? '-' }}
                  </span>
                </td>

                <!-- STATUS -->
                <td>
                  <span
                    class="status-badge"
                    :class="getStatusClass(enrollment.status)"
                  >
                    {{ enrollment.status }}
                  </span>
                </td>

                <!-- ACTIONS -->
                <td>
                  <div class="action-buttons">
                    <BaseButton
                      variant="ghost"
                      size="sm"
                      @click="emit('edit', enrollment)"
                    >
                      <Pencil :size="15" />
                      Edit
                    </BaseButton>

                    <BaseButton
                      variant="danger"
                      size="sm"
                      @click="emit('delete', enrollment)"
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

        <!-- PAGINATION -->
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
.student-enrollment-table {
  width: 100%;
}

.table-wrapper {
  width: 100%;
  overflow-x: auto;

  /* Hide scrollbar */
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.table-wrapper::-webkit-scrollbar {
  display: none;
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

.student-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.student-name {
  color: #302744;
  font-weight: 700;
}

.student-email {
  color: #9690a3;
  font-size: 11px;
}

.academic-year {
  color: #302744;
  font-weight: 650;
}

.year-level-badge {
  display: inline-flex;
  padding: 5px 11px;
  border-radius: 20px;
  background: #eaf2ff;
  color: #3972c6;
  font-size: 11px;
  font-weight: 700;
}

.major-name {
  color: #5e566f;
  font-weight: 600;
}

.semester-badge {
  display: inline-flex;
  padding: 5px 11px;
  border-radius: 20px;
  background: #f2ecff;
  color: #8233f4;
  font-size: 11px;
  font-weight: 700;
}

.status-badge {
  display: inline-flex;
  padding: 5px 11px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}

.status-active {
  background: #e5f8ef;
  color: #16845b;
}

.status-completed {
  background: #eaf2ff;
  color: #3972c6;
}

.status-withdrawn {
  background: #fff3e5;
  color: #c17a26;
}

.status-suspended {
  background: #fff0f1;
  color: #cf3f55;
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
