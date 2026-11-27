<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
import { Inbox, Search, Upload, X } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import PageHero from '@/components/layout/PageHero.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue'
import ClearFiltersButton from '@/components/ui/ClearFiltersButton.vue'
import MyUploadCard from '../components/MyUploadCard.vue'
import StudyMaterialEditForm from '../components/StudyMaterialEditForm.vue'
import StudyMaterialPreviewDialog from '../components/StudyMaterialPreviewDialog.vue'
import type { StudyMaterial } from '../interfaces/studyMaterial.interface'
import { useMyStudyMaterials } from '../queries/useMyStudyMaterials'
import { useDeleteStudyMaterial } from '../mutations/useDeleteStudyMaterial'
import { useUpdateStudyMaterial } from '../mutations/useUpdateStudyMaterial'
import { useSubjectOfferings } from '../../peer-learning/queries/useSubjectOfferings'
import { useDebouncedSearch } from '../utils/useDebouncedSearch'

const { searchInput, searchTerm, clearSearch } = useDebouncedSearch()
const materialType = ref<'official' | 'custom' | ''>('')
const academicYearId = ref<number | null>(null)
const yearLevelId = ref<number | null>(null)
const notice = ref('')
const selectedMaterial = ref<StudyMaterial | null>(null)
const detailsDialog = ref<HTMLDialogElement | null>(null)
const editDialog = ref<HTMLDialogElement | null>(null)
const previewDialog = ref<InstanceType<typeof StudyMaterialPreviewDialog> | null>(null)
const { data: subjectOfferingPage } = useSubjectOfferings()
const academicYearOptions = computed(() => {
  const options = new Map<number, string>()

  for (const offering of subjectOfferingPage.value?.data ?? []) {
    if (offering.academic_year.id !== null) {
      options.set(offering.academic_year.id, offering.academic_year.name ?? 'Unnamed')
    }
  }

  return [...options].map(([id, name]) => ({ id, name }))
})
const yearLevelOptions = computed(() => {
  const options = new Map<number, string>()

  for (const offering of subjectOfferingPage.value?.data ?? []) {
    if (
      offering.year_level.id !== null
      && (!academicYearId.value || offering.academic_year.id === academicYearId.value)
    ) {
      options.set(offering.year_level.id, offering.year_level.name ?? 'Unnamed')
    }
  }

  return [...options].map(([id, name]) => ({ id, name }))
})
const filters = computed(() => ({
  search: searchTerm.value || undefined,
  material_type: materialType.value || undefined,
  academic_year_id: academicYearId.value ?? undefined,
  year_level_id: yearLevelId.value ?? undefined,
  per_page: 20,
}))
const hasActiveFilters = computed(() =>
  Boolean(searchInput.value.trim() || materialType.value || academicYearId.value || yearLevelId.value),
)

const { data: uploadPage, isPending: isUploadsPending } = useMyStudyMaterials(filters)
const { mutate: updateMaterial, isPending: isUpdating } = useUpdateStudyMaterial()
const { mutate: deleteMaterial } = useDeleteStudyMaterial()

watch(materialType, (type) => {
  if (type !== 'official') {
    academicYearId.value = null
    yearLevelId.value = null
  }
})

watch(academicYearId, () => {
  yearLevelId.value = null
})

function clearFilters(): void {
  clearSearch()
  materialType.value = ''
  academicYearId.value = null
  yearLevelId.value = null
}

async function showDetails(material: StudyMaterial): Promise<void> {
  selectedMaterial.value = material
  await nextTick()
  detailsDialog.value?.showModal()
}

function closeDetails(): void {
  detailsDialog.value?.close()
}

async function openEdit(material: StudyMaterial): Promise<void> {
  selectedMaterial.value = material
  await nextTick()
  editDialog.value?.showModal()
}

function closeEdit(): void {
  editDialog.value?.close()
}

