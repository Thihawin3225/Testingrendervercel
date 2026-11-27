<script setup lang="ts">
import { computed } from 'vue'
import { Bookmark, BookmarkCheck } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const props = withDefaults(
  defineProps<{
    saved: boolean
    materialTitle: string
    disabled?: boolean
  }>(),
  { disabled: false },
)

const emit = defineEmits<{
  toggle: []
}>()

const actionLabel = computed(() =>
  props.saved
    ? `Remove ${props.materialTitle} from bookmarks`
    : `Save ${props.materialTitle} to bookmarks`,
)
</script>

<template>
  <BaseButton
    :variant="saved ? 'primary' : 'outline'"
    size="sm"
    class="bookmark-button"
    :class="{ 'bookmark-button--saved': saved }"
    :title="saved ? 'Remove from bookmarks' : 'Save to bookmarks'"
    :aria-label="actionLabel"
    :aria-pressed="saved"
    :disabled="disabled"
    @click="emit('toggle')"
  >
    <BookmarkCheck v-if="saved" :size="15" aria-hidden="true" />
    <Bookmark v-else :size="15" aria-hidden="true" />
  </BaseButton>
</template>

<style scoped>
.bookmark-button {
  transition: transform 160ms ease, box-shadow 160ms ease, filter 160ms ease;
}

.bookmark-button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 5px 14px #5727ce24;
}

.bookmark-button:not(.bookmark-button--saved):hover:not(:disabled) {
  border-color: #8b5cf6;
  background: #f8f5ff;
  color: #5420b8;
}

.bookmark-button--saved {
  box-shadow: 0 3px 10px #5727ce30;
}

.bookmark-button--saved:hover:not(:disabled) {
  box-shadow: 0 7px 18px #5727ce3d;
}
</style>
