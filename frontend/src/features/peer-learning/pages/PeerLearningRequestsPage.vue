<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import {
  CalendarDays,
  Clock3,
  Eye,
  FileText,
  Handshake,
  Inbox,
  Pencil,
  Plus,
  Trash2,
  X,
} from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue'
import ClearFiltersButton from '@/components/ui/ClearFiltersButton.vue'
import PageHero from '@/components/layout/PageHero.vue'
import PeerLearningRequestForm from '../components/PeerLearningRequestForm.vue'
import { useCreatePeerLearningRequest } from '../mutations/useCreatePeerLearningRequest'
import type {
  CreatePeerLearningRequestPayload,
  PeerLearningRequest,
} from '../interfaces/peerLearningRequest.interface'
import { usePeerLearningRequests } from '../queries/usePeerLearningRequests'
import { getPeerLearningApiErrorMessage } from '../utils/apiErrorMessage'
import { useUpdatePeerLearningRequest } from '../mutations/useUpdatePeerLearningRequest'
import { useDeletePeerLearningRequest } from '../mutations/useDeletePeerLearningRequest'
import { useSubjectOfferings } from '../queries/useSubjectOfferings'

const academicYearId = ref<number | null>(null)
const yearLevelId = ref<number | null>(null)
const topicType = ref<'custom' | 'official' | ''>('')
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
const requestFilters = computed(() => ({
  academic_year_id: academicYearId.value
    ? academicYearId.value
    : undefined,
  year_level_id: yearLevelId.value
    ? yearLevelId.value
    : undefined,
  topic_type: topicType.value || undefined,
  per_page: 10,
}))
const hasActiveFilters = computed(() =>
  Boolean(topicType.value || academicYearId.value || yearLevelId.value),
)
const {
  data: requestPage,
  isPending: isRequestsPending,
  isError: isRequestsError,
  error: requestsError,
} = usePeerLearningRequests(requestFilters)

const {
  mutate: createRequest,
  reset: resetCreateMutation,
  isPending: isCreating,
  error: createError,
  isSuccess: isCreateSuccess,
} = useCreatePeerLearningRequest()
const {
  mutate: updateRequest,
  reset: resetUpdateMutation,
  isPending: isUpdating,
  error: updateError,
  isSuccess: isUpdateSuccess,
} = useUpdatePeerLearningRequest()
const {
  mutate: deleteRequest,
  reset: resetDeleteMutation,
  isPending: isDeleting,
  isError: isDeleteError,
  error: deleteError,
  isSuccess: isDeleteSuccess,
} = useDeletePeerLearningRequest()

const createDialog = ref<HTMLDialogElement | null>(null)
const formKey = ref(0)
const dialogMode = ref<'create' | 'edit' | 'details' | null>(null)
const activeRequest = ref<PeerLearningRequest | null>(null)
const activeSubjectOffering = computed(() => {
  const subjectOfferingId = activeRequest.value?.subject_offering_id

  if (!subjectOfferingId) return null

  return subjectOfferingPage.value?.data.find(
    (offering) => offering.id === subjectOfferingId,
  ) ?? null
})
const pageNotice = ref('')
const isSubmittingForm = computed(() => isCreating.value || isUpdating.value)
const requestsErrorMessage = computed(() =>
  getPeerLearningApiErrorMessage(requestsError.value, 'load your learning requests'),
)
const dialogErrorMessage = computed(() => {
  const error = dialogMode.value === 'edit' ? updateError.value : createError.value
  const action = dialogMode.value === 'edit'
    ? 'update the learning request'
    : 'create the learning request'

  return error ? getPeerLearningApiErrorMessage(error, action) : ''
})
const deleteErrorMessage = computed(() =>
  getPeerLearningApiErrorMessage(deleteError.value, 'delete the learning request'),
)

watch(topicType, (type) => {
  if (type !== 'official') {
    academicYearId.value = null
    yearLevelId.value = null
  }
})

watch(academicYearId, () => {
  yearLevelId.value = null
})

watch(isCreateSuccess, (success) => {
  if (!success) return

  pageNotice.value = 'Your learning request was created.'
  closeCreateDialog()
})

watch(isUpdateSuccess, (success) => {
  if (!success) return

  pageNotice.value = 'Your learning request was updated.'
  closeCreateDialog()
})

watch(isDeleteSuccess, (success) => {
  if (success) pageNotice.value = 'Your learning request was deleted.'
})

function resetMutations(): void {
  resetCreateMutation()
  resetUpdateMutation()
  resetDeleteMutation()
}