function openPreview(material: StudyMaterial): void {
  void previewDialog.value?.open(material)
}

function submitEdit(payload: Parameters<typeof updateMaterial>[0]): void {
  updateMaterial(payload, {
    onSuccess: () => {
      closeEdit()
      notice.value = 'Study material updated successfully.'
    },
    onError: () => {
      notice.value = 'Study material could not be updated. Please try again.'
    },
  })
}

function deleteUpload(material: StudyMaterial): void {
  if (!window.confirm(`Delete "${material.title}"? This cannot be undone.`)) return

  deleteMaterial(material.id, {
    onSuccess: () => {
      notice.value = 'Study material deleted successfully.'
    },
    onError: () => {
      notice.value = 'Study material could not be deleted. Please try again.'
    },
  })
}

function formatDate(value: string | null): string {
  if (!value) return 'Date unavailable'
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value))
}

function formatFileSize(size: number): string {
  if (size < 1024 * 1024) return `${Math.max(1, Math.round(size / 1024))} KB`
  return `${(size / (1024 * 1024)).toFixed(1)} MB`
}
</script>

<template>
  <section class="my-uploads-page" aria-labelledby="my-uploads-title">
    <PageHero
      eyebrow="STUDY MATERIALS"
      title="My Study Uploads"
      title-id="my-uploads-title"
      description="Manage the learning resources you have shared with your campus community."
      :icon="Upload"
    />

    <BaseCard as="section" padded class="uploads-panel" aria-labelledby="uploads-list-title">
      <div class="panel-heading">
        <div class="heading-icon" aria-hidden="true"><Upload :size="20" /></div>
        <div class="heading-copy">
          <h2 id="uploads-list-title">My uploads</h2>
          <p>{{ uploadPage?.meta.total ?? 0 }} resources</p>
        </div>
        <RouterLink
          class="upload-link"
          :to="{ name: 'study-materials', query: { upload: '1' } }"
        >
          <Upload :size="16" aria-hidden="true" />
          Upload Material
        </RouterLink>
      </div>

      <div class="uploads-toolbar">
        <label class="search-field">
          <Search :size="17" aria-hidden="true" />
          <span class="sr-only">Search your uploads</span>
          <input v-model="searchInput" type="search" placeholder="Search titles or filenames..." />
        </label>
        <div class="material-filters">
          <label class="filter-field">
            <span class="sr-only">Material type</span>
            <select v-model="materialType">
              <option value="">All materials</option>
              <option value="official">Official subjects</option>
              <option value="custom">Custom materials</option>
            </select>
          </label>
          <label class="filter-field">
            <span class="sr-only">Academic year</span>
            <select
              v-model="academicYearId"
              :disabled="materialType === 'custom' || !academicYearOptions.length"
            >
              <option :value="null">All years</option>
              <option v-for="option in academicYearOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </label>
          <label class="filter-field">
            <span class="sr-only">Year level</span>
            <select
              v-model="yearLevelId"
              :disabled="materialType === 'custom' || !academicYearId || !yearLevelOptions.length"
            >
              <option :value="null">All levels</option>
              <option v-for="option in yearLevelOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </label>
          <ClearFiltersButton :disabled="!hasActiveFilters" @clear="clearFilters" />
        </div>
        <span class="result-count">{{ uploadPage?.data.length ?? 0 }} shown</span>
      </div>

      <p v-if="notice" class="action-notice" role="status">{{ notice }}</p>

      <BaseEmptyState
        v-if="isUploadsPending || !uploadPage?.data.length"
        :title="isUploadsPending ? 'Loading uploads...' : hasActiveFilters ? 'No matching uploads' : 'No uploads yet'"
        :description="hasActiveFilters
          ? 'Try different filters or clear them to see all your uploads.'
          : 'Upload a study resource and it will appear here.'"
      >
        <template #icon><Inbox :size="38" /></template>
      </BaseEmptyState>

      <div v-else class="uploads-grid">
        <MyUploadCard
          v-for="material in uploadPage.data"
          :key="material.id"
          :material="material"
          @view="showDetails"
          @preview="openPreview"
          @edit="openEdit"
          @delete="deleteUpload"
        />
      </div>
    </BaseCard>

    <dialog
      ref="detailsDialog"
      class="details-dialog"
      aria-labelledby="upload-details-title"
      @cancel.prevent
    >
      <section v-if="selectedMaterial" class="details-shell">
        <header class="details-heading">
          <div>
            <p class="eyebrow">UPLOAD DETAILS</p>
            <h2 id="upload-details-title">{{ selectedMaterial.title }}</h2>
          </div>
          <button class="close-button" type="button" aria-label="Close details" @click="closeDetails">
            <X :size="19" aria-hidden="true" />
          </button>
        </header>
        <div class="details-content">
          <p class="details-description">
            {{ selectedMaterial.description || 'No description provided.' }}
          </p>
          <dl class="details-list">
            <div>
              <dt>Subject</dt>
              <dd>
                {{ selectedMaterial.subject_offering?.subject.subject_name
                  || (selectedMaterial.subject_offering_id ? `Offering #${selectedMaterial.subject_offering_id}` : 'Custom Material') }}
              </dd>
            </div>
            <div><dt>Filename</dt><dd>{{ selectedMaterial.original_filename }}</dd></div>
            <div><dt>File type</dt><dd>{{ selectedMaterial.file_type.toUpperCase() }}</dd></div>
            <div><dt>File size</dt><dd>{{ formatFileSize(selectedMaterial.file_size) }}</dd></div>
            <div><dt>Status</dt><dd class="detail-status">{{ selectedMaterial.status }}</dd></div>
            <div><dt>Uploaded</dt><dd>{{ formatDate(selectedMaterial.created_at) }}</dd></div>
          </dl>
        </div>
      </section>
    </dialog>

    <dialog
      ref="editDialog"
      class="details-dialog"
      aria-labelledby="edit-upload-title"
      @cancel.prevent
    >
      <section v-if="selectedMaterial" class="details-shell">
        <StudyMaterialEditForm
          :material="selectedMaterial"
          :submitting="isUpdating"
          @submit="submitEdit"
          @cancel="closeEdit"
        />
      </section>
    </dialog>
    <StudyMaterialPreviewDialog ref="previewDialog" />
  </section>
