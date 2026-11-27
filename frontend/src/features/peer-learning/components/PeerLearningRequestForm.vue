<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import CustomTopicPicker from './CustomTopicPicker.vue'
import type {
  CreatePeerLearningRequestPayload,
  PeerLearningRequest,
  PeerLearningMode,
} from '../interfaces/peerLearningRequest.interface'
import { useSubjectOfferings } from '../queries/useSubjectOfferings'

const props = withDefaults(
  defineProps<{
    submitting?: boolean
    initialRequest?: PeerLearningRequest | null
  }>(),
  { submitting: false },
)

const emit = defineEmits<{
  submit: [payload: CreatePeerLearningRequestPayload]
  cancel: []
}>()

const initialRequest = props.initialRequest
const topicSource = ref<'subject_offering' | 'custom_topic'>(
  initialRequest?.custom_topic_id && !initialRequest.subject_offering_id
    ? 'custom_topic'
    : 'subject_offering',
)
const subjectOfferingId = ref<number | null>(
  initialRequest?.subject_offering_id ?? null,
)
const academicYearId = ref<number | null>(null)
const yearLevelId = ref<number | null>(null)
const majorId = ref<number | null>(null)
const semesterId = ref<number | null>(null)
const customTopicId = ref<number | null>(initialRequest?.custom_topic_id ?? null)
const title = ref(initialRequest?.title ?? '')
const description = ref(initialRequest?.description ?? '')
const learningGoal = ref(initialRequest?.learning_goal ?? null)
const preferredMode = ref<PeerLearningMode>(initialRequest?.preferred_mode ?? 'either')
const includeSchedule = ref(Boolean(
  initialRequest?.preferred_start_date
  || initialRequest?.preferred_end_date
  || initialRequest?.preferred_start_time
  || initialRequest?.preferred_end_time,
))
const preferredStartDate = ref(initialRequest?.preferred_start_date ?? getTodayDateString())
const preferredEndDate = ref(initialRequest?.preferred_end_date ?? '')
const startDefault = initialRequest?.preferred_start_time ?? getCurrentTimeString()
const preferredStartTime = ref(startDefault)
const preferredEndTime = ref(
  initialRequest?.preferred_end_time ?? addOneHourToTimeString(startDefault),
)
const additionalNotes = ref(initialRequest?.preferred_availability ?? '')


const {
  data: subjectOfferingPage,
  isPending: isSubjectOfferingsPending,
  isError: isSubjectOfferingsError,
} = useSubjectOfferings()

const subjectOfferings = computed(() => subjectOfferingPage.value?.data ?? [])
const filteredSubjectOfferings = computed(() => subjectOfferings.value.filter((offering) => (
  (!academicYearId.value || offering.academic_year.id === academicYearId.value)
  && (!yearLevelId.value || offering.year_level.id === yearLevelId.value)
  && (!majorId.value || offering.major.id === majorId.value)
  && (!semesterId.value || offering.semester.id === semesterId.value)
)))

function uniqueOptions(
  options: Array<{ id: number | null; name: string | null }>,
): Array<{ id: number; name: string }> {
  const seen = new Set<number>()

  return options.reduce<Array<{ id: number; name: string }>>((result, option) => {
    if (option.id !== null && !seen.has(option.id)) {
      seen.add(option.id)
      result.push({ id: option.id, name: option.name ?? 'Unnamed' })
    }

    return result
  }, [])
}

const academicYearOptions = computed(() => uniqueOptions(
  subjectOfferings.value.map((offering) => offering.academic_year),
))
const yearLevelOptions = computed(() => uniqueOptions(
  subjectOfferings.value
    .filter((offering) => !academicYearId.value || offering.academic_year.id === academicYearId.value)
    .map((offering) => offering.year_level),
))
const majorOptions = computed(() => uniqueOptions(
  subjectOfferings.value
    .filter((offering) => (
      (!academicYearId.value || offering.academic_year.id === academicYearId.value)
      && (!yearLevelId.value || offering.year_level.id === yearLevelId.value)
    ))
    .map((offering) => offering.major),
))
const semesterOptions = computed(() => uniqueOptions(
  subjectOfferings.value
    .filter((offering) => (
      (!academicYearId.value || offering.academic_year.id === academicYearId.value)
      && (!yearLevelId.value || offering.year_level.id === yearLevelId.value)
      && (!majorId.value || offering.major.id === majorId.value)
    ))
    .map((offering) => offering.semester),
))

