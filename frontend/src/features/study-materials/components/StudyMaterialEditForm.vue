<script setup lang="ts">
import { ref } from 'vue'
import { FileText, Trash2 } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type {
  StudyMaterial,
  UpdateStudyMaterialPayload,
} from '../interfaces/studyMaterial.interface'

const props = withDefaults(
  defineProps<{
    material: StudyMaterial
    submitting?: boolean
  }>(),
  { submitting: false },
)

const emit = defineEmits<{
  submit: [payload: UpdateStudyMaterialPayload]
  cancel: []
}>()

const title = ref(props.material.title)
const description = ref(props.material.description ?? '')
const selectedFile = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const fileError = ref('')
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
  if (file.size <= 0 || file.size > MAX_FILE_SIZE) {
    fileError.value = 'The replacement file must be between 1 byte and 15 MB.'
    input.value = ''
    return
  }

  selectedFile.value = file
}

function clearFile(): void {
  selectedFile.value = null
  fileError.value = ''
  if (fileInput.value) fileInput.value.value = ''
}

function submitForm(): void {
  if (props.submitting) return

  const cleanTitle = title.value.trim()
  if (!cleanTitle) return

  emit('submit', {
    id: props.material.id,
    title: cleanTitle,
    description: description.value.trim() || null,
    file: selectedFile.value ?? undefined,
  })
}
</script>

<template>
  <form class="edit-form" @submit.prevent="submitForm">
    <div class="edit-heading">
      <span class="edit-icon" aria-hidden="true"><FileText :size="18" /></span>
      <div>
        <h3 id="edit-upload-title">Edit study material</h3>
        <p>Update the details or replace the uploaded file.</p>
      </div>
    </div>

    <label class="edit-field">
      <span>Title</span>
      <input v-model="title" type="text" maxlength="180" required :disabled="props.submitting" />
    </label>

    <label class="edit-field">
      <span>Description</span>
      <textarea v-model="description" rows="4" maxlength="5000" :disabled="props.submitting" />
    </label>

    <label class="edit-field">
      <span>Replace file <small>(optional)</small></span>
      <input
        ref="fileInput"
        type="file"
        accept=".pdf,.ppt,.pptx,.doc,.docx"
        :disabled="props.submitting"
        @change="handleFileChange"
      />
    </label>

    <p v-if="fileError" class="edit-error" role="alert">{{ fileError }}</p>
    <div v-if="selectedFile" class="selected-file">
      <span>{{ selectedFile.name }}</span>
      <button type="button" aria-label="Remove replacement file" @click="clearFile">
        <Trash2 :size="16" aria-hidden="true" />
      </button>
    </div>

    <footer class="edit-actions">
      <BaseButton type="button" variant="outline" :disabled="props.submitting" @click="emit('cancel')">
        Cancel
      </BaseButton>
      <BaseButton type="submit" :disabled="props.submitting">
        {{ props.submitting ? 'Saving...' : 'Save changes' }}
      </BaseButton>
    </footer>
  </form>
</template>

<style scoped>
.edit-form {
  display: grid;
  gap: 16px;
  padding: 22px;
}

.edit-heading {
  display: flex;
  align-items: center;
  gap: 10px;
}

.edit-icon {
  display: grid;
  width: 38px;
  height: 38px;
  place-items: center;
  border-radius: 10px;
  background: #f2ebff;
  color: #7138df;
}

.edit-heading h3,
.edit-heading p {
  margin: 0;
}

.edit-heading h3 {
  color: #172244;
  font-size: 17px;
}

.edit-heading p {
  margin-top: 4px;
  color: #77718b;
  font-size: 12px;
}

.edit-field {
  display: grid;
  gap: 7px;
  color: #4b4560;
  font-size: 12px;
  font-weight: 700;
}

.edit-field input,
.edit-field textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 10px 11px;
  border: 1px solid #ded7f2;
  border-radius: 9px;
  color: #292345;
  font: inherit;
  font-weight: 400;
}

.edit-field textarea {
  resize: vertical;
}

.edit-error {
  margin: 0;
  color: #b32d43;
  font-size: 12px;
}

.selected-file {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 9px 11px;
  border-radius: 9px;
  background: #f5f1ff;
  color: #5836a4;
  font-size: 12px;
}

.selected-file button {
  display: grid;
  padding: 4px;
  border: 0;
  background: transparent;
  color: #7138df;
  cursor: pointer;
}

.edit-actions {
  display: flex;
  justify-content: flex-end;
  gap: 9px;
  padding-top: 4px;
}
</style>