function showDialog(mode: 'create' | 'edit' | 'details', request?: PeerLearningRequest): void {
  pageNotice.value = ''
  resetMutations()
  dialogMode.value = mode
  activeRequest.value = request ?? null
  formKey.value += 1

  if (createDialog.value && !createDialog.value.open) {
    createDialog.value.showModal()
  }
}

function openCreateDialog(): void {
  showDialog('create')
}

function openDetails(request: PeerLearningRequest): void {
  showDialog('details', request)
}

function openEdit(request: PeerLearningRequest): void {
  showDialog('edit', request)
}

function clearFilters(): void {
  topicType.value = ''
  academicYearId.value = null
  yearLevelId.value = null
}

function closeCreateDialog(): void {
  createDialog.value?.close()
}

function handleDialogClose(): void {
  dialogMode.value = null
  activeRequest.value = null
  formKey.value += 1
  resetMutations()
}

function handleFormSubmit(payload: CreatePeerLearningRequestPayload): void {
  if (dialogMode.value === 'edit' && activeRequest.value) {
    updateRequest({ id: activeRequest.value.id, payload })
    return
  }

  createRequest(payload)
}

function handleDelete(request: PeerLearningRequest): void {
  const shouldDelete = window.confirm(
    `Delete "${request.title}"? This cannot be undone.`,
  )

  if (shouldDelete) {
    pageNotice.value = ''
    resetDeleteMutation()
    deleteRequest(request.id)
  }
}

function formatDate(value: string | null): string {
  if (!value) return 'Not specified'

  const [year, month, day] = value.split('-')
  return `${month}/${day}/${year}`
}
</script>

