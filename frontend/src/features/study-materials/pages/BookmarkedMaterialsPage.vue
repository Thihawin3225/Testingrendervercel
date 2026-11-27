<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
import { Bookmark, BookOpen, Inbox, Search, X } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import PageHero from '@/components/layout/PageHero.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue'
import ClearFiltersButton from '@/components/ui/ClearFiltersButton.vue'
import BookmarkedMaterialCard from '../components/BookmarkedMaterialCard.vue'
import StudyMaterialPreviewDialog from '../components/StudyMaterialPreviewDialog.vue'
import type { BookmarkedStudyMaterial } from '../interfaces/bookmarkedMaterial.interface'
import { useBookmarkedStudyMaterials } from '../queries/useBookmarkedStudyMaterials'
import { useRemoveStudyMaterialBookmark } from '../mutations/useRemoveStudyMaterialBookmark'
import { downloadStudyMaterial } from '../api/studyMaterial.api'
import { useSubjectOfferings } from '../../peer-learning/queries/useSubjectOfferings'
import { useDebouncedSearch } from '../utils/useDebouncedSearch'

const { searchInput, searchTerm, clearSearch } = useDebouncedSearch()
const materialType = ref<'official' | 'custom' | ''>('')
const academicYearId = ref<number | null>(null)
const yearLevelId = ref<number | null>(null)
const notice = ref('')
const selectedMaterial = ref<BookmarkedStudyMaterial | null>(null)
const detailsDialog = ref<HTMLDialogElement | null>(null)
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
const { data: bookmarkPage, isPending: isBookmarksPending } =
  useBookmarkedStudyMaterials(filters)
const { mutate: removeBookmarkMutation } = useRemoveStudyMaterialBookmark()

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

async function showDetails(material: BookmarkedStudyMaterial): Promise<void> {
  selectedMaterial.value = material
  await nextTick()
  if (detailsDialog.value && !detailsDialog.value.open) detailsDialog.value.showModal()
}

function closeDetails(): void {
  detailsDialog.value?.close()
}

function openPreview(material: BookmarkedStudyMaterial): void {
  void previewDialog.value?.open(material)
}

function removeBookmark(material: BookmarkedStudyMaterial): void {
  removeBookmarkMutation(material.id, {
    onSuccess: () => {
      notice.value = 'Material removed from bookmarks.'
    },
  })
}

async function downloadMaterial(material: BookmarkedStudyMaterial): Promise<void> {
  try {
    const blob = await downloadStudyMaterial(material.id)
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = material.original_filename
    link.click()
    URL.revokeObjectURL(url)
  } catch {
    notice.value = 'Material could not be downloaded. Please try again.'
  }
}

function formatDate(value: string | null | undefined): string {
  if (!value) return 'Date unavailable'
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value));
}

function formatFileSize(size: number): string {
  if (size < 1024 * 1024) return `${Math.max(1, Math.round(size / 1024))} KB`
  return `${(size / (1024 * 1024)).toFixed(1)} MB`
}
</script>