let syncingAcademicFilters = false

watch(academicYearId, () => {
  if (syncingAcademicFilters) return
  yearLevelId.value = null
  majorId.value = null
  semesterId.value = null
  subjectOfferingId.value = null
})

watch(yearLevelId, () => {
  if (syncingAcademicFilters) return
  majorId.value = null
  semesterId.value = null
  subjectOfferingId.value = null
})

watch(majorId, () => {
  if (syncingAcademicFilters) return
  semesterId.value = null
  subjectOfferingId.value = null
})

watch(semesterId, () => {
  if (syncingAcademicFilters) return
  subjectOfferingId.value = null
})

watch(subjectOfferingPage, (page) => {
  const selectedOffering = page?.data.find(
    (offering) => offering.id === initialRequest?.subject_offering_id,
  )

  watch(preferredStartTime, (newStartTime) => {
  if (!newStartTime) return
  preferredEndTime.value = addOneHourToTimeString(newStartTime)
  })

  if (!selectedOffering) return

  syncingAcademicFilters = true
  academicYearId.value = selectedOffering.academic_year.id
  yearLevelId.value = selectedOffering.year_level.id
  majorId.value = selectedOffering.major.id
  semesterId.value = selectedOffering.semester.id
  syncingAcademicFilters = false
}, { immediate: true })

function selectTopicSource(source: 'subject_offering' | 'custom_topic'): void {
  topicSource.value = source

  if (source === 'subject_offering') {
    customTopicId.value = null
    return
  }

  subjectOfferingId.value = null
  academicYearId.value = null
  yearLevelId.value = null
  majorId.value = null
  semesterId.value = null
}

function setDefaultTimes(): void {
  if (preferredStartDate.value !== todayStr.value) return
  if (preferredStartTime.value && preferredStartTime.value >= getCurrentTimeString()) return

  preferredStartTime.value = getCurrentTimeString()
  preferredEndTime.value = addOneHourToTimeString(preferredStartTime.value)
}

watch(preferredStartDate, () => setDefaultTimes())

function toggleSchedule(): void {
  if (includeSchedule.value) {
    if (!preferredStartDate.value) preferredStartDate.value = getTodayDateString()
    if (!preferredEndDate.value) preferredEndDate.value = getTodayDateString()
    if (!preferredStartTime.value) preferredStartTime.value = getCurrentTimeString()
    if (!preferredEndTime.value) preferredEndTime.value = getCurrentTimeString()
    return
  }

  preferredStartDate.value = getTodayDateString()
  preferredEndDate.value = ''
  preferredStartTime.value = getCurrentTimeString()
  preferredEndTime.value = getCurrentTimeString()
}

const todayStr = computed(() => getTodayDateString())
const nowTimeStr = computed(() => getCurrentTimeString())

const startDateWarning = computed(() => {
  if (!includeSchedule.value || !preferredStartDate.value) return ''
  if (preferredStartDate.value < todayStr.value) {
    return 'This date has already passed. Choose today or a future date.'
  }
  return ''
})

const endDateWarning = computed(() => {
  if (!includeSchedule.value || !preferredEndDate.value) return ''
  if (preferredEndDate.value < todayStr.value) {
    return 'This date has already passed. Choose today or a future date.'
  }
  if (preferredStartDate.value && preferredEndDate.value < preferredStartDate.value) {
    return 'End date cannot be earlier than the start date.'
  }
  return ''
})

const startTimeWarning = computed(() => {
  if (!includeSchedule.value) return ''

  if (
    preferredStartDate.value === todayStr.value
    && preferredStartTime.value
    && preferredStartTime.value < nowTimeStr.value
  ) {
    return 'This time has already passed. Choose the current or a future time.'
  }

  return ''
})

const endTimeWarning = computed(() => {
  if (!includeSchedule.value) return ''

  if (
    preferredStartTime.value
    && preferredEndTime.value
    && preferredEndTime.value <= preferredStartTime.value
  ) {
    return 'End time must be later than the start time.'
  }

  if (
    preferredStartTime.value
    && preferredEndTime.value
    && durationInMinutes(preferredStartTime.value, preferredEndTime.value) < MIN_SESSION_MINUTES
  ) {
    return `The time range must be at least ${MIN_SESSION_MINUTES} minutes.`
  }

  return ''
})

