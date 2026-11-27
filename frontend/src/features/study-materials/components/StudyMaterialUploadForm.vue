<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { FileText, Trash2 } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type { CreateStudyMaterialPayload } from '../interfaces/studyMaterial.interface'
import { useSubjectOfferings } from '../../peer-learning/queries/useSubjectOfferings'
import type { SubjectOffering } from '../../peer-learning/interfaces/subjectOffering.interface'

const props = withDefaults(
  defineProps<{
    submitting?: boolean
  }>(),
  { submitting: false },
)

const emit = defineEmits<{
  submit: [payload: CreateStudyMaterialPayload]
  cancel: []
}>()

const title = ref('')
const description = ref('')
const selectedFile = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const fileError = ref('')
const materialSource = ref<'official' | 'custom'>('official')
const subjectOfferingId = ref<number | null>(null)
const academicYearId = ref<number | null>(null)
const yearLevelId = ref<number | null>(null)
const majorId = ref<number | null>(null)
const semesterId = ref<number | null>(null)
const { data: subjectOfferingPage, isPending: isSubjectOfferingsPending } = useSubjectOfferings()

const subjectOfferings = computed(() => subjectOfferingPage.value?.data ?? [])
const filteredSubjectOfferings = computed(() =>
  subjectOfferings.value.filter((offering) =>
    (!academicYearId.value || offering.academic_year.id === academicYearId.value)
    && (!yearLevelId.value || offering.year_level.id === yearLevelId.value)
    && (!majorId.value || offering.major.id === majorId.value)
    && (!semesterId.value || offering.semester.id === semesterId.value),
  ),
)

function uniqueOptions(
  offerings: SubjectOffering[],
  field: 'academic_year' | 'year_level' | 'major' | 'semester',
): Array<{ id: number; name: string }> {
  const seen = new Set<number>()

  return offerings.reduce<Array<{ id: number; name: string }>>((result, offering) => {
    const option = offering[field]
    if (option.id !== null && !seen.has(option.id)) {
      seen.add(option.id)
      result.push({ id: option.id, name: option.name ?? 'Unnamed' })
    }
    return result
  }, [])
}

const academicYearOptions = computed(() => uniqueOptions(subjectOfferings.value, 'academic_year'))
const yearLevelOptions = computed(() => uniqueOptions(
  subjectOfferings.value.filter((offering) =>
    !academicYearId.value || offering.academic_year.id === academicYearId.value,
  ),
  'year_level',
))
const majorOptions = computed(() => uniqueOptions(
  subjectOfferings.value.filter((offering) =>
    (!academicYearId.value || offering.academic_year.id === academicYearId.value)
    && (!yearLevelId.value || offering.year_level.id === yearLevelId.value),
  ),
  'major',
))
const semesterOptions = computed(() => uniqueOptions(
  subjectOfferings.value.filter((offering) =>
    (!academicYearId.value || offering.academic_year.id === academicYearId.value)
    && (!yearLevelId.value || offering.year_level.id === yearLevelId.value)
    && (!majorId.value || offering.major.id === majorId.value),
  ),
  'semester',
))

watch(academicYearId, () => {
  yearLevelId.value = null
  majorId.value = null
  semesterId.value = null
  subjectOfferingId.value = null
})
watch(yearLevelId, () => {
  majorId.value = null
  semesterId.value = null
  subjectOfferingId.value = null
})
watch(majorId, () => {
  semesterId.value = null
  subjectOfferingId.value = null
})
watch(semesterId, () => {
  subjectOfferingId.value = null
})

function selectMaterialSource(source: 'official' | 'custom'): void {
  materialSource.value = source
  subjectOfferingId.value = null
  if (source === 'custom') {
    academicYearId.value = null
    yearLevelId.value = null
    majorId.value = null
    semesterId.value = null
  }
}

const MAX_FILE_SIZE = 15 * 1024 * 1024
const ALLOWED_EXTENSIONS = new Set(['pdf', 'ppt', 'pptx', 'doc', 'docx'])

function handleFileChange(event: Event): void {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] ?? null

  fileError.value = ''
  selectedFile.value = null

  if (!file) return

  const extension = file.name.split('.').pop()?.toLocaleLowerCase() ?? ''

  if (!ALLOWED_EXTENSIONS.has(extension)) {
    fileError.value = 'Choose a PDF, PPT, PPTX, DOC, or DOCX file.'
    input.value = ''
    return
  }

  if (file.size <= 0) {
    fileError.value = 'The selected file is empty.'
    input.value = ''
    return
  }

  if (file.size > MAX_FILE_SIZE) {
    fileError.value = 'The file must be 15 MB or smaller.'
    input.value = ''
    return
  }

  selectedFile.value = file
}

