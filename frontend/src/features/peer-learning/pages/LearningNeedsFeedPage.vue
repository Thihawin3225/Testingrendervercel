<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { ChevronLeft, ChevronRight, Handshake, Inbox, Search, X } from '@lucide/vue'
import PageHero from '@/components/layout/PageHero.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue'
import ClearFiltersButton from '@/components/ui/ClearFiltersButton.vue'
import LearningNeedFeedCard from '../components/LearningNeedFeedCard.vue'
import type { LearningNeedFeedItem } from '../interfaces/peerLearningRequest.interface'
import { useLearningNeedsFeed } from '../queries/useLearningNeedsFeed'
import { getPeerLearningApiErrorMessage } from '../utils/apiErrorMessage'
import { useSubjectOfferings } from '../queries/useSubjectOfferings'

const search = ref('')
const searchTerm = ref('')
const academicYearId = ref<number | null>(null)
const yearLevelId = ref<number | null>(null)
const topicType = ref<'custom' | 'official' | ''>('')
const currentPage = ref(1)
const selectedRequest = ref<LearningNeedFeedItem | null>(null)
const detailsDialog = ref<HTMLDialogElement | null>(null)
let searchDebounce: ReturnType<typeof setTimeout> | undefined
const { data: subjectOfferingPage } = useSubjectOfferings()
const selectedSubjectOffering = computed(() => {
  const subjectOfferingId = selectedRequest.value?.subject_offering_id

  if (!subjectOfferingId) return null

  return subjectOfferingPage.value?.data.find(
    (offering) => offering.id === subjectOfferingId,
  ) ?? null
})
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
  academic_year_id: academicYearId.value
    ? academicYearId.value
    : undefined,
  year_level_id: yearLevelId.value
    ? yearLevelId.value
    : undefined,
  topic_type: topicType.value || undefined,
  per_page: 10,
  page: currentPage.value,
}))
const hasActiveFilters = computed(() =>
  Boolean(search.value.trim() || topicType.value || academicYearId.value || yearLevelId.value),
)

const {
  data: feedPage,
  isPending,
  isError,
  error,
} = useLearningNeedsFeed(filters)

const errorMessage = computed(() =>
  getPeerLearningApiErrorMessage(error.value, 'load the learning needs feed'),
)

watch(search, () => {
  if (searchDebounce) clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => {
    searchTerm.value = search.value.trim()
    currentPage.value = 1
  }, 1000)
})

watch([topicType, academicYearId], () => {
  currentPage.value = 1
})

watch(topicType, (type) => {
  if (type !== 'official') {
    academicYearId.value = null
    yearLevelId.value = null
  }
})

watch(academicYearId, () => {
  yearLevelId.value = null
})

onBeforeUnmount(() => {
  if (searchDebounce) clearTimeout(searchDebounce)
})

async function openDetails(request: LearningNeedFeedItem): Promise<void> {
  selectedRequest.value = request
  await nextTick()

  if (detailsDialog.value && !detailsDialog.value.open) {
    detailsDialog.value.showModal()
  }
}

function closeDetails(): void {
  detailsDialog.value?.close()
}

function clearFilters(): void {
  if (searchDebounce) clearTimeout(searchDebounce)
  search.value = ''
  searchTerm.value = ''
  topicType.value = ''
  academicYearId.value = null
  yearLevelId.value = null
  currentPage.value = 1
}

function formatDate(value: string | null): string {
  if (!value) return 'Not specified'
  const [year, month, day] = value.slice(0, 10).split('-')
  return `${month}/${day}/${year}`
}
</script>