<template>
  <section class="peer-learning" aria-labelledby="peer-learning-title">
    <PageHero
      eyebrow="LEARN TOGETHER"
      title="Peer Learning"
      title-id="peer-learning-title"
      description="Ask for help with a topic and connect with learners who can support your goals."
      :icon="Handshake"
    />

    <p v-if="pageNotice" class="success-message" role="status">
      {{ pageNotice }}
    </p>
    <p v-if="isDeleteError" class="error-message page-error" role="alert">
      {{ deleteErrorMessage }}
    </p>

    <BaseCard as="section" padded class="panel" aria-labelledby="requests-heading">
      <div class="requests-toolbar">
        <div class="panel-heading">
          <span class="heading-icon" aria-hidden="true">
            <FileText :size="20" />
          </span>
          <div>
            <h2 id="requests-heading">
              My learning requests
              <span v-if="requestPage" class="heading-count">{{ requestPage.meta.total }}</span>
            </h2>
            <p>Requests you have shared with the campus community.</p>
          </div>
        </div>
        <div class="request-filters" aria-label="Filter my learning needs">
          <div class="filter-field">
          
            <select id="my-topic-type" v-model="topicType">
              <option value="">All topics</option>
              <option value="official">Official subjects</option>
              <option value="custom">Custom topics</option>
            </select>
          </div>
          <div class="filter-field">
            
            <select
              id="my-academic-year"
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
              id="my-year-level"
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
        <BaseButton class="create-button" @click="openCreateDialog">
          <Plus :size="17" aria-hidden="true" />
          Create Learning Request
        </BaseButton>
      </div>

      <BaseEmptyState
        v-if="isRequestsPending"
        title="Loading requests..."
        description="Your learning requests will appear here shortly."
      >
        <template #icon><Inbox :size="38" /></template>
      </BaseEmptyState>

      <div v-else-if="isRequestsError" class="error-state" role="alert">
        <strong>Requests could not be loaded.</strong>
        <p>{{ requestsErrorMessage }}</p>
      </div>

      <BaseEmptyState
        v-else-if="requestPage?.data.length === 0"
        title="No learning requests yet"
        description="Create a request to tell other learners what you want to study."
      >
        <template #icon><Inbox :size="38" /></template>
        <template #action>
          <BaseButton size="sm" @click="openCreateDialog">
            Create a request
          </BaseButton>
        </template>
      </BaseEmptyState>

      <ul v-else class="request-list">
        <li v-for="request in requestPage?.data ?? []" :key="request.id">
          <div class="request-card-top">
            <span class="topic-chip">
            {{ request.topic_title || request.custom_topic?.title || 'Peer learning topic' }}
            </span>
            <div class="request-top-actions">
              <span class="request-status">{{ request.status }}</span>
              <BaseButton
                variant="ghost"
                size="sm"
                :aria-label="`View details for ${request.title}`"
                @click="openDetails(request)"
              >
                <Eye :size="15" aria-hidden="true" />
                
              </BaseButton>
            </div>
          </div>
          <div class="request-copy">
            <h3>{{ request.title }}</h3>
            <p v-if="request.description">{{ request.description }}</p>
          </div>
          <div
            v-if="request.preferred_start_date || (request.preferred_start_time && request.preferred_end_time)"
            class="request-card-schedule"
          >
            <span v-if="request.preferred_start_date" class="request-schedule-item">
              <CalendarDays :size="14" aria-hidden="true" />
              {{ request.preferred_start_date }}
              <template v-if="request.preferred_end_date">
                to {{ request.preferred_end_date }}
              </template>
            </span>
            <span
              v-if="request.preferred_start_time && request.preferred_end_time"
              class="request-schedule-item"
            >
              <Clock3 :size="14" aria-hidden="true" />
              {{ request.preferred_start_time }} to {{ request.preferred_end_time }}
            </span>
          </div>
          <div class="request-details">
            <div class="request-meta">
              <span class="request-mode">
                {{ request.preferred_mode.replace('_', ' ') }}
              </span>
              <span v-if="request.preferred_availability" class="request-schedule">
                {{ request.preferred_availability }}
              </span>
            </div>
            <div class="request-card-actions">
              <BaseButton
                variant="outline"
                size="sm"
                title="Edit this request"
                @click="openEdit(request)"
              >
                <Pencil :size="15" aria-hidden="true" />
                
              </BaseButton>
              <BaseButton
                variant="danger"
                size="sm"
                :disabled="isDeleting"
                @click="handleDelete(request)"
              >
                <Trash2 :size="15" aria-hidden="true" />
                
              </BaseButton>
            </div>
          </div>
        </li>
      </ul>
    </BaseCard>

    <dialog
      ref="createDialog"
      class="create-dialog"
      aria-labelledby="request-dialog-title"
      @cancel.prevent
      @close="handleDialogClose"
    >
      <section class="dialog-shell" aria-labelledby="request-dialog-title">
        <header class="dialog-heading">
          <div>
            <p class="dialog-eyebrow">PEER LEARNING · LEARNER</p>
            <h2 id="request-dialog-title">
              {{ dialogMode === 'details'
                ? 'Learning Request Details'
                : dialogMode === 'edit'
                  ? 'Edit Learning Request'
                  : 'Request Peer Learning' }}
            </h2>
          </div>
          <BaseButton
            variant="ghost"
            aria-label="Close form"
            @click="closeCreateDialog"
          >
            <X :size="20" aria-hidden="true" />
          </BaseButton>
        </header>

        <div class="dialog-content">
          <p v-if="dialogErrorMessage" class="error-message" role="alert">
            {{ dialogErrorMessage }}
          </p>

          <section
            v-if="dialogMode === 'details' && activeRequest"
            class="request-detail-view"
            aria-label="Learning request details"
          >
            <div class="detail-topic-status">
              <span class="topic-chip">
                {{ activeRequest.topic_title || activeRequest.custom_topic?.title || 'Peer learning topic' }}
              </span>
              <span class="request-status">{{ activeRequest.status }}</span>
            </div>
            <h3>{{ activeRequest.title }}</h3>
            <dl class="detail-list">
              <template v-if="activeRequest.subject_offering_id">
                <div class="detail-section-heading">
                  <dt>Academic details</dt>
                  <dd></dd>
                </div>
                <div>
                  <dt>Subject</dt>
                  <dd>
                    <template v-if="activeSubjectOffering?.subject">
                      <span v-if="activeSubjectOffering.subject.subject_code">
                        {{ activeSubjectOffering.subject.subject_code }} —
                      </span>
                      {{ activeSubjectOffering.subject.subject_name || 'Not specified' }}
                    </template>
                    <template v-else>Not available</template>
                  </dd>
                </div>
                <div>
                  <dt>Academic year</dt>
                  <dd>{{ activeSubjectOffering?.academic_year.name || 'Not specified' }}</dd>
                </div>
                <div>
                  <dt>Year level</dt>
                  <dd>{{ activeSubjectOffering?.year_level.name || 'Not specified' }}</dd>
                </div>
                <div>
                  <dt>Major</dt>
                  <dd>{{ activeSubjectOffering?.major.name || 'Not specified' }}</dd>
                </div>
                <div>
                  <dt>Semester</dt>
                  <dd>{{ activeSubjectOffering?.semester.name || 'Not specified' }}</dd>
                </div>
              </template>
              <div>
                <dt>Description</dt>
                <dd>{{ activeRequest.description || 'No description provided.' }}</dd>
              </div>
              <div v-if="activeRequest.learning_goal">
                <dt>Learning goal</dt>
                <dd>{{ activeRequest.learning_goal }}</dd>
              </div>
              <div>
                <dt>Preferred learning mode</dt>
                <dd>{{ activeRequest.preferred_mode.replace('_', ' ') }}</dd>
              </div>
              <div>
                <dt>Preferred dates</dt>
                <dd>
                  {{ formatDate(activeRequest.preferred_start_date) }}
                  <template v-if="activeRequest.preferred_end_date">
                    to {{ formatDate(activeRequest.preferred_end_date) }}
                  </template>
                </dd>
              </div>
              <div>
                <dt>Preferred time</dt>
                <dd v-if="activeRequest.preferred_start_time && activeRequest.preferred_end_time">
                  {{ activeRequest.preferred_start_time }} to {{ activeRequest.preferred_end_time }}
                </dd>
                <dd v-else>Not specified</dd>
              </div>
              <div v-if="activeRequest.preferred_availability">
                <dt>Additional notes</dt>
                <dd>{{ activeRequest.preferred_availability }}</dd>
              </div>
              <div>
                <dt>Created</dt>
                <dd>{{ formatDate(activeRequest.created_at.slice(0, 10)) }}</dd>
              </div>
            </dl>
            <div class="detail-actions">
              <BaseButton variant="outline" @click="closeCreateDialog">Close</BaseButton>
              <BaseButton
                @click="openEdit(activeRequest)"
              >
                <Pencil :size="15" aria-hidden="true" />
                Edit Request
              </BaseButton>
            </div>
          </section>

          <PeerLearningRequestForm
            v-else-if="dialogMode === 'create' || dialogMode === 'edit'"
            :key="formKey"
            :submitting="isSubmittingForm"
            :initial-request="dialogMode === 'edit' ? activeRequest : null"
            @submit="handleFormSubmit"
            @cancel="closeCreateDialog"
          />
        </div>
      </section>
    </dialog>
  </section>
