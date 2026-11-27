<script setup lang="ts">
import { computed, nextTick, ref } from 'vue'
import { Download, X } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type {
  StudyMaterial,
  StudyMaterialFileType,
} from '../interfaces/studyMaterial.interface'
import {
  downloadStudyMaterial,
  fetchStudyMaterialPreviewUrl,
} from '../api/studyMaterial.api'

type PreviewKind = 'pdf' | 'office' | 'unsupported'

const PDF_TYPES = new Set<StudyMaterialFileType>(['pdf'])
const OFFICE_TYPES = new Set<StudyMaterialFileType>(['doc', 'docx', 'ppt', 'pptx'])
const OFFICE_VIEWER_URL = 'https://view.officeapps.live.com/op/embed.aspx?src='

const dialog = ref<HTMLDialogElement | null>(null)
const material = ref<StudyMaterial | null>(null)
const previewUrl = ref('')
const officePreviewUrl = ref('')
const isLoading = ref(false)
const isDownloading = ref(false)
const errorMessage = ref('')

const previewKind = computed<PreviewKind>(() => {
  if (!material.value) return 'unsupported'
  if (PDF_TYPES.has(material.value.file_type)) return 'pdf'
  if (OFFICE_TYPES.has(material.value.file_type)) return 'office'
  return 'unsupported'
})

const officeViewerSrc = computed(() =>
  officePreviewUrl.value
    ? `${OFFICE_VIEWER_URL}${encodeURIComponent(officePreviewUrl.value)}`
    : '',
)

async function open(selectedMaterial: StudyMaterial): Promise<void> {
  reset()
  material.value = selectedMaterial
  await nextTick()
  dialog.value?.showModal()

  if (previewKind.value === 'pdf') {
    await loadPdf(selectedMaterial)
    return
  }

  if (previewKind.value === 'office') {
    await loadOfficeDocument(selectedMaterial)
    return
  }

  errorMessage.value =
    'Preview is not available for this file type. Download the file to open it.'
}

function close(): void {
  dialog.value?.close()
  reset()
}

async function loadPdf(selectedMaterial: StudyMaterial): Promise<void> {
  isLoading.value = true

  try {
    const blob = await downloadStudyMaterial(selectedMaterial.id)
    if (material.value?.id !== selectedMaterial.id) return

    previewUrl.value = URL.createObjectURL(
      blob.type === 'application/pdf'
        ? blob
        : new Blob([blob], { type: 'application/pdf' }),
    )
  } catch {
    if (material.value?.id === selectedMaterial.id) {
      errorMessage.value = 'The PDF preview could not be loaded. Please download the file instead.'
    }
  } finally {
    if (material.value?.id === selectedMaterial.id) {
      isLoading.value = false
    }
  }
}

async function loadOfficeDocument(selectedMaterial: StudyMaterial): Promise<void> {
  isLoading.value = true

  try {
    const url = await fetchStudyMaterialPreviewUrl(selectedMaterial.id)
    if (material.value?.id !== selectedMaterial.id) return

    officePreviewUrl.value = url
  } catch {
    if (material.value?.id === selectedMaterial.id) {
      errorMessage.value = 'The document preview could not be loaded. Please download the file instead.'
    }
  } finally {
    if (material.value?.id === selectedMaterial.id) {
      isLoading.value = false
    }
  }
}

function reset(): void {
  revokePreviewUrl()
  officePreviewUrl.value = ''
  material.value = null
  isLoading.value = false
  errorMessage.value = ''
}

function revokePreviewUrl(): void {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = ''
  }
}

async function download(): Promise<void> {
  if (!material.value || isDownloading.value) return

  const target = material.value
  isDownloading.value = true

  try {
    const blob = await downloadStudyMaterial(target.id)
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')

    link.href = url
    link.download = target.original_filename
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
  } catch {
    errorMessage.value = 'The file could not be downloaded. Please try again.'
  } finally {
    isDownloading.value = false
  }
}

defineExpose({ open, close })
</script>

<template>
  <dialog
    ref="dialog"
    class="preview-dialog"
    aria-labelledby="material-preview-title"
    @cancel.prevent="close"
  >
    <section v-if="material" class="preview-shell">
      <header class="preview-heading">
        <div>
          <p class="eyebrow">{{ material.file_type.toUpperCase() }} PREVIEW</p>
          <h2 id="material-preview-title">{{ material.title }}</h2>
        </div>
        <div class="preview-actions">
          <BaseButton
            type="button"
            size="sm"
            variant="ghost"
            title="Download file"
            :aria-label="`Download ${material.original_filename}`"
            :disabled="isDownloading"
            @click="download"
          >
            <Download :size="15" aria-hidden="true" />
          </BaseButton>
          <button
            type="button"
            class="close-button"
            aria-label="Close preview"
            title="Close preview"
            @click="close"
          >
            <X :size="19" aria-hidden="true" />
          </button>
        </div>
      </header>

      <div class="preview-content">
        <p v-if="isLoading" class="preview-state" role="status">
          Loading {{ material.file_type.toUpperCase() }} preview...
        </p>
        <p v-else-if="errorMessage" class="preview-state preview-error" role="alert">
          {{ errorMessage }}
        </p>
        <iframe
          v-else-if="previewKind === 'pdf' && previewUrl"
          :src="previewUrl"
          title="PDF preview"
          allowfullscreen
        />
        <iframe
          v-else-if="previewKind === 'office' && officeViewerSrc"
          :src="officeViewerSrc"
          title="Document preview"
          allowfullscreen
        />
      </div>
    </section>
  </dialog>
</template>

<style scoped>
.preview-dialog {
  width: min(980px, calc(100vw - 28px));
  height: min(820px, calc(100dvh - 28px));
  max-width: none;
  max-height: none;
  margin: auto;
  padding: 0;
  overflow: hidden;
  border: 1px solid #e7e0f5;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 24px 80px #21134245;
}

.preview-dialog::backdrop {
  background: #17112b75;
  backdrop-filter: blur(7px);
}

.preview-shell {
  display: grid;
  height: 100%;
  grid-template-rows: auto minmax(0, 1fr);
}

.preview-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 16px 18px;
  border-bottom: 1px solid #eee8f7;
  background: linear-gradient(105deg, #f3edff, #e6ddff);
}

.eyebrow {
  margin: 0 0 5px;
  color: #6a43cc;
  font-size: 10px;
  font-weight: 850;
  letter-spacing: 0.13em;
}

.preview-heading h2 {
  max-width: 620px;
  margin: 0;
  color: #172244;
  font-size: 18px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.preview-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: none;
}

.close-button {
  display: grid;
  width: 36px;
  height: 36px;
  place-items: center;
  border: 0;
  border-radius: 10px;
  background: #fff;
  color: #6335c5;
  cursor: pointer;
}

.preview-content {
  min-height: 0;
  background: #eceaf1;
}

.preview-content iframe {
  width: 100%;
  height: 100%;
  border: 0;
  background: #fff;
}

.preview-state {
  display: grid;
  height: 100%;
  margin: 0;
  place-items: center;
  color: #77718b;
  font-size: 13px;
}

.preview-error {
  color: #b32d43;
}

@media (max-width: 560px) {
  .preview-dialog {
    width: 100vw;
    height: 100dvh;
    border-radius: 0;
  }

  .preview-heading h2 {
    max-width: 170px;
  }
}
</style>