const timeRangeWarning = computed(() => startTimeWarning.value || endTimeWarning.value)

const hasScheduleWarning = computed(() =>
  Boolean(startDateWarning.value || endDateWarning.value || timeRangeWarning.value),
)

const MIN_SESSION_MINUTES = 30

function durationInMinutes(startTime: string, endTime: string): number {
const [startHoursText = '', startMinutesText = ''] = startTime.split(':')
const [endHoursText = '', endMinutesText = ''] = endTime.split(':')

const startHours = Number(startHoursText)
const startMinutes = Number(startMinutesText)
const endHours = Number(endHoursText)
const endMinutes = Number(endMinutesText)

if (
  !startHoursText || !startMinutesText || !endHoursText || !endMinutesText
  || Number.isNaN(startHours) || Number.isNaN(startMinutes)
  || Number.isNaN(endHours) || Number.isNaN(endMinutes)
) {
  return 0
}

  return (endHours * 60 + endMinutes) - (startHours * 60 + startMinutes)
}

function getTodayDateString(): string {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function getCurrentTimeString(): string {
  const now = new Date()
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  return `${hours}:${minutes}`
}

function addOneHourToTimeString(timeStr: string): string {
  const [hoursStr, minutesStr] = timeStr.split(':')
  const hours = Number(hoursStr)
  const minutes = Number(minutesStr)

  if (Number.isNaN(hours) || Number.isNaN(minutes)) {
    return timeStr
  }

  if (hours >= 23) {
    return '23:59'
  }

  const nextHours = String(hours + 1).padStart(2, '0')
  const nextMinutes = String(minutes).padStart(2, '0')
  return `${nextHours}:${nextMinutes}`
}

function submitForm(): void {
  if (!title.value.trim() || !description.value.trim()) return
  if (topicSource.value === 'subject_offering' && !subjectOfferingId.value) return
  if (topicSource.value === 'custom_topic' && !customTopicId.value) return
  if (
    includeSchedule.value
    && (
      !preferredStartDate.value
      || !preferredStartTime.value
      || !preferredEndTime.value
    )
  ) return
  if (includeSchedule.value && hasScheduleWarning.value) return

  emit('submit', {
    subject_offering_id: topicSource.value === 'subject_offering'
      ? subjectOfferingId.value
      : null,
    custom_topic_id: topicSource.value === 'custom_topic'
      ? customTopicId.value
      : null,
    title: title.value.trim(),
    description: description.value.trim(),
    learning_goal: learningGoal.value,
    preferred_mode: preferredMode.value,
    preferred_availability: additionalNotes.value.trim() || null,
    preferred_start_date: includeSchedule.value ? preferredStartDate.value : null,
    preferred_end_date: includeSchedule.value ? preferredEndDate.value : null,
    preferred_start_time: includeSchedule.value ? preferredStartTime.value : null,
    preferred_end_time: includeSchedule.value ? preferredEndTime.value : null,
  })
}
</script>

<template>
  <form class="request-form" @submit.prevent="submitForm">
    <section class="form-section" aria-labelledby="topic-section-title">
      <div class="section-heading">
        <span class="step-number">1</span>
        <h3 id="topic-section-title">What do you want to learn?</h3>
      </div>

      <div class="topic-source-toggle" role="group" aria-label="Topic source">
        <button
          type="button"
          :class="{ active: topicSource === 'subject_offering' }"
          :disabled="props.submitting"
          @click="selectTopicSource('subject_offering')"
        >
          Official Subject
        </button>
        <button
          type="button"
          :class="{ active: topicSource === 'custom_topic' }"
          :disabled="props.submitting"
          @click="selectTopicSource('custom_topic')"
        >
          Custom Topic
        </button>
      </div>

      <div v-if="topicSource === 'subject_offering'" class="academic-selection">
        <p class="field-hint">
          Select the academic context first. Only subjects configured by an administrator will appear.
        </p>
        <div class="academic-filter-grid">
          <div class="form-field">
            <label class="field-label" for="academic-year">
              Academic Year <span class="required-mark">*</span>
            </label>
            <select
              id="academic-year"
              v-model="academicYearId"
              class="field-control"
              :disabled="props.submitting || isSubjectOfferingsPending || isSubjectOfferingsError"
            >
              <option :value="null">Select academic year</option>
              <option v-for="option in academicYearOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>

          <div class="form-field">
            <label class="field-label" for="year-level">
              Year Level <span class="required-mark">*</span>
            </label>
            <select
              id="year-level"
              v-model="yearLevelId"
              class="field-control"
              :disabled="props.submitting || !academicYearId || !yearLevelOptions.length"
            >
              <option :value="null">Select year level</option>
              <option v-for="option in yearLevelOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>

          <div class="form-field">
            <label class="field-label" for="major">
              Major <span class="required-mark">*</span>
            </label>
            <select
              id="major"
              v-model="majorId"
              class="field-control"
              :disabled="props.submitting || !yearLevelId || !majorOptions.length"
            >
              <option :value="null">Select major</option>
              <option v-for="option in majorOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>

          <div class="form-field">
            <label class="field-label" for="semester">
              Semester <span class="required-mark">*</span>
            </label>
            <select
              id="semester"
              v-model="semesterId"
              class="field-control"
              :disabled="props.submitting || !majorId || !semesterOptions.length"
            >
              <option :value="null">Select semester</option>
              <option v-for="option in semesterOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="form-field">
          <label class="field-label" for="subject-offering">
            Subject <span class="required-mark">*</span>
          </label>
          <select
            id="subject-offering"
            v-model="subjectOfferingId"
            class="field-control"
            :disabled="props.submitting || !semesterId || !filteredSubjectOfferings.length"
          >
            <option :value="null">Select subject</option>
            <option
              v-for="offering in filteredSubjectOfferings"
              :key="offering.id"
              :value="offering.id"
            >
              {{ offering.subject.subject_code }} — {{ offering.subject.subject_name }}
            </option>
          </select>
        </div>
        <small v-if="isSubjectOfferingsPending" class="field-hint">
          Loading official subjects...
        </small>
        <small v-else-if="isSubjectOfferingsError" class="field-message field-message--error">
          Official subjects could not be loaded.
        </small>
        <small v-else-if="!subjectOfferings.length" class="field-hint">
          No official subject offerings are available yet.
        </small>
        <small v-else-if="semesterId && !filteredSubjectOfferings.length" class="field-hint">
          No subjects match this academic selection.
        </small>
      </div>

      <CustomTopicPicker
        v-else
        v-model="customTopicId"
        :initial-topic="initialRequest?.custom_topic"
        :disabled="props.submitting"
      />

      <div class="form-field">
        <label class="field-label" for="request-title">
          Request Title <span class="required-mark">*</span>
        </label>
        <input
          id="request-title"
          v-model="title"
          class="field-control"
          type="text"
          maxlength="180"
          placeholder="e.g. Understanding MySQL Relationships"
          required
          :disabled="props.submitting"
        />
      </div>

      <div class="form-field">
        <label class="field-label" for="request-description">
          Description <span class="required-mark">*</span>
        </label>
        <textarea
          id="request-description"
          v-model="description"
          class="field-control description-control"
          rows="4"
          placeholder="Explain your learning goals"
          required
          :disabled="props.submitting"
        />
      </div>

      <div class="form-field">
        <label class="field-label" for="preferred-mode">
          Preferred Learning Mode <span class="required-mark">*</span>
        </label>
        <select
          id="preferred-mode"
          v-model="preferredMode"
          class="field-control"
          :disabled="props.submitting"
        >
          <option value="either">Either</option>
          <option value="online">Online</option>
          <option value="in_person">In person</option>
        </select>
      </div>


    </section>

    <section class="form-section availability-section" aria-labelledby="availability-section-title">
      <div class="section-heading">
        <span class="step-number">2</span>
        <h3 id="availability-section-title">Preferred Availability <span>— optional</span></h3>
      </div>

      <label class="schedule-toggle">
        <input
          v-model="includeSchedule"
          type="checkbox"
          :disabled="props.submitting"
          @change="toggleSchedule"
        />
        <span>
          <strong>Specify preferred date and time</strong>
          <small>Leave unchecked to share without date or time constraints.</small>
        </span>
      </label>

      <div v-if="includeSchedule" class="schedule-fields">
        <div class="date-range-group">
          <div class="date-range-heading">
            <strong>Preferred date</strong>
            <span>Choose one day or a date range.</span>
          </div>
          <div class="date-range-inputs">
            <div class="form-field">
              <label class="field-label" for="preferred-start-date">Start date *</label>
              <input
                id="preferred-start-date"
                v-model="preferredStartDate"
                class="field-control"
                type="date" 
                :min="todayStr"
                required
                :disabled="props.submitting"
              />
              <small v-if="startDateWarning" class="field-message field-message--warning" role="alert">
                {{ startDateWarning }}
              </small>
            </div>
            <span class="date-range-connector" aria-hidden="true">to</span>
            <div class="form-field">
              <label class="field-label optional-date-label" for="preferred-end-date">
                End date <span>Optional</span>
              </label>
              <input
                id="preferred-end-date"
                v-model="preferredEndDate"
                class="field-control"
                type="date"
                :min="preferredStartDate || todayStr"
                :disabled="props.submitting"
              />
              <small v-if="endDateWarning" class="field-message field-message--warning" role="alert">
                {{ endDateWarning }}
              </small>
            </div>
          </div>
          <small class="field-hint">Leave the end date blank if you only want to choose one day.</small>
        </div>
        <div class="form-field">
          <label class="field-label" for="preferred-start-time">From *</label>
          <input
            id="preferred-start-time"
            v-model="preferredStartTime"
            class="field-control"
            type="time"
            :min="preferredStartDate === todayStr ? nowTimeStr : undefined"
            required
            :disabled="props.submitting"
            @change="preferredEndTime = addOneHourToTimeString(preferredStartTime)"
          />
          <small v-if="startTimeWarning" class="field-message field-message--warning" role="alert">
            {{ startTimeWarning }}
          </small>
        </div>
        <div class="form-field">
          <label class="field-label" for="preferred-end-time">To *</label>
          <input
            id="preferred-end-time"
            v-model="preferredEndTime"
            class="field-control"
            type="time"
            :min="preferredStartTime || undefined"
            required
            :disabled="props.submitting"
          />
          <small v-if="endTimeWarning" class="field-message field-message--warning" role="alert">
            {{ endTimeWarning }}
          </small>
        </div>
      </div>

      <div class="form-field">
        <label class="field-label" for="additional-notes">Additional Notes (Optional)</label>
        <textarea
          id="additional-notes"
          v-model="additionalNotes"
          class="field-control notes-control"
          rows="3"
          placeholder="Anything else your learning partner should know?"
          :disabled="props.submitting"
        />
      </div>

      <p class="availability-note">
        Without a preferred date and time, matching can focus on your topic and learning mode.
      </p>
    </section>

    <footer class="form-footer">
      <BaseButton
        type="button"
        variant="outline"
        :disabled="props.submitting"
        @click="emit('cancel')"
      >
        Cancel
      </BaseButton>
      <BaseButton
        type="submit"
        :disabled="props.submitting
          || !title.trim()
          || !description.trim()
          || (topicSource === 'subject_offering' && !subjectOfferingId)
          || (topicSource === 'custom_topic' && !customTopicId)
          || (includeSchedule && hasScheduleWarning)"
      >
        {{ props.submitting
          ? initialRequest ? 'Updating...' : 'Posting...'
          : initialRequest ? 'Update Request' : 'Post Request' }}
      </BaseButton>
    </footer>
  </form>
</template>

<style scoped>
.request-form {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(300px, 0.85fr);
  gap: 14px 22px;
}

.form-section {
  display: grid;
  gap: 9px;
}

.request-form > .availability-section {
  padding-top: 0;
  padding-left: 24px;
  border-top: 0;
  border-left: 1px solid #e7e0f4;
}

.request-form > .form-footer {
  grid-column: 1 / -1;
}

.topic-source-toggle {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 6px;
  padding: 4px;
  border: 1px solid #ded7f2;
  border-radius: 12px;
  background: #faf8ff;
}

.topic-source-toggle button {
  min-height: 31px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: #655d7e;
  cursor: pointer;
  font: inherit;
  font-size: 12px;
  font-weight: 750;
}

.topic-source-toggle button.active {
  background: #7039dd;
  color: #fff;
}

.topic-source-toggle button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.academic-selection {
  display: grid;
  gap: 8px;
  padding: 10px;
  border: 1px solid #e9e3f7;
  border-radius: 12px;
  background: #fdfbff;
}

.academic-selection > .field-hint {
  margin: 0;
  line-height: 1.5;
}

.academic-filter-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.section-heading {
  display: flex;
  min-height: 42px;
  align-items: center;
  gap: 10px;
  padding: 9px 13px;
  border-radius: 17px;
  background: #f4edff;
  color: #6231cc;
}

.section-heading h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 800;
}