<template>
  <section class="bookmarked-page" aria-labelledby="bookmarked-title">
    <PageHero
      eyebrow="STUDY MATERIALS"
      title="Bookmarked Materials"
      title-id="bookmarked-title"
      description="Keep useful class notes and resources close at hand."
      :icon="Bookmark"
    />

    <p class="preview-notice" role="status">
      Your saved study materials appear here.
    </p>

    <BaseCard as="section" padded class="bookmarks-panel" aria-labelledby="bookmarks-list-title">
      <div class="panel-heading">
        <div class="heading-icon" aria-hidden="true"><BookOpen :size="20" /></div>
        <div class="heading-copy">
          <h2 id="bookmarks-list-title">Saved resources</h2>
          <p>{{ bookmarkPage?.meta.total ?? 0 }} bookmarks</p>
        </div>
        <RouterLink class="browse-link" :to="{ name: 'study-materials' }">
          Browse Materials
        </RouterLink>
      </div>

      <div class="bookmarks-toolbar">
        <label class="search-field">
          <Search :size="17" aria-hidden="true" />
          <span class="sr-only">Search bookmarked materials</span>
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
        <span class="result-count">{{ bookmarkPage?.data.length ?? 0 }} shown</span>
      </div>

      <p v-if="notice" class="action-notice" role="status">{{ notice }}</p>

      <BaseEmptyState
        v-if="isBookmarksPending || !bookmarkPage?.data.length"
        :title="isBookmarksPending ? 'Loading bookmarks...' : hasActiveFilters ? 'No matching bookmarks' : 'No bookmarked materials yet'"
        :description="hasActiveFilters
          ? 'Try different filters or clear them to see all your bookmarks.'
          : 'Bookmark an active resource from Browse Materials to save it here.'"
      >
        <template #icon><Inbox :size="38" /></template>
        <template #action>
          <RouterLink class="empty-browse-link" :to="{ name: 'study-materials' }">
            Browse Materials
          </RouterLink>
        </template>
      </BaseEmptyState>

      <div v-else class="bookmarks-grid">
        <BookmarkedMaterialCard
          v-for="material in bookmarkPage?.data ?? []"
          :key="material.id"
          :material="material"
          @view="showDetails"
          @preview="openPreview"
          @remove-bookmark="removeBookmark"
          @download="downloadMaterial"
        />
      </div>
    </BaseCard>

    <dialog
      ref="detailsDialog"
      class="details-dialog"
      aria-labelledby="bookmark-details-title"
      @cancel.prevent
    >
      <section v-if="selectedMaterial" class="details-shell">
        <header class="details-heading">
          <div>
            <p class="eyebrow">BOOKMARKED RESOURCE</p>
            <h2 id="bookmark-details-title">{{ selectedMaterial.title }}</h2>
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
            <div><dt>Shared by</dt><dd>{{ selectedMaterial.uploader?.name || 'Campus learner' }}</dd></div>
            <div><dt>Filename</dt><dd>{{ selectedMaterial.original_filename }}</dd></div>
            <div><dt>File type</dt><dd>{{ selectedMaterial.file_type.toUpperCase() }}</dd></div>
            <div><dt>File size</dt><dd>{{ formatFileSize(selectedMaterial.file_size) }}</dd></div>
            <div><dt>Bookmarked</dt><dd>{{ formatDate(selectedMaterial.bookmarked_at ?? null) }}</dd></div>
          </dl>
        </div>
      </section>
    </dialog>
    <StudyMaterialPreviewDialog ref="previewDialog" />
  </section>
</template>

<style scoped>
.bookmarked-page {
  display: grid;
  gap: 17px;
  color: var(--text);
}

.heading-icon {
  display: grid;
  flex: none;
  place-items: center;
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

.bookmarks-panel {
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
  width: 45px;
  height: 45px;
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

.browse-link,
.empty-browse-link {
  display: inline-flex;
  min-height: 40px;
  align-items: center;
  justify-content: center;
  margin-left: auto;
  padding: 0 14px;
  border: 1px solid #a68af9;
  border-radius: 10px;
  background: #fff;
  color: #6427d4;
  font-size: 12px;
  font-weight: 750;
  text-decoration: none;
  white-space: nowrap;
}

.empty-browse-link {
  margin: 15px auto 0;
}

.browse-link:hover,
.empty-browse-link:hover {
  background: #f8f5ff;
}

.browse-link:focus-visible,
.empty-browse-link:focus-visible,
.close-button:focus-visible {
  outline: 2px solid #6c2dea;
  outline-offset: 2px;
}

.bookmarks-toolbar {
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

.bookmarks-grid {
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

@media (max-width: 560px) {
  .panel-heading {
    flex-wrap: wrap;
  }

  .heading-copy {
    flex: 1;
  }

  .browse-link {
    width: 100%;
    margin: 4px 0 0;
  }

  .bookmarks-toolbar {
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
