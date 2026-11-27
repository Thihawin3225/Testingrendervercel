<script setup lang="ts">
import {
  Pencil,
  Trash2,
  Power,
  MapPin,
} from '@lucide/vue'

import BaseButton from '@/components/ui/BaseButton.vue'
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue'

import type {
  CampusLocation,
} from '../interfaces/campusLocation.interface'

defineProps<{
  locations: CampusLocation[]
  loading: boolean
  currentPage: number
  lastPage: number
  total: number
}>()

const emit = defineEmits<{
  edit: [location: CampusLocation]
  delete: [location: CampusLocation]
  deactivate: [location: CampusLocation]
  page: [page: number]
}>()

function previousPage(currentPage: number) {
  if (currentPage > 1) {
    emit('page', currentPage - 1)
  }
}

function nextPage(currentPage: number, lastPage: number) {
  if (currentPage < lastPage) {
    emit('page', currentPage + 1)
  }
}
</script>

<template>
  <div class="location-table-wrapper">
    <div v-if="loading" class="table-loading">
      <div class="loading-spinner" />
      <p>Loading campus locations...</p>
    </div>

    <template v-else>
      <div v-if="locations.length > 0" class="table-scroll">
        <table class="location-table">
          <thead>
            <tr>
              <th class="number-column">No.</th>
              <th>Location Name</th>
              <th>Building</th>
              <th>Description</th>
              <th>Status</th>
              <th class="actions-column">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(location, index) in locations"
              :key="location.id"
            >
              <td class="number-cell">
                {{ (currentPage - 1) * 10 + index + 1 }}
              </td>

              <td>
                <div class="location-name">
                  <div class="location-icon">
                    <MapPin :size="16" />
                  </div>

                  <span>
                    {{ location.name }}
                  </span>
                </div>
              </td>

              <td>
                <span class="building-text">
                  {{ location.building || '-' }}
                </span>
              </td>

              <td>
                <span class="description-text">
                  {{ location.description || '-' }}
                </span>
              </td>

              <td>
                <span
                  class="status-badge"
                  :class="location.is_active ? 'active' : 'inactive'"
                >
                  <span class="status-dot" />
                  {{ location.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>

              <td>
                <div class="action-buttons">
                  <button
                    type="button"
                    class="action-button edit-button"
                    title="Edit location"
                    @click="emit('edit', location)"
                  >
                    <Pencil :size="16" />
                  </button>

                  <button
                    v-if="location.is_active"
                    type="button"
                    class="action-button deactivate-button"
                    title="Deactivate location"
                    @click="emit('deactivate', location)"
                  >
                    <Power :size="16" />
                  </button>

                  <button
                    type="button"
                    class="action-button delete-button"
                    title="Delete location"
                    @click="emit('delete', location)"
                  >
                    <Trash2 :size="16" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <BaseEmptyState
        v-else
        title="No campus locations found"
        description="There are no campus locations to display."
      />

      <!-- Pagination -->
      <div v-if="total > 0" class="pagination-wrapper">
        <p class="pagination-info">
          Showing
          <strong>{{ (currentPage - 1) * 10 + 1 }}</strong>
          to
          <strong>{{ Math.min(currentPage * 10, total) }}</strong>
          of
          <strong>{{ total }}</strong>
          locations
        </p>

        <div class="pagination-actions">
          <button
            type="button"
            class="pagination-button"
            :disabled="currentPage <= 1"
            @click="previousPage(currentPage)"
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
            @click="nextPage(currentPage, lastPage)"
          >
            Next
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.location-table-wrapper {
  width: 100%;
  min-width: 0;
}

.table-scroll {
  width: 100%;
  overflow-x: auto;
}

.location-table {
  width: 100%;
  min-width: 850px;
  border-collapse: collapse;
  text-align: left;
}

.location-table thead {
  background: #f8f6fb;
}

.location-table th {
  padding: 13px 14px;
  color: #817991;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
  border-bottom: 1px solid #eeeaf3;
}

.location-table td {
  padding: 14px;
  color: #514963;
  font-size: 12px;
  border-bottom: 1px solid #f0edf4;
  vertical-align: middle;
}

.location-table tbody tr {
  transition: background 0.15s;
}

.location-table tbody tr:hover {
  background: #fcfbfe;
}

.number-column {
  width: 55px;
}

.number-cell {
  color: #9991a8 !important;
}

.location-name {
  display: flex;
  align-items: center;
  gap: 9px;
  min-width: 160px;
  color: #302744;
  font-weight: 650;
}

.location-icon {
  display: grid;
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  place-items: center;
  border-radius: 8px;
  background: #f2ecff;
  color: #8233f4;
}

.building-text {
  color: #514963;
  white-space: nowrap;
}

.description-text {
  display: block;
  max-width: 210px;
  overflow: hidden;
  color: #8a849b;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 9px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 650;
  white-space: nowrap;
}

.status-badge.active {
  background: #e8f8ef;
  color: #16845b;
}

.status-badge.inactive {
  background: #f1eff5;
  color: #817993;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.actions-column {
  width: 130px;
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

.deactivate-button {
  border-color: #f3e5c8;
  background: #fff8e9;
  color: #bd8423;
}

.deactivate-button:hover {
  background: #bd8423;
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
