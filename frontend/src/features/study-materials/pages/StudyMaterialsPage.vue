<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { BookOpen, Download, Eye, Inbox, Search, Upload, X } from '@lucide/vue'
import PageHero from '@/components/layout/PageHero.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue'
import ClearFiltersButton from '@/components/ui/ClearFiltersButton.vue'
import StudyMaterialCard from '../components/StudyMaterialCard.vue'
import StudyMaterialBookmarkButton from '../components/StudyMaterialBookmarkButton.vue'
import StudyMaterialUploadForm from '../components/StudyMaterialUploadForm.vue'
import StudyMaterialPreviewDialog from '../components/StudyMaterialPreviewDialog.vue'
import type {
  CreateStudyMaterialPayload,
  StudyMaterial,
} from '../interfaces/studyMaterial.interface'
import { useStudyMaterials } from '../queries/useStudyMaterials'
import { useUploadStudyMaterial } from '../mutations/useUploadStudyMaterial'
import { useBookmarkStudyMaterial } from '../mutations/useBookmarkStudyMaterial'
import { useRemoveStudyMaterialBookmark } from '../mutations/useRemoveStudyMaterialBookmark'
import { downloadStudyMaterial } from '../api/studyMaterial.api'
import { useSubjectOfferings } from '../../peer-learning/queries/useSubjectOfferings'
import { getStudyMaterialApiErrorMessage } from '../utils/apiErrorMessage'
import { useDebouncedSearch } from '../utils/useDebouncedSearch'

const uploadDialog = ref<HTMLDialogElement | null>(null)
const previewDialog = ref<InstanceType<typeof StudyMaterialPreviewDialog> | null>(null)
const { searchInput, searchTerm, clearSearch } = useDebouncedSearch()
const materialType = ref<'official' | 'custom' | ''>('')
const academicYearId = ref<number | null>(null)
const yearLevelId = ref<number | null>(null)
const materialNotice = ref('')
const uploadFormKey = ref(0)
const bookmarkPendingId = ref<number | null>(null)
const route = useRoute()
const router = useRouter()
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
const { data: materialPage, isPending: isMaterialsPending, isError: isMaterialsError } =
  useStudyMaterials(filters)
const {
  mutate: uploadMaterial,
  isPending: isUploading,
  error: uploadError,
  reset: resetUpload,
} = useUploadStudyMaterial()
const { mutate: bookmarkMaterial } = useBookmarkStudyMaterial()
const { mutate: removeBookmark } = useRemoveStudyMaterialBookmark()
const uploadErrorMessage = computed(() =>
  uploadError.value
    ? getStudyMaterialApiErrorMessage(uploadError.value, 'upload the study material')
    : '',
)

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

onMounted(() => {
  if (route.query.upload === '1') openUploadDialog()
})

function openUploadDialog(): void {
  resetUpload()
  materialNotice.value = ''
  uploadFormKey.value += 1
  uploadDialog.value?.showModal()
}

function closeUploadDialog(): void {
  uploadDialog.value?.close()

  if (route.query.upload === '1') {
    void router.replace({ name: 'study-materials' })
  }
}

function handleUploadSubmit(payload: CreateStudyMaterialPayload): void {
  resetUpload()
  materialNotice.value = ''
  uploadMaterial(payload, {
    onSuccess: () => {
      materialNotice.value = 'Study material uploaded successfully.'
      uploadFormKey.value += 1
      closeUploadDialog()
    },
  })
}

function toggleBookmark(material: StudyMaterial): void {
  const wasBookmarked = material.is_bookmarked === true

  bookmarkPendingId.value = material.id
  const options = {
    onSuccess: () => {
      material.is_bookmarked = !wasBookmarked
      materialNotice.value = wasBookmarked
        ? 'Material removed from bookmarks.'
        : 'Material saved to bookmarks.'
    },
    onError: () => {
      materialNotice.value = 'Bookmark could not be updated. Please try again.'
    },
    onSettled: () => {
      bookmarkPendingId.value = null
    },
  }

  if (wasBookmarked) {
    removeBookmark(material.id, options)
    return
  }

  bookmarkMaterial(material.id, options)
}