</template>

<style scoped>
.peer-learning {
  display: grid;
  gap: 18px;
  color: var(--text);
}

.panel {
  min-width: 0;
}

.panel-heading,
.dialog-heading {
  display: flex;
  align-items: center;
  gap: 11px;
  margin-bottom: 17px;
}

.requests-toolbar {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 17px;
}

.requests-toolbar .panel-heading {
  min-width: 0;
  margin-right: auto;
  margin-bottom: 0;
}

.create-button {
  flex: none;
}

.panel-heading h2,
.dialog-heading h2 {
  margin: 0;
  font-size: 18px;
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

.panel-heading p,
.dialog-heading p {
  margin: 3px 0 0;
  color: var(--muted);
  font-size: 12px;
}

.heading-icon {
  display: grid;
  flex: none;
  width: 40px;
  height: 40px;
  place-items: center;
  border-radius: 12px;
  background: #f3edff;
  color: var(--purple);
}

.request-filters {
  display: flex;
  flex-wrap: wrap;
  flex: none;
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

.request-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 290px), 1fr));
  gap: 15px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.request-list li {
  display: flex;
  flex-direction: column;
  gap: 14px;
  min-width: 0;
  height: 100%;
  min-height: 300px;
  padding: 18px;
  border: 1px solid #e6e1f2;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 8px 22px #2413440a;
}

.request-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.request-card-top {
  min-width: 0;
}

.request-top-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  flex-wrap: wrap;
  gap: 7px;
}

