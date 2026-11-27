<script setup lang="ts">
import { computed } from 'vue'
import { FileText, UserRound } from '@lucide/vue'
import type { StudyMaterial } from '../interfaces/studyMaterial.interface'

const props = withDefaults(
  defineProps<{
    material: StudyMaterial
    uploaderLabel?: string
  }>(),
  { uploaderLabel: 'Shared by' },
)

const uploaderName = computed(() => props.material.uploader?.name || 'Campus learner')

function formatFileSize(size: number): string {
  if (size < 1024 * 1024) return `${Math.max(1, Math.round(size / 1024))} KB`
  return `${(size / (1024 * 1024)).toFixed(1)} MB`
}
</script>

<template>
  <article class="material-card">
    <header class="card-topline">
      <span class="file-icon" aria-hidden="true"><FileText :size="21" /></span>
      <span class="status-badge" :class="`status-badge--${material.status}`">
        {{ material.status }}
      </span>
    </header>

    <div class="card-copy">
      <h3>{{ material.title }}</h3>
      <p class="offering-label">
        {{ material.subject_offering?.subject.subject_name
          || (material.subject_offering_id ? `Subject offering #${material.subject_offering_id}` : 'Custom Material') }}
      </p>
      <p class="description">{{ material.description || 'No description provided.' }}</p>
    </div>

    <p class="uploader-line">
      <UserRound :size="14" aria-hidden="true" />
      <span>{{ uploaderLabel }} <strong>{{ uploaderName }}</strong></span>
    </p>

    <div class="file-meta">
      <span class="file-name" :title="material.original_filename">
        {{ material.original_filename }}
      </span>
      <span class="file-size">{{ formatFileSize(material.file_size) }}</span>
    </div>

    <footer class="card-actions">
      <div class="action-group">
        <slot name="primary-action" />
      </div>
      <div class="action-group action-group--end">
        <slot name="actions" />
      </div>
    </footer>
  </article>
</template>

<style scoped>
.material-card {
  display: flex;
  height: 100%;
  min-width: 0;
  min-height: 300px;
  flex-direction: column;
  gap: 14px;
  padding: 18px;
  border: 1px solid #e6e1f2;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 8px 22px #2413440a;
}

.card-topline,
.card-actions,
.action-group {
  display: flex;
  align-items: center;
}

.card-topline,
.card-actions {
  justify-content: space-between;
  gap: 10px;
}

.file-icon {
  display: grid;
  width: 43px;
  height: 43px;
  flex: none;
  place-items: center;
  border-radius: 13px;
  background: #f2ebff;
  color: #7138df;
}

.status-badge {
  padding: 5px 10px;
  border-radius: 999px;
  background: #e9f8f1;
  color: #16734f;
  font-size: 11px;
  font-weight: 750;
  text-transform: capitalize;
}

.status-badge--pending {
  background: #fff6df;
  color: #8b6410;
}

.status-badge--rejected,
.status-badge--archived {
  background: #fff0f2;
  color: #a43c4c;
}

.card-copy {
  min-width: 0;
}

.card-copy h3 {
  display: -webkit-box;
  overflow: hidden;
  margin: 0;
  color: #1b2342;
  font-size: 16px;
  line-height: 1.4;
  overflow-wrap: anywhere;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.offering-label {
  margin: 7px 0 0;
  color: #6b44c5;
  font-size: 12px;
  font-weight: 700;
}

.description {
  display: -webkit-box;
  overflow: hidden;
  margin: 9px 0 0;
  color: #716b82;
  font-size: 13px;
  line-height: 1.55;
  overflow-wrap: anywhere;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.uploader-line {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 6px;
  margin: 0;
  color: #77718b;
  font-size: 11px;
}

.uploader-line svg {
  flex: none;
  color: #8a5be0;
}

.uploader-line span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.uploader-line strong {
  color: #37304f;
}

.file-meta {
  display: flex;
  min-width: 0;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-top: auto;
  padding: 10px 0;
  border-top: 1px solid #f0edf6;
  color: #77718b;
  font-size: 11px;
}

.file-name {
  overflow: hidden;
  min-width: 0;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-size {
  flex: none;
}

.card-actions {
  flex-wrap: wrap;
  gap: 7px;
}

.action-group {
  gap: 7px;
}

.action-group--end {
  justify-content: flex-end;
}

@media (max-width: 420px) {
  .material-card {
    padding: 15px;
  }
}
</style>