async function downloadMaterial(id: number, filename: string): Promise<void> {
  try {
    const blob = await downloadStudyMaterial(id)
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.click()
    URL.revokeObjectURL(url)
  } catch {
    materialNotice.value = 'Material could not be downloaded. Please try again.'
  }
}

function openPreview(material: StudyMaterial): void {
  void previewDialog.value?.open(material)
}
</script>

<template>
  <section class="study-materials-page" aria-labelledby="study-materials-title">
    <PageHero
      eyebrow="SHARE WHAT YOU KNOW"
      title="Study Materials"
      title-id="study-materials-title"
      description="Browse course resources shared by your campus community."
      :icon="BookOpen"
    />

    <BaseCard as="section" padded class="materials-panel" aria-labelledby="browse-heading">
      <div class="panel-heading">
        <div class="heading-icon" aria-hidden="true">
          <BookOpen :size="20" />
        </div>
        <div class="heading-copy">
          <h2 id="browse-heading">Browse Materials</h2>
          <p>Find notes, slides, and documents for your courses.</p>
        </div>
        <BaseButton class="upload-button" @click="openUploadDialog">
          <Upload :size="15" aria-hidden="true" />
          Upload Material
        </BaseButton>
      </div>

      <div class="materials-toolbar" role="search" aria-label="Filter study materials">
        <label class="search-field">
          <Search :size="17" aria-hidden="true" />
          <span class="sr-only">Search materials</span>
          <input
            v-model="searchInput"
            type="search"
            placeholder="Search titles and filenames..."
          />
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
      </div>

      <p v-if="materialNotice" class="upload-notice" role="status">{{ materialNotice }}</p>

      <BaseEmptyState
        v-if="isMaterialsPending || isMaterialsError || !materialPage?.data.length"
        :title="isMaterialsPending
          ? 'Loading study materials...'
          : isMaterialsError
            ? 'Study materials could not be loaded'
            : 'No study materials to show yet'"
        :description="isMaterialsError
          ? 'Please try again later.'
          : 'Course resources shared by learners will appear here. You can be the first to upload one.'"
      >
        <template #icon><Inbox :size="38" /></template>
        <template #action>
          <BaseButton v-if="!isMaterialsPending" size="sm" @click="openUploadDialog">
            <Upload :size="15" aria-hidden="true" />
            Upload Material
          </BaseButton>
        </template>
      </BaseEmptyState>

      <div v-else class="materials-grid">
        <StudyMaterialCard
          v-for="material in materialPage.data"
          :key="material.id"
          :material="material"
        >
          <template #primary-action>
            <BaseButton
              variant="ghost"
              size="sm"
              :aria-label="`Preview ${material.title}`"
              @click="openPreview(material)"
            >
              <Eye :size="15" aria-hidden="true" /> Preview
            </BaseButton>
          </template>
          <template #actions>
            <StudyMaterialBookmarkButton
              :saved="material.is_bookmarked === true"
              :material-title="material.title"
              :disabled="bookmarkPendingId === material.id"
              @toggle="toggleBookmark(material)"
            />
            <BaseButton
              variant="ghost"
              size="sm"
              title="Download material"
              :aria-label="`Download ${material.title}`"
              @click="downloadMaterial(material.id, material.original_filename)"
            >
              <Download :size="15" aria-hidden="true" />
            </BaseButton>
          </template>
        </StudyMaterialCard>
      </div>
    </BaseCard>

    <StudyMaterialPreviewDialog ref="previewDialog" />

    <dialog
      ref="uploadDialog"
      class="upload-dialog"
      aria-labelledby="upload-dialog-title"
      @cancel.prevent
    >
      <section class="dialog-shell">
        <header class="dialog-heading">
          <div>
            <p class="eyebrow">STUDY MATERIALS</p>
            <h2 id="upload-dialog-title">Upload a study material</h2>
          </div>
          <BaseButton variant="ghost" aria-label="Close upload form" @click="closeUploadDialog">
            <X :size="19" aria-hidden="true" />
          </BaseButton>
        </header>

        <div class="dialog-content">
          <p v-if="uploadErrorMessage" class="upload-error" role="alert">
            {{ uploadErrorMessage }}
          </p>
          <StudyMaterialUploadForm
            :key="uploadFormKey"
            :submitting="isUploading"
            @submit="handleUploadSubmit"
            @cancel="closeUploadDialog"
          />
        </div>
      </section>
    </dialog>
  </section>
