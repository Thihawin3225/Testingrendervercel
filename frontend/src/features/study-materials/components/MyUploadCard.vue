<script setup lang="ts">
import { Eye, Pencil, Trash2 } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import StudyMaterialCard from './StudyMaterialCard.vue'
import type { StudyMaterial } from '../interfaces/studyMaterial.interface'

defineProps<{
  material: StudyMaterial
}>()

const emit = defineEmits<{
  view: [material: StudyMaterial]
  preview: [material: StudyMaterial]
  edit: [material: StudyMaterial]
  delete: [material: StudyMaterial]
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
      <BaseButton
        size="sm"
        variant="outline"
        :aria-label="`Edit or replace ${material.title}`"
        title="Edit or replace material"
        @click="emit('edit', material)"
      >
        <Pencil :size="15" aria-hidden="true" />
      </BaseButton>
      <BaseButton
        size="sm"
        variant="danger"
        :aria-label="`Delete ${material.title}`"
        title="Delete material"
        @click="emit('delete', material)"
      >
        <Trash2 :size="15" aria-hidden="true" />
      </BaseButton>
    </template>
  </StudyMaterialCard>
</template>
