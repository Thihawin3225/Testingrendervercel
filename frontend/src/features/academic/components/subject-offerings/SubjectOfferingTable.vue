<script setup lang="ts">
import {
  Pencil,
  Trash2,
  Sparkles,
} from '@lucide/vue'

import BaseButton from '../../../../components/ui/BaseButton.vue'
import BaseEmptyState from '../../../../components/ui/BaseEmptyState.vue'

import type {
  SubjectOffering,
  PaginationMeta,
} from '../../interfaces/subjectOffering.interface'

defineProps<{
  subjectOfferings: SubjectOffering[]
  loading: boolean
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  edit: [subjectOffering: SubjectOffering]
  delete: [subjectOffering: SubjectOffering]
  page: [page: number]
}>()
</script>

<template>
  <div class="subject-offering-table">

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading subject offerings...</p>
    </div>

    <template v-else>

      <!-- Empty State -->
      <div
        v-if="subjectOfferings.length === 0"
        class="empty-wrapper"
      >
        <BaseEmptyState
          title="No Subject Offerings Found"
          description="There are currently no subject offerings to display."
        >
          <template #icon>
            <Sparkles :size="36" />
          </template>
        </BaseEmptyState>
      </div>

      <template v-else>

        <!-- Table -->
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Academic Year</th>
                <th>Year Level</th>
                <th>Major</th>
                <th>Semester</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th class="actions-heading">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="offering in subjectOfferings"
                :key="offering.id"
              >
                <!-- Academic Year -->
                <td>
                  <div class="academic-year">
                    {{ offering.academic_year?.name ?? '-' }}
                  </div>
                </td>

                <!-- Year Level -->
                <td>
                  <span class="year-level-badge">
                    {{ offering.year_level?.name ?? '-' }}
                  </span>
                </td>

                <!-- Major -->
                <td>
                  <span class="major-name">
                    {{ offering.major?.name ?? '-' }}
                  </span>
                </td>

                <!-- Semester -->
                <td>
                  <span class="semester-badge">
                    {{ offering.semester?.name ?? '-' }}
                  </span>
                </td>

                <!-- Subject Code -->
                <td>
                  <span class="subject-code">
                    {{ offering.subject?.subject_code ?? '-' }}
                  </span>
                </td>

                <!-- Subject Name -->
                <td>
                  <span class="subject-name">
                    {{ offering.subject?.subject_name ?? '-' }}
                  </span>
                </td>

                <!-- Actions -->
                <td>
                  <div class="action-buttons">
                    <BaseButton
                      variant="ghost"
                      size="sm"
                      @click="emit('edit', offering)"
                    >
                      <Pencil :size="15" />
                      Edit
                    </BaseButton>

                    <BaseButton
                      variant="danger"
                      size="sm"
                      @click="emit('delete', offering)"
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
.subject-offering-table {
  width: 100%;
}

.table-wrapper {
  width: 100%;
  overflow-x: auto;

  /* Hide horizontal scrollbar */
  scrollbar-width: none;
  -ms-overflow-style: none;
}

/* Chrome, Edge and Safari */
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

.academic-year {
  color: #302744;
  font-weight: 700;
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

.subject-code {
  color: #6d4aff;
  font-family: monospace;
  font-size: 13px;
  font-weight: 700;
}

.subject-name {
  color: #302744;
  font-weight: 600;
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