</template>

<style scoped>
.study-materials-page {
  display: grid;
  gap: 18px;
  color: var(--text);
}

.eyebrow {
  margin: 0 0 7px;
  color: #6a43cc;
  font-size: 11px;
  font-weight: 850;
  letter-spacing: 0.13em;
}

.materials-panel {
  display: grid;
  gap: 20px;
}

.upload-error {
  margin: 0;
  color: #b4233a;
  font-size: 13px;
  font-weight: 650;
}

.panel-heading {
  display: flex;
  align-items: center;
  gap: 13px;
  min-width: 0;
}

.heading-icon {
  display: grid;
  width: 46px;
  height: 46px;
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
  font-size: 19px;
}

.heading-copy p {
  margin: 5px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.upload-button {
  margin-left: auto;
  flex: none;
}

.materials-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.material-filters {
  display: flex;
  flex-wrap: wrap;
  flex: none;
  align-items: center;
  justify-content: flex-end;
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

.materials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 290px), 1fr));
  align-items: stretch;
  gap: 15px;
}

.search-field {
  display: flex;
  width: min(320px, 100%);
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

.upload-dialog {
  position: fixed;
  inset: 0;
  width: min(850px, calc(100vw - 28px));
  max-width: none;
  max-height: calc(100dvh - 28px);
  margin: auto;
  padding: 0;
  overflow: hidden;
  border: 0;
  border-radius: 20px;
  background: transparent;
}

.upload-dialog::backdrop {
  background: #17112b75;
  backdrop-filter: blur(7px);
}

.dialog-shell {
  display: grid;
  grid-template-rows: auto minmax(0, 1fr);
  max-height: calc(100dvh - 28px);
  overflow: hidden;
  border: 1px solid #e7e0f5;
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 24px 80px #21134230;
}

.dialog-heading {
  display: flex;
  min-height: 88px;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 18px 24px;
  background: linear-gradient(105deg, #f3edff, #e6ddff);
}

.dialog-heading .eyebrow {
  margin-bottom: 4px;
}

.dialog-heading h2 {
  margin: 0;
  color: #172244;
  font-size: 22px;
}

.dialog-content {
  min-height: 0;
  overflow-y: auto;
  overscroll-behavior: contain;
  padding: 24px;
}

.upload-notice {
  margin: 0 0 18px;
  padding: 11px 13px;
  border: 1px solid #ded4ff;
  border-radius: 10px;
  background: #f8f5ff;
  color: #5936a8;
  font-size: 13px;
}

@media (max-width: 560px) {
  .panel-heading {
    flex-wrap: wrap;
  }

  .heading-copy {
    flex: 1;
  }

  .upload-button {
    width: 100%;
    margin: 4px 0 0;
  }

  .search-field {
    width: 100%;
  }

  .material-filters {
    justify-content: flex-start;
  }

  .dialog-heading {
    min-height: 78px;
    padding: 14px 16px;
  }

  .dialog-heading h2 {
    font-size: 19px;
  }

  .dialog-content {
    padding: 16px;
  }
}
</style>
