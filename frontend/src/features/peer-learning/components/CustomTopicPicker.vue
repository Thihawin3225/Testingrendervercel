<script setup lang="ts">
import { computed, ref } from 'vue'
import { Plus, X } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type { CustomTopic } from '../interfaces/customTopic.interface'
import { useCreateCustomTopic } from '../mutations/useCreateCustomTopic'
import { useDeleteCustomTopic } from '../mutations/useDeleteCustomTopic'
import { useRecentCustomTopics } from '../queries/useRecentCustomTopics'
import { getPeerLearningApiErrorMessage } from '../utils/apiErrorMessage'

const props = withDefaults(
  defineProps<{
    modelValue: number | null
    disabled?: boolean
    initialTopic?: Pick<CustomTopic, 'id' | 'title'> | null
  }>(),
  { disabled: false },
)

const emit = defineEmits<{
  'update:modelValue': [value: number | null]
}>()

const searchText = ref('')
const suggestionTitle = ref('')
const showSuggestForm = ref(false)
const selectedTitle = ref('')

const {
  data: recentTopics,
  isPending: isTopicsPending,
  isError: isTopicsError,
  error: topicsError,
} = useRecentCustomTopics()

const {
  mutate: createTopic,
  isPending: isCreatingTopic,
  isError: isCreateTopicError,
  error: createTopicError,
  reset: resetCreateTopic,
} = useCreateCustomTopic()

const {
  mutate: deleteTopic,
  isPending: isDeletingTopic,
  isError: isDeleteTopicError,
  error: deleteTopicError,
  reset: resetDeleteTopic,
} = useDeleteCustomTopic()

const deletingTopicId = ref<number | null>(null)
const deleteTopicNotice = ref('')

const normalizedSearch = computed(() => searchText.value.trim().toLocaleLowerCase())
const selectedTopicLabel = computed(() =>
  selectedTitle.value
  || (props.initialTopic?.id === props.modelValue ? props.initialTopic.title : '')
  || recentTopics.value?.find((topic) => topic.id === props.modelValue)?.title
  || 'Selected topic',
)
const visibleTopics = computed(() => {
  const topics = recentTopics.value ?? []
  if (!normalizedSearch.value) return topics

  return topics.filter((topic) => {
    const searchableText = [topic.title, topic.subtitle, topic.description]
      .filter(Boolean)
      .join(' ')
      .toLocaleLowerCase()

    return searchableText.includes(normalizedSearch.value)
  })
})

const recentTopicsErrorMessage = computed(() =>
  getPeerLearningApiErrorMessage(topicsError.value, 'load recent topics'),
)
const createTopicErrorMessage = computed(() =>
  getPeerLearningApiErrorMessage(createTopicError.value, 'suggest this topic'),
)
const deleteTopicErrorMessage = computed(() =>
  getPeerLearningApiErrorMessage(deleteTopicError.value, 'delete this topic'),
)

function selectTopic(topic: CustomTopic): void {
  selectedTitle.value = topic.title
  // searchText.value = topic.title
  emit('update:modelValue', topic.id)
}

function clearSelection(): void {
  selectedTitle.value = ''
  searchText.value = ''
  emit('update:modelValue', null)
}

function removeTopic(topic: CustomTopic, event: Event): void {
  event.stopPropagation() 
  if (!topic.is_mine || props.disabled || isDeletingTopic.value) return

  const shouldDelete = window.confirm(
    `Delete your custom topic "${topic.title}"?`,
  )

  if (!shouldDelete) return

  resetDeleteTopic()
  deleteTopicNotice.value = ''
  deletingTopicId.value = topic.id

  deleteTopic(topic.id, {
    onSuccess: () => {
      deleteTopicNotice.value = 'Your custom topic was deleted.'

      if (props.modelValue === topic.id) {
        clearSelection()
      }
    },
    onSettled: () => {
      deletingTopicId.value = null
    },
  })
}

function openSuggestForm(): void {
  resetCreateTopic()
  suggestionTitle.value = searchText.value.trim()
  showSuggestForm.value = !showSuggestForm.value
}

function suggestTopic(): void {
  const title = suggestionTitle.value.trim()
  if (!title || isCreatingTopic.value) return

  createTopic(
    { title },
    {
      onSuccess: (topic) => {
        selectTopic(topic)
        showSuggestForm.value = false
      },
    },
  )
}
</script>