function clearFile(): void {
  selectedFile.value = null
  fileError.value = ''

  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function formatFileSize(size: number): string {
  return size < 1024 * 1024
    ? `${Math.max(1, Math.round(size / 1024))} KB`
    : `${(size / (1024 * 1024)).toFixed(1)} MB`
}

function submitForm(): void {
  if (props.submitting) return

  const cleanTitle = title.value.trim()
  const selectedSubjectOfferingId = subjectOfferingId.value
  if (!cleanTitle || !selectedFile.value) return
  if (materialSource.value === 'official' && !selectedSubjectOfferingId) return

  emit('submit', {
    subject_offering_id: selectedSubjectOfferingId,
    title: cleanTitle,
    description: description.value.trim() || null,
    file: selectedFile.value,
  })
}
</script>

<template>
  <form class="upload-form" @submit.prevent="submitForm">
    <section class="upload-section" aria-labelledby="material-information-title">
      <div class="section-heading">
        <span class="section-icon" aria-hidden="true">
          <FileText :size="19" />
        </span>
        <div>
          <h3 id="material-information-title">Material information</h3>
          <p>Add a title and description for the resource.</p>
        </div>
      </div>

      <div class="form-field">
        <span class="field-label">Material type</span>
        <div class="source-toggle" role="group" aria-label="Material type">
          <button
            type="button"
            :class="{ active: materialSource === 'official' }"
            :disabled="props.submitting"
            @click="selectMaterialSource('official')"
          >
            Official Subject
          </button>
          <button
            type="button"
            :class="{ active: materialSource === 'custom' }"
            :disabled="props.submitting"
            @click="selectMaterialSource('custom')"
          >
            Custom Material
          </button>
        </div>
      </div>

      <div v-if="materialSource === 'official'" class="official-selection">
        <div class="academic-filter-grid">
          <div class="form-field">
            <label class="field-label" for="material-academic-year">Academic Year *</label>
            <select
              id="material-academic-year"
              v-model="academicYearId"
              class="field-control"
              :disabled="props.submitting || isSubjectOfferingsPending"
            >
              <option :value="null">Select academic year</option>
              <option v-for="option in academicYearOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>
          <div class="form-field">
            <label class="field-label" for="material-year-level">Year Level *</label>
            <select
              id="material-year-level"
              v-model="yearLevelId"
              class="field-control"
              :disabled="props.submitting || !academicYearId"
            >
              <option :value="null">Select year level</option>
              <option v-for="option in yearLevelOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>
          <div class="form-field">
            <label class="field-label" for="material-major">Major *</label>
            <select
              id="material-major"
              v-model="majorId"
              class="field-control"
              :disabled="props.submitting || !yearLevelId"
            >
              <option :value="null">Select major</option>
              <option v-for="option in majorOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>
          <div class="form-field">
            <label class="field-label" for="material-semester">Semester *</label>
            <select
              id="material-semester"
              v-model="semesterId"
              class="field-control"
              :disabled="props.submitting || !majorId"
            >
              <option :value="null">Select semester</option>
              <option v-for="option in semesterOptions" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="form-field">
          <label class="field-label" for="material-subject">Subject *</label>
          <select
            id="material-subject"
            v-model="subjectOfferingId"
            class="field-control"
            :disabled="props.submitting || !semesterId"
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
      </div>

      <div class="form-field">
        <label class="field-label" for="material-title">
          Material title <span class="required-mark">*</span>
        </label>
        <input
          id="material-title"
          v-model="title"
          class="field-control"
          type="text"
          maxlength="180"
          placeholder="e.g. Database Management lecture notes"
          autocomplete="off"
          required
          :disabled="props.submitting"
        />
      </div>

      <div class="form-field">
        <label class="field-label" for="material-description">
          Description <span class="optional-label">Optional</span>
        </label>
        <textarea
          id="material-description"
          v-model="description"
          class="field-control description-control"
          rows="4"
          maxlength="5000"
          placeholder="Briefly describe what this resource covers."
          :disabled="props.submitting"
        />
      </div>

    </section>

    <section class="upload-section file-section" aria-labelledby="material-file-title">
      <div class="section-heading">
        <span class="section-icon" aria-hidden="true">
          <FileText :size="19" />
        </span>
        <div>
          <h3 id="material-file-title">Choose a file</h3>
          <p>Supported formats: PDF, PPT, PPTX, DOC, and DOCX. Maximum size: 15 MB.</p>
        </div>
      </div>

      <div class="form-field">
        <label class="field-label" for="material-file">
          Study material file <span class="required-mark">*</span>
        </label>
        <input
          id="material-file"
          ref="fileInput"
          class="field-control file-control"
          type="file"
          accept=".pdf,.ppt,.pptx,.doc,.docx"
          required
          :disabled="props.submitting"
          @change="handleFileChange"
        />
      </div>

      <p v-if="fileError" class="file-error" role="alert">
        {{ fileError }}
      </p>

      <div v-if="selectedFile" class="selected-file">
        <span class="selected-file-icon" aria-hidden="true">
          <FileText :size="18" />
        </span>
        <div class="selected-file-copy">
          <strong>{{ selectedFile.name }}</strong>
          <span>{{ formatFileSize(selectedFile.size) }}</span>
        </div>
        <button
          type="button"
          class="remove-file-button"
          aria-label="Remove selected file"
          :disabled="props.submitting"
          @click="clearFile"
        >
          <Trash2 :size="17" aria-hidden="true" />
        </button>
      </div>
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
          || (materialSource === 'official' && !subjectOfferingId)
          || !title.trim()
          || !selectedFile"
      >
        {{ props.submitting ? 'Uploading...' : 'Upload Material' }}
      </BaseButton>
    </footer>
  </form>
</template>

<style scoped>
.upload-form {
  display: grid;
  gap: 24px;
  color: var(--text);
}

.upload-section {
  display: grid;
  gap: 17px;
}

.source-toggle {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  padding: 6px;
  border: 1px solid #e1daf3;
  border-radius: 12px;
  background: #faf8ff;
}

.source-toggle button {
  min-height: 42px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: #716987;
  font: inherit;
  font-size: 12px;
  font-weight: 750;
  cursor: pointer;
}

.source-toggle button.active {
  background: #fff;
  box-shadow: 0 4px 14px #3d1b7412;
  color: #6335c5;
}

.official-selection {
  display: grid;
  gap: 16px;
}

.academic-filter-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.section-heading {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 14px;
  background: #f4edff;
}

.section-icon {
  display: grid;
  flex: none;
  width: 38px;
  height: 38px;
  place-items: center;
  border-radius: 11px;
  background: #fff;
  color: #7138df;
}

.section-heading h3 {
  margin: 0;
  color: #302252;
  font-size: 15px;
}

.section-heading p {
  margin: 4px 0 0;
  color: #77718f;
  font-size: 12px;
  line-height: 1.5;
}

.form-field {
  display: grid;
  gap: 7px;
}

.field-label {
  color: var(--text);
  font-size: 13px;
  font-weight: 750;
}

.required-mark {
  color: #dc4860;
}

.optional-label {
  margin-left: 5px;
  color: var(--muted);
  font-size: 11px;
  font-weight: 500;
}

.field-control {
  width: 100%;
  min-height: 47px;
  padding: 10px 13px;
  border: 1px solid #dcd8ef;
  border-radius: 11px;
  background: #fff;
  color: var(--text);
  font: inherit;
}

.field-control::placeholder {
  color: #9690a8;
}

.field-control:focus-visible {
  border-color: #956af0;
  outline: 2px solid #6c2dea30;
  outline-offset: 1px;
}

.field-control:disabled {
  background: #f7f6fb;
  cursor: not-allowed;
}

.description-control {
  min-height: 105px;
  resize: vertical;
}

.file-control {
  padding: 9px;
}

.file-control::file-selector-button {
  margin-right: 10px;
  padding: 8px 11px;
  border: 1px solid #ddd3f4;
  border-radius: 8px;
  background: #f7f3ff;
  color: #6335c5;
  font: inherit;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.file-error {
  margin: -7px 0 0;
  color: #c92f49;
  font-size: 12px;
}

.selected-file {
  display: flex;
  align-items: center;
  gap: 11px;
  min-width: 0;
  padding: 12px;
  border: 1px solid #e5def4;
  border-radius: 12px;
  background: #fcfbff;
}

.selected-file-icon {
  display: grid;
  flex: none;
  width: 36px;
  height: 36px;
  place-items: center;
  border-radius: 10px;
  background: #f1eaff;
  color: #6335c5;
}

.selected-file-copy {
  display: grid;
  flex: 1;
  gap: 3px;
  min-width: 0;
  color: var(--text);
  font-size: 12px;
}

.selected-file-copy strong {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.selected-file-copy span {
  color: var(--muted);
}

.remove-file-button {
  display: grid;
  flex: none;
  width: 34px;
  height: 34px;
  place-items: center;
  border: 1px solid #f4cbd2;
  border-radius: 9px;
  background: #fff7f8;
  color: #c92f49;
  cursor: pointer;
}

.remove-file-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.remove-file-button:focus-visible {
  outline: 2px solid #c92f49;
  outline-offset: 2px;
}

.form-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 15px;
  border-top: 1px solid #e7e0f4;
}

@media (max-width: 560px) {
  .section-heading {
    align-items: flex-start;
    padding: 12px;
  }

  .form-footer {
    position: sticky;
    bottom: 0;
    z-index: 2;
    margin: 0 -16px -16px;
    padding: 12px 16px;
    background: #fcfbff;
  }
}
</style>
