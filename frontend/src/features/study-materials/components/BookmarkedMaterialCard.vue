<script setup lang="ts">
import { Download, Eye } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import StudyMaterialCard from './StudyMaterialCard.vue'
import StudyMaterialBookmarkButton from './StudyMaterialBookmarkButton.vue'
import type { BookmarkedStudyMaterial } from '../interfaces/bookmarkedMaterial.interface'

defineProps<{
  material: BookmarkedStudyMaterial
}>()

const emit = defineEmits<{
  view: [material: BookmarkedStudyMaterial]
  preview: [material: BookmarkedStudyMaterial]
  removeBookmark: [material: BookmarkedStudyMaterial]
  download: [material: BookmarkedStudyMaterial]
}>()
</script>

<template>
  <StudyMaterialCard :material="material">
    <template #primary-action>
      <BaseButton
        size="sm"
        variant="ghost"
        :aria-label="`Preview ${material.title}`"
        @click="emit('preview', material)"
      >
        <Eye :size="15" aria-hidden="true" />Preview
      </BaseButton>
    </template>
    <template #actions>
      <StudyMaterialBookmarkButton
        saved
        :material-title="material.title"
        @toggle="emit('removeBookmark', material)"
      />
      <BaseButton
        size="sm"
        variant="ghost"
        title="Download material"
        :aria-label="`Download ${material.title}`"
        @click="emit('download', material)"
      >
        <Download :size="15" aria-hidden="true" />
      </BaseButton>
    </template>
  </StudyMaterialCard>
</template>