.topic-chip {
  flex: 0 1 auto;
  min-width: 0;
  width: fit-content;
  max-width: min(65%, 360px);
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

.request-copy {
  min-width: 0;
  overflow-wrap: anywhere;
}

.request-copy h3 {
  display: -webkit-box;
  overflow: hidden;
  margin: 0;
  color: var(--text);
  font-size: 14px;
  line-height: 1.4;
  overflow-wrap: anywhere;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.request-copy p {
  display: -webkit-box;
  overflow: hidden;
  margin: 6px 0 0;
  color: var(--muted);
  overflow-wrap: anywhere;
  line-height: 1.55;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
}

.request-details {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px 12px;
  min-width: 0;
  margin-top: auto;
  padding-top: 11px;
  border-top: 1px solid #f0ecfa;
}

.request-meta {
  display: flex;
  flex: 1 1 150px;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 12px;
  min-width: 0;
}

.request-card-schedule {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: 8px;
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding: 11px 12px;
  border: 1px solid #eee9f8;
  border-radius: 11px;
  background: #faf8ff;
  color: #716b88;
  font-size: 11px;
  line-height: 1.45;
}

.request-schedule-item {
  display: flex;
  min-height: 20px;
  align-items: center;
  gap: 5px;
  min-width: 0;
  overflow-wrap: anywhere;
}

.request-schedule-item svg {
  flex: none;
  color: #8a5be0;
}

.request-mode {
  color: #77718f;
  font-size: 11px;
  text-transform: capitalize;
}

.request-status {
  flex: none;
  padding: 4px 9px;
  border-radius: 999px;
  background: #f3edff;
  color: #6541bb;
  font-size: 11px;
  font-weight: 750;
  text-transform: capitalize;
}

.request-schedule {
  min-width: 0;
  color: #716b88;
  font-size: 11px;
  overflow-wrap: anywhere;
}

.request-card-actions {
  display: flex;
  flex: 0 0 auto;
  flex-wrap: nowrap;
  justify-content: flex-end;
  gap: 7px;
}

.success-message {
  margin: 0;
  color: var(--success);
  font-weight: 650;
}

.error-state,
.error-message {
  color: var(--danger);
}

.error-state p {
  margin: 5px 0 0;
}

.create-dialog {
  position: fixed;
  inset: 0;
  width: min(1120px, calc(100vw - 28px));
  max-width: none;
  max-height: calc(100dvh - 24px);
  margin: auto;
  padding: 0;
  overflow: visible;
  border: 0;
  background: transparent;
}

.create-dialog::backdrop {
  background: rgb(16 17 50 / 42%);
  backdrop-filter: blur(8px);
}

.dialog-shell {
  display: grid;
  grid-template-rows: auto minmax(0, 1fr);
  height: min(860px, calc(100dvh - 24px));
  max-height: calc(100dvh - 24px);
  overflow: hidden;
  border: 1px solid #e7e0f5;
  border-radius: 22px;
  background: #fff;
  box-shadow: 0 24px 80px #21134230;
}

.dialog-content {
  min-height: 0;
  overflow-y: auto;
  overscroll-behavior: contain;
  padding: 18px 26px 20px;
}

.dialog-heading {
  min-height: 100px;
  align-items: center;
  justify-content: space-between;
  margin: 0;
  padding: 20px 26px;
  background: linear-gradient(105deg, #f3edff, #e6ddff);
}

.dialog-eyebrow {
  margin: 0 0 5px !important;
  color: #6939d2 !important;
  font-size: 11px !important;
  font-weight: 850;
  letter-spacing: 0.13em;
}

.dialog-heading h2 {
  font-size: 23px;
}

.dialog-heading > div {
  min-width: 0;
}

.error-message {
  margin: 0 0 12px;
}

.page-error {
  margin: 0;
}

.request-detail-view {
  display: grid;
  gap: 18px;
}

.detail-topic-status {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.request-detail-view > h3 {
  margin: 0;
  color: var(--text);
  font-size: 23px;
  overflow-wrap: anywhere;
}

.detail-list {
  display: grid;
  gap: 0;
  margin: 0;
  border: 1px solid #ece7f6;
  border-radius: 14px;
  overflow: hidden;
}

.detail-list > div {
  display: grid;
  grid-template-columns: minmax(145px, 0.35fr) minmax(0, 1fr);
  gap: 14px;
  padding: 14px 16px;
  border-bottom: 1px solid #f0ecf7;
}

.detail-list > div:last-child {
  border-bottom: 0;
}

.detail-list > .detail-section-heading {
  display: block;
  padding: 11px 16px 7px;
  background: #faf8ff;
}

.detail-section-heading dt {
  color: #6335c5;
  font-size: 11px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.detail-section-heading dd {
  display: none;
}

.detail-list dt {
  color: #77718f;
  font-size: 12px;
  font-weight: 750;
}

.detail-list dd {
  margin: 0;
  color: var(--text);
  font-size: 13px;
  overflow-wrap: anywhere;
  white-space: pre-wrap;
}

.detail-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

@media (max-width: 760px) {
  .requests-toolbar {
    align-items: flex-start;
    flex-direction: column;
  }

  .requests-toolbar .panel-heading {
    width: 100%;
    margin-right: 0;
  }

  .request-filters {
    width: 100%;
  }

  .create-button {
    align-self: flex-start;
  }

  .dialog-content {
    padding: 18px 16px 16px;
  }

  .dialog-heading {
    min-height: 84px;
    padding: 16px;
  }

  .dialog-heading h2 {
    font-size: 20px;
  }

  .detail-list > div {
    grid-template-columns: 1fr;
    gap: 5px;
  }
}
</style>
