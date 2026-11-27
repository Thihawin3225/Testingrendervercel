
<script setup lang="ts">
import {
  Pencil,
  Trash2,
  BookOpen,
  Plus,
} from '@lucide/vue'

import BaseButton from '../../../../components/ui/BaseButton.vue'
import BaseEmptyState from '../../../../components/ui/BaseEmptyState.vue'

import type {
  Subject,
  PaginationMeta,
} from '../../interfaces/subject.interface'

import { formatSubjectDate } from '../../utils/subject.utils'

defineProps<{
  subjects: Subject[]
  loading: boolean
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  edit: [subject: Subject]
  delete: [subject: Subject]
  page: [page: number]
  addOffering: [subject: Subject]
}>()
</script>

<template>
  <div class="subject-table">
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading subjects...</p>
    </div>

    <template v-else>
      <div v-if="subjects.length === 0" class="empty-wrapper">
        <BaseEmptyState
          title="No Subjects Found"
          description="There are currently no subjects to display."
        >
          <template #icon>
            <BookOpen :size="36" />
          </template>
        </BaseEmptyState>
      </div>

      <template v-else>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th class="subject-column">Subject</th>
                <th class="code-column">Subject Code</th>
                <th class="description-column">Description</th>
                <th class="date-column">Created Date</th>
                <th class="actions-heading">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="subject in subjects"
                :key="subject.id"
              >
                <td>
                  <div class="subject-name">
                    <span class="subject-icon">
                      <BookOpen :size="17" />
                    </span>
                    <span>{{ subject.subject_name }}</span>
                  </div>
                </td>

                <td>
                  <span class="subject-code">
                    {{ subject.subject_code }}
                  </span>
                </td>

                <td>
                  <span class="description-text">
                    {{ subject.description || '—' }}
                  </span>
                </td>

                <td>
                  {{ formatSubjectDate(subject.created_at) }}
                </td>

                <td class="actions-cell">
                  <div class="action-buttons">
                    <BaseButton
                      variant="outline"
                      size="sm"
                      @click="emit('addOffering', subject)"
                    >
                      <Plus :size="15" />
                      Add Offering
                    </BaseButton>

                    <BaseButton
                      variant="ghost"
                      size="sm"
                      @click="emit('edit', subject)"
                    >
                      <Pencil :size="15" />
                      Edit
                    </BaseButton>

                    <BaseButton
                      variant="danger"
                      size="sm"
                      @click="emit('delete', subject)"
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
.subject-table {
  width: 100%;
  min-width: 0;
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

/* COLUMN WIDTHS */

.subject-column {
  width: 22%;
}

.code-column {
  width: 15%;
}

.description-column {
  width: 22%;
}

.date-column {
  width: 15%;
}

.actions-heading {
  width: 26%;
  text-align: center;
  white-space: nowrap;
}

/* TABLE HEADER */

thead {
  background: #faf8ff;
}

th {
  padding: 14px 12px;
  color: #817993;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

/* TABLE BODY */

td {
  padding: 15px 12px;
  border-top: 1px solid #f0edf6;
  color: #5e566f;
  font-size: 13px;
  white-space: nowrap;
}

tbody tr:hover {
  background: #fcfbff;
}

/* SUBJECT NAME */

.subject-name {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #302744;
  font-weight: 700;
  min-width: 0;
}

.subject-name > span:last-child {
  overflow: hidden;
  text-overflow: ellipsis;
}

.subject-icon {
  display: grid;
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 9px;
  background: #f2ecff;
  color: #8233f4;
}

/* SUBJECT CODE */

.subject-code {
  display: inline-flex;
  padding: 5px 11px;
  border-radius: 20px;
  background: #f2ecff;
  color: #8233f4;
  font-size: 11px;
  font-weight: 700;
}

/* DESCRIPTION */

.description-text {
  display: inline-block;
  max-width: 100%;
  overflow: hidden;
  color: #817993;
  text-overflow: ellipsis;
  vertical-align: middle;
}

/* ACTIONS */

.actions-cell {
  width: 26%;
  padding: 10px 6px;
  white-space: nowrap;
}

.action-buttons {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

/* PAGINATION */

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

/* LOADING */

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

/* EMPTY STATE */

.empty-wrapper {
  padding: 15px 0;
}

/* ANIMATION */

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* RESPONSIVE */

@media (max-width: 640px) {
  .table-wrapper {
    overflow-x: auto;
  }

  table {
    min-width: 1000px;
  }

  .pagination {
    align-items: flex-start;
    flex-direction: column;
  }

  .action-buttons {
    justify-content: flex-start;
  }
}
</style>