<template>
  <section class="topic-picker" aria-labelledby="topic-picker-label">
    <label id="topic-picker-label" class="field-label" for="topic-search">
      Search Skills / Topics <span class="required-mark">*</span>
    </label>
    <input
      id="topic-search"
      v-model="searchText"
      class="field-control"
      type="search"
      maxlength="255"
      :disabled="props.disabled"
      placeholder="Search Laravel, MySQL, Flutter..."
      autocomplete="off"
    />

    <div
      v-if="visibleTopics.length"
      class="topic-chip-list"
      aria-label="Recent shared topics and your topics"
    >
      <div
        v-for="topic in visibleTopics"
        :key="topic.id"
        class="topic-chip-group"
      >
       
        <button
          class="topic-chip"
          :class="{
            'topic-chip--selected': props.modelValue === topic.id,
            'topic-chip--mine': topic.is_mine,
            'topic-chip--pending': deletingTopicId === topic.id,
          }"
          type="button"
          :aria-pressed="props.modelValue === topic.id"
          :disabled="props.disabled"
          @click="selectTopic(topic)"
        >
          <span>{{ topic.title }}</span>
          
         
          <span
            v-if="topic.is_mine"
            class="topic-chip-remove"
            :aria-label="`Delete your topic ${topic.title}`"
            :title="`Delete ${topic.title}`"
            @click="(e) => removeTopic(topic, e)"
          >
            <X :size="13" aria-hidden="true" />
          </span>
        </button>
      </div>
    </div>
    <p v-else-if="isTopicsPending" class="field-hint" role="status">
      Loading recent shared topics and your topics...
    </p>
    <p v-else-if="isTopicsError" class="field-message field-message--error" role="alert">
      {{ recentTopicsErrorMessage }}
    </p>
    <p v-else class="field-hint">
      No matching recent topics or topics you created. You can suggest a new one.
    </p>

    <p v-if="deleteTopicNotice" class="field-message field-message--success" role="status">
      {{ deleteTopicNotice }}
    </p>
    <p v-if="isDeleteTopicError" class="field-message field-message--error" role="alert">
      {{ deleteTopicErrorMessage }}
    </p>

    <div v-if="props.modelValue" class="selected-topic">
      <span class="field-label">Your Selected Topic</span>
      <span class="selected-topic-chip">
        {{ selectedTopicLabel }}
        <button
          type="button"
          aria-label="Remove selected topic"
          :disabled="props.disabled"
          @click="clearSelection"
        >
          <X :size="13" aria-hidden="true" />
        </button>
      </span>
    </div>

    <BaseButton
      class="suggest-topic-button"
      variant="outline"
      type="button"
      :disabled="props.disabled"
      @click="openSuggestForm"
    >
      <Plus :size="15" aria-hidden="true" />
      {{ showSuggestForm ? 'Cancel Topic Suggestion' : 'Suggest New Topic' }}
    </BaseButton>

    <div v-if="showSuggestForm" class="suggest-topic-form">
      <label class="field-label" for="suggest-topic-title">New topic name</label>
      <div class="suggest-topic-row">
        <input
          id="suggest-topic-title"
          v-model="suggestionTitle"
          class="field-control"
          type="text"
          maxlength="255"
          placeholder="Enter a topic to share"
          :disabled="props.disabled || isCreatingTopic"
          @keydown.enter.prevent="suggestTopic"
        />
        <BaseButton
          type="button"
          size="sm"
          :disabled="props.disabled || !suggestionTitle.trim() || isCreatingTopic"
          @click="suggestTopic"
        >
          {{ isCreatingTopic ? 'Adding...' : 'Add Topic' }}
        </BaseButton>
      </div>
      <p v-if="isCreateTopicError" class="field-message field-message--error" role="alert">
        {{ createTopicErrorMessage }}
      </p>
    </div>
  </section>
</template>

<style scoped>
.topic-picker {
  display: grid;
  gap: 10px;
}

.field-label {
  color: var(--text);
  font-size: 13px;
  font-weight: 750;
}

.required-mark {
  color: #dc4860;
}

.field-hint,
.field-message {
  margin: 0;
  color: var(--muted);
  font-size: 12px;
}

.field-message--error {
  color: var(--danger);
}

.field-message--success {
  color: var(--success);
}

.field-message--warning {
  color: var(--amber);
}

.field-control {
  width: 100%;
  min-width: 0;
  min-height: 48px;
  padding: 11px 14px;
  border: 1px solid #dcd8ef;
  border-radius: 11px;
  background: #fff;
  color: var(--text);
  font: inherit;
}

.field-control:focus-visible {
  border-color: var(--purple);
  outline: 2px solid #6c2dea30;
  outline-offset: 1px;
}

.topic-chip-list {
  display: flex;
  flex-wrap: wrap;
  gap: 9px;
}

.topic-chip-group {
  display: inline-flex;
  align-items: stretch;
  gap: 4px;
}

.topic-chip {
  display: inline-flex;
  min-height: 42px;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border: 1px solid #ded6fa;
  border-radius: 12px;
  background: #fff;
  color: #51447b;
  font: inherit;
  cursor: pointer;
}

.topic-chip:hover:not(:disabled) {
  border-color: #8c5bea;
  background: #f8f4ff;
}

.topic-chip--mine {
  border-color: #ded6fa;
  background: #f1ddf8;
  color: #5727ce;
}

.topic-chip--mine:hover:not(:disabled) {
  border-color: #8c5bea;
  background: #f8f4ff;
}


.topic-chip-remove {
  display: grid;
  width: 20px;
  height: 20px;
  place-items: center;
  border-radius: 50%;
  background: transparent;
  color: inherit;
  transition: background 0.2s;
}

.topic-chip-remove:hover {
  background: rgba(87, 39, 206, 0.12);
  color: #c22d48; 
}

.topic-chip--pending {
  opacity: 0.45;
}

.topic-chip:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.selected-topic {
  display: grid;
  justify-items: start;
  gap: 6px;
}

.selected-topic-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 11px;
  border-radius: 999px;
  background: #eee7ff;
  color: #6032c6;
  font-size: 12px;
  font-weight: 750;
}

.selected-topic-chip button {
  display: grid;
  width: 20px;
  height: 20px;
  place-items: center;
  border: 0;
  border-radius: 50%;
  background: transparent;
  color: inherit;
  cursor: pointer;
}

.selected-topic-chip button:hover:not(:disabled) {
  background: #dcd0ff;
}

.suggest-topic-button {
  justify-self: start;
}

.suggest-topic-form {
  display: grid;
  gap: 7px;
  padding: 13px;
  border: 1px solid #e2dafa;
  border-radius: 12px;
  background: #faf8ff;
}

.suggest-topic-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 8px;
}

@media (max-width: 520px) {
  .suggest-topic-row {
    grid-template-columns: 1fr;
  }
}
</style>