<template>
  <section class="learning-feed" aria-labelledby="feed-title">
    <PageHero
      eyebrow="LEARN TOGETHER"
      title="Learning Needs Feed"
      title-id="feed-title"
      description="Find learners who are looking to study the same topics as you."
      :icon="Search"
    />

    <BaseCard as="section" padded class="feed-panel" aria-labelledby="feed-list-title">
      <div class="feed-toolbar">
        <div>
          <h2 id="feed-list-title">
            Open learning requests
            <span v-if="feedPage" class="heading-count">{{ feedPage.meta.total }}</span>
          </h2>
          <p>Browse requests shared by other active learners.</p>
        </div>
        <label class="search-field">
          <Search :size="17" aria-hidden="true" />
          <span class="sr-only">Search learning requests</span>
          <input
            v-model="search"
            type="search"
            maxlength="100"
            placeholder="Search topics or requests..."
          />
        </label>
        <div class="feed-filters" aria-label="Filter learning requests">
          <div class="filter-field">
            <select id="feed-topic-type" v-model="topicType">
              <option value="">All topics</option>
              <option value="official">Official subjects</option>
              <option value="custom">Custom topics</option>
            </select>
          </div>
          <div class="filter-field">
            <select
              id="feed-academic-year"
              v-model="academicYearId"
              :disabled="topicType === 'custom' || !academicYearOptions.length"
            >
              <option :value="null">All years</option>
              <option v-for="option in academicYearOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>
          <div class="filter-field">
            <select
              id="feed-year-level"
              v-model="yearLevelId"
              :disabled="topicType === 'custom' || !academicYearId || !yearLevelOptions.length"
            >
              <option :value="null">All levels</option>
              <option v-for="option in yearLevelOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>
          <ClearFiltersButton
            :disabled="!hasActiveFilters"
            @clear="clearFilters"
          />
        </div>
      </div>

      <BaseEmptyState
        v-if="isPending"
        title="Loading learning requests..."
        description="Requests from other learners will appear here shortly."
      >
        <template #icon><Inbox :size="38" /></template>
      </BaseEmptyState>

      <div v-else-if="isError" class="feed-error" role="alert">
        <strong>The learning needs feed could not be loaded.</strong>
        <p>{{ errorMessage }}</p>
      </div>

      <BaseEmptyState
        v-else-if="feedPage?.data.length === 0"
        :title="search.trim() ? 'No matching requests' : 'No open learning requests yet'"
        :description="search.trim()
          ? 'Try another topic or search phrase.'
          : 'Check back later to see what other learners want to study.'"
      >
        <template #icon><Inbox :size="38" /></template>
      </BaseEmptyState>

      <template v-else-if="feedPage">
        <ul class="feed-list">
          <li v-for="request in feedPage.data" :key="request.id">
            <LearningNeedFeedCard :request="request" @view="openDetails" />
          </li>
        </ul>

        <nav v-if="feedPage.meta.last_page > 1" class="pagination" aria-label="Feed pages">
          <BaseButton
            variant="outline"
            size="sm"
            :disabled="currentPage <= 1 || isPending"
            @click="currentPage--"
          >
            <ChevronLeft :size="16" aria-hidden="true" />
            Previous
          </BaseButton>
          <span>Page {{ feedPage.meta.current_page }} of {{ feedPage.meta.last_page }}</span>
          <BaseButton
            variant="outline"
            size="sm"
            :disabled="currentPage >= feedPage.meta.last_page || isPending"
            @click="currentPage++"
          >
            Next
            <ChevronRight :size="16" aria-hidden="true" />
          </BaseButton>
        </nav>
      </template>
    </BaseCard>

    <dialog
      ref="detailsDialog"
      class="details-dialog"
      aria-labelledby="details-title"
      @cancel.prevent
      @close="selectedRequest = null"
    >
      <section v-if="selectedRequest" class="details-shell">
        <header class="details-heading">
          <div>
            <p class="eyebrow">LEARNING NEED</p>
            <h2 id="details-title">Request Details</h2>
          </div>
          <BaseButton variant="ghost" aria-label="Close details" @click="closeDetails">
            <X :size="19" aria-hidden="true" />
          </BaseButton>
        </header>

        <div class="details-content">
          <div class="details-topic-author">
            <span class="topic-chip">
              {{ selectedRequest.topic_title || selectedRequest.custom_topic?.title || 'Peer learning topic' }}
            </span>
            <span class="feed-author">{{ selectedRequest.learner.name }}</span>
          </div>
          <h3>{{ selectedRequest.title }}</h3>
          <dl class="details-list">
            <template v-if="selectedRequest.subject_offering_id">
              <div class="details-section-heading">
                <dt>Academic details</dt>
                <dd></dd>
              </div>
              <div>
                <dt>Subject</dt>
                <dd>
                  <template v-if="selectedSubjectOffering?.subject">
                    <span v-if="selectedSubjectOffering.subject.subject_code">
                      {{ selectedSubjectOffering.subject.subject_code }} —
                    </span>
                    {{ selectedSubjectOffering.subject.subject_name || 'Not specified' }}
                  </template>
                  <template v-else>Not available</template>
                </dd>
              </div>
              <div>
                <dt>Academic year</dt>
                <dd>{{ selectedSubjectOffering?.academic_year.name || 'Not specified' }}</dd>
              </div>
              <div>
                <dt>Year level</dt>
                <dd>{{ selectedSubjectOffering?.year_level.name || 'Not specified' }}</dd>
              </div>
              <div>
                <dt>Major</dt>
                <dd>{{ selectedSubjectOffering?.major.name || 'Not specified' }}</dd>
              </div>
              <div>
                <dt>Semester</dt>
                <dd>{{ selectedSubjectOffering?.semester.name || 'Not specified' }}</dd>
              </div>
            </template>
            <div>
              <dt>Description</dt>
              <dd>{{ selectedRequest.description || 'No description provided.' }}</dd>
            </div>
            <div v-if="selectedRequest.learning_goal">
              <dt>Learning goal</dt>
              <dd>{{ selectedRequest.learning_goal }}</dd>
            </div>
            <div>
              <dt>Preferred mode</dt>
              <dd>{{ selectedRequest.preferred_mode.replace('_', ' ') }}</dd>
            </div>
            <div>
              <dt>Preferred dates</dt>
              <dd>
                {{ formatDate(selectedRequest.preferred_start_date) }}
                <template v-if="selectedRequest.preferred_end_date">
                  to {{ formatDate(selectedRequest.preferred_end_date) }}
                </template>
              </dd>
            </div>
            <div>
              <dt>Preferred time</dt>
              <dd>
                <template v-if="selectedRequest.preferred_start_time && selectedRequest.preferred_end_time">
                  {{ selectedRequest.preferred_start_time }} to {{ selectedRequest.preferred_end_time }}
                </template>
                <template v-else>Not specified</template>
              </dd>
            </div>
            <div v-if="selectedRequest.preferred_availability">
              <dt>Additional notes</dt>
              <dd>{{ selectedRequest.preferred_availability }}</dd>
            </div>
          </dl>
          <div class="details-actions">
            <BaseButton variant="outline" @click="closeDetails">Close</BaseButton>
          </div>
        </div>
      </section>
    </dialog>
  </section>