.section-heading h3 span {
  font-weight: 650;
}

.step-number {
  display: grid;
  flex: none;
  width: 30px;
  height: 30px;
  place-items: center;
  border-radius: 50%;
  background: #7138df;
  color: #fff;
  font-weight: 800;
}

.form-field {
  display: grid;
  gap: 5px;
}

.field-label {
  color: var(--text);
  font-size: 12px;
  font-weight: 750;
}

.required-mark {
  color: #dc4860;
}

.field-control {
  width: 100%;
  min-height: 35px;
  padding: 6px 10px;
  border: 1px solid #dcd8ef;
  border-radius: 9px;
  background: #fff;
  color: var(--text);
  font: inherit;
}

.field-control::placeholder {
  color: #9699aa;
}

.field-control:focus-visible {
  border-color: var(--purple);
  outline: 2px solid #6c2dea30;
  outline-offset: 1px;
}

textarea.field-control {
  resize: vertical;
}

.description-control {
  min-height: 62px;
}

.notes-control {
  min-height: 50px;
}

.availability-section {
  padding-top: 0;
  border-top: 1px solid #e7e0f4;
}

.schedule-toggle {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 9px;
  border: 1px solid #e2dafa;
  border-radius: 13px;
  background: #fbfaff;
  cursor: pointer;
}