</template>

<style scoped>
.my-uploads-page {
  display: grid;
  gap: 17px;
  color: var(--text);
}

.eyebrow {
  margin: 0 0 7px;
  color: #6a43cc;
  font-size: 11px;
  font-weight: 850;
  letter-spacing: 0.13em;
}

.preview-notice,
.action-notice {
  margin: 0;
  padding: 11px 14px;
  border: 1px solid #ded4ff;
  border-radius: 11px;
  background: #f8f5ff;
  color: #5936a8;
  font-size: 12px;
  line-height: 1.5;
}

.uploads-panel {
  display: grid;
  gap: 20px;
}

.panel-heading {
  display: flex;
  align-items: center;
  gap: 13px;
  min-width: 0;
}

.heading-icon {
  display: grid;
  width: 45px;
  height: 45px;
  flex: none;
  place-items: center;
  border-radius: 13px;
  background: #f1eaff;
  color: #7039dd;
}

.heading-copy {
  min-width: 0;
}

.heading-copy h2 {
  margin: 0;
  color: var(--text);
  font-size: 18px;
}

.heading-copy p {
  margin: 5px 0 0;
  color: var(--muted);
  font-size: 12px;
}

.upload-link {
  display: inline-flex;
  min-height: 42px;
  align-items: center;
  justify-content: center;
  gap: 7px;
  margin-left: auto;
  padding: 0 14px;
  border-radius: 10px;
  background: linear-gradient(115deg, #8233f4, #5727ce);
  color: #fff;
  font-size: 13px;
  font-weight: 750;
  text-decoration: none;
  white-space: nowrap;
}

.upload-link:hover {
  filter: brightness(1.07);
}

.upload-link:focus-visible,
.close-button:focus-visible {
  outline: 2px solid #6c2dea;
  outline-offset: 2px;
}

.uploads-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;
}