</template>

<style scoped>
.learning-feed {
  display: grid;
  gap: 18px;
  color: var(--text);
}

.feed-panel {
  display: grid;
  gap: 20px;
}

.feed-toolbar {
  display: flex;
  align-items: center;
  gap: 14px;
}

.feed-toolbar > div:first-child {
  margin-right: auto;
}

.feed-toolbar h2 {
  margin: 0;
  font-size: 19px;
}

.heading-count {
  display: inline-flex;
  min-width: 22px;
  height: 22px;
  align-items: center;
  justify-content: center;
  margin-left: 6px;
  padding: 0 6px;
  border-radius: 999px;
  background: #f3edff;
  color: #6541bb;
  font-size: 12px;
  vertical-align: 2px;
}

.feed-toolbar p {
  margin: 5px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.search-field {
  display: flex;
  width: min(250px, 100%);
  min-height: 36px;
  align-items: center;
  gap: 9px;
  padding: 0 10px;
  border: 1px solid #ded7f2;
  border-radius: 9px;
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
  color: var(--text);
  font: inherit;
}

.feed-filters {
  display: flex;
  flex-wrap: wrap;
  flex: none;
  justify-content: flex-end;
  gap: 8px;
}

.filter-field {
  display: grid;
  min-width: 125px;
  gap: 4px;
}

.filter-field label {
  color: var(--muted);
  font-size: 10px;
  font-weight: 750;
  letter-spacing: 0.02em;
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

.feed-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 290px), 1fr));
  gap: 15px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.feed-list li {
  min-width: 0;
}