.schedule-toggle input {
  flex: none;
  width: 19px;
  height: 19px;
  margin: 1px 0 0;
  accent-color: #7138df;
}

.schedule-toggle span {
  display: grid;
  gap: 4px;
}

.schedule-toggle strong {
  color: var(--text);
  font-size: 14px;
}

.schedule-toggle small {
  color: var(--muted);
  font-size: 12px;
}

.schedule-fields {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.date-range-group {
  display: grid;
  grid-column: 1 / -1;
  gap: 8px;
  min-width: 0;
  padding: 10px;
  border: 1px solid #e2dafa;
  border-radius: 13px;
  background: #fcfbff;
}

.date-range-heading {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 5px 10px;
}

.date-range-heading strong {
  color: var(--text);
  font-size: 13px;
}

.date-range-heading > span,
.field-hint {
  color: var(--muted);
  font-size: 12px;
}

.date-range-inputs {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
  align-items: end;
  gap: 10px;
  min-width: 0;
}

.date-range-connector {
  display: grid;
  width: 30px;
  height: 48px;
  place-items: center;
  color: #8a829f;
  font-size: 12px;
}

.optional-date-label {
  display: flex;
  align-items: center;
  gap: 7px;
}

.optional-date-label span {
  padding: 2px 6px;
  border-radius: 999px;
  background: #f1eaff;
  color: #7043c4;
  font-size: 10px;
  font-weight: 700;
}

.field-message--warning {
  color: var(--amber);
}

.availability-note {
  margin: 0;
  padding: 9px 11px;
  border: 1px solid #b7f0d4;
  border-radius: 12px;
  background: #effcf5;
  color: #16734e;
  font-size: 12px;
  line-height: 1.55;
}

.form-footer {
  position: sticky;
  bottom: 0;
  z-index: 3;
  display: flex;
  justify-content: flex-end;
  gap: 9px;
  margin: 0;
  padding: 12px 0 0;
  border-top: 1px solid #e7e0f4;
  background: #fcfbff;
}

@media (max-width: 760px) {
  .request-form {
    grid-template-columns: 1fr;
  }

  .request-form > .availability-section {
    padding-top: 18px;
    padding-left: 0;
    border-top: 1px solid #e7e0f4;
    border-left: 0;
  }

  .request-form > .form-footer {
    grid-column: auto;
  }

  .academic-filter-grid {
    grid-template-columns: 1fr;
  }

  .schedule-fields {
    grid-template-columns: 1fr;
  }

  .date-range-group {
    grid-column: auto;
    padding: 13px;
  }

  .date-range-inputs {
    grid-template-columns: minmax(0, 1fr);
    gap: 11px;
  }

  .date-range-connector {
    display: none;
  }

  .form-footer {
    padding: 10px 0 0;
  }
}
</style>