.material-filters {
  display: flex;
  flex-wrap: wrap;
  flex: none;
  align-items: center;
  gap: 8px;
}

.filter-field {
  display: grid;
  min-width: 125px;
  gap: 4px;
}

.filter-field select {
  min-height: 36px;
  padding: 6px 9px;
  border: 1px solid #dcd8ef;
  border-radius: 8px;
  background: #fff;
  color: var(--text);
  font: inherit;
  font-size: 12px;
}

.filter-field select:focus-visible {
  outline: 2px solid #6c2dea;
  outline-offset: 2px;
}

.filter-field select:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}

.search-field {
  display: flex;
  width: min(280px, 100%);
  min-width: 0;
  min-height: 36px;
  align-items: center;
  gap: 9px;
  padding: 0 12px;
  border: 1px solid #ded7f2;
  border-radius: 8px;
  background: #fff;
  color: #817998;
}

.search-field:focus-within {
  border-color: #966cf0;
  box-shadow: 0 0 0 3px #966cf01a;
}

.search-field input {
  width: 100%;
  min-width: 0;
  border: 0;
  outline: 0;
  background: transparent;
  color: var(--text);
  font: inherit;
  font-size: 13px;
}

.result-count {
  flex: none;
  margin-left: auto;
  color: var(--muted);
  font-size: 12px;
}

.uploads-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 290px), 1fr));
  align-items: stretch;
  gap: 15px;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.details-dialog {
  position: fixed;
  inset: 0;
  width: min(560px, calc(100vw - 28px));
  max-width: none;
  max-height: calc(100dvh - 28px);
  margin: auto;
  padding: 0;
  overflow: auto;
  border: 1px solid #e7e0f5;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 24px 80px #21134230;
}

.details-dialog::backdrop {
  background: #17112b75;
  backdrop-filter: blur(6px);
}

.details-shell {
  overflow: hidden;
}

.details-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  padding: 20px 22px;
  background: linear-gradient(105deg, #f3edff, #e6ddff);
}

.details-heading h2 {
  margin: 0;
  color: #172244;
  font-size: 21px;
  overflow-wrap: anywhere;
}

.close-button {
  display: grid;
  width: 36px;
  height: 36px;
  flex: none;
  place-items: center;
  border: 0;
  border-radius: 10px;
  background: #fff;
  color: #6335c5;
  cursor: pointer;
}

.details-content {
  padding: 22px;
}

.details-description {
  margin: 0 0 18px;
  color: #6f6981;
  line-height: 1.6;
}

.details-list {
  display: grid;
  gap: 0;
  margin: 0;
}

.details-list > div {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 11px 0;
  border-top: 1px solid #f0edf6;
}

.details-list dt {
  color: #77718b;
  font-size: 12px;
}

.details-list dd {
  margin: 0;
  color: #292345;
  font-size: 12px;
  font-weight: 700;
  text-align: right;
  overflow-wrap: anywhere;
}

.detail-status {
  color: #16734f !important;
  text-transform: capitalize;
}

@media (max-width: 560px) {
  .panel-heading {
    flex-wrap: wrap;
  }

  .heading-copy {
    flex: 1;
  }

  .upload-link {
    width: 100%;
    margin: 4px 0 0;
  }

  .uploads-toolbar {
    align-items: flex-start;
    flex-direction: column;
  }

  .search-field {
    width: 100%;
  }

  .material-filters {
    width: 100%;
  }

  .result-count {
    margin-left: 0;
  }
}
</style>