.feed-error {
  padding: 15px;
  border: 1px solid #ffd4dc;
  border-radius: 12px;
  background: #fff7f8;
  color: #b4233a;
}

.feed-error p {
  margin: 5px 0 0;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  padding-top: 2px;
  color: var(--muted);
  font-size: 13px;
}

.details-dialog {
  position: fixed;
  inset: 0;
  width: min(720px, calc(100vw - 28px));
  max-width: none;
  max-height: calc(100dvh - 28px);
  margin: auto;
  padding: 0;
  overflow: hidden;
  border: 0;
  border-radius: 19px;
  background: transparent;
}

.details-dialog::backdrop {
  background: #17112b75;
  backdrop-filter: blur(7px);
}

.details-shell {
  display: grid;
  grid-template-rows: auto minmax(0, 1fr);
  max-height: calc(100dvh - 28px);
  overflow: hidden;
  border: 1px solid #e7e0f5;
  border-radius: 19px;
  background: #fff;
  box-shadow: 0 24px 80px #21134230;
}

.details-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 17px 22px;
  background: linear-gradient(105deg, #f3edff, #e6ddff);
}

.details-heading .eyebrow {
  margin: 0 0 4px;
  color: #6a43cc;
  font-size: 11px;
  font-weight: 850;
  letter-spacing: 0.13em;
  text-transform: uppercase;
}

.details-heading h2 {
  margin: 0;
  font-size: 21px;
}

.details-content {
  display: grid;
  gap: 17px;
  min-height: 0;
  overflow: auto;
  padding: 22px;
}

.details-topic-author {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 9px;
}

.topic-chip {
  width: fit-content;
  max-width: min(100%, 360px);
  padding: 5px 10px;
  overflow: hidden;
  border-radius: 999px;
  background: #f1eaff;
  color: #6335c5;
  font-size: 11px;
  font-weight: 750;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.feed-author {
  color: #655d7e;
  font-size: 12px;
  font-weight: 650;
  overflow-wrap: anywhere;
}

.details-content h3 {
  margin: 0;
  font-size: 22px;
  overflow-wrap: anywhere;
}

.details-list {
  display: grid;
  margin: 0;
  overflow: hidden;
  border: 1px solid #ece7f6;
  border-radius: 13px;
}

.details-list > div {
  display: grid;
  grid-template-columns: minmax(130px, 0.35fr) minmax(0, 1fr);
  gap: 14px;
  padding: 13px 15px;
  border-bottom: 1px solid #f0ecf7;
}

.details-list > div:last-child {
  border-bottom: 0;
}

.details-section-heading {
  display: block;
  padding: 11px 15px 7px;
  background: #faf8ff;
}

.details-section-heading dt {
  color: #6335c5;
  font-size: 11px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.details-section-heading dd {
  display: none;
}

.details-list dt {
  color: #77718f;
  font-size: 12px;
  font-weight: 750;
}

.details-list dd {
  min-width: 0;
  margin: 0;
  color: var(--text);
  font-size: 13px;
  overflow-wrap: anywhere;
  white-space: pre-wrap;
}

.details-actions {
  display: flex;
  justify-content: flex-end;
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

@media (max-width: 600px) {
  .feed-toolbar {
    align-items: stretch;
    flex-direction: column;
  }

  .feed-toolbar > div:first-child {
    margin-right: 0;
  }

  .search-field {
    width: 100%;
  }

  .feed-filters {
    width: 100%;
  }

  .pagination {
    gap: 8px;
  }

  .pagination span {
    font-size: 12px;
  }

  .details-heading,
  .details-content {
    padding-right: 16px;
    padding-left: 16px;
  }

  .details-list > div {
    grid-template-columns: 1fr;
    gap: 5px;
  }
}
</style>
