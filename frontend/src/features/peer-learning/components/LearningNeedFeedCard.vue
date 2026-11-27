<script setup lang="ts">
import { CalendarDays, Clock3, Eye, UserRound } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type { LearningNeedFeedItem } from '../interfaces/peerLearningRequest.interface'

defineProps<{
  request: LearningNeedFeedItem
}>()

const emit = defineEmits<{
  view: [request: LearningNeedFeedItem]
}>()

function formatDate(value: string): string {
  const [year, month, day] = value.slice(0, 10).split('-')
  return `${month}/${day}/${year}`
}
</script>

<template>
  <article class="feed-card">
    <header class="feed-card-top">
      <div class="feed-topic-author">
        <span class="topic-chip">
          {{ request.topic_title || request.custom_topic?.title || 'Peer learning topic' }}
        </span>
        <span class="feed-author">
          <UserRound :size="14" aria-hidden="true" />
          {{ request.learner.name }}
        </span>
      </div>
      <span class="request-status">{{ request.status }}</span>
    </header>

    <div class="feed-card-copy">
      <h2>{{ request.title }}</h2>
      <p v-if="request.description">{{ request.description }}</p>
    </div>

    <div
      v-if="request.preferred_start_date || (request.preferred_start_time && request.preferred_end_time)"
      class="feed-card-schedule"
    >
      <span v-if="request.preferred_start_date" class="schedule-item">
        <CalendarDays :size="14" aria-hidden="true" />
        {{ formatDate(request.preferred_start_date) }}
        <template v-if="request.preferred_end_date">
          to {{ formatDate(request.preferred_end_date) }}
        </template>
      </span>
      <span
        v-if="request.preferred_start_time && request.preferred_end_time"
        class="schedule-item"
      >
        <Clock3 :size="14" aria-hidden="true" />
        {{ request.preferred_start_time }} to {{ request.preferred_end_time }}
      </span>
    </div>

    <div class="feed-card-meta">
      <span class="request-mode">{{ request.preferred_mode.replace('_', ' ') }}</span>
    </div>

    <footer class="feed-card-actions">
      <BaseButton variant="ghost" size="sm" @click="emit('view', request)">
        <Eye :size="15" aria-hidden="true" />
        Details
      </BaseButton>
    </footer>
  </article>
</template>

<style scoped>
.feed-card {
  display: flex;
  min-width: 0;
  height: 100%;
  min-height: 300px;
  flex-direction: column;
  gap: 14px;
  padding: 18px;
  border: 1px solid #e6e1f2;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 8px 22px #2413440a;
}

.feed-card-top,
.feed-topic-author,
.feed-card-meta,
.feed-card-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.feed-card-top {
  align-items: flex-start;
  justify-content: space-between;
}

.feed-topic-author {
  flex-wrap: wrap;
}

.topic-chip {
  max-width: min(100%, 240px);
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
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #655d7e;
  font-size: 12px;
  font-weight: 650;
  overflow-wrap: anywhere;
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

.feed-card-copy {
  min-width: 0;
  overflow-wrap: anywhere;
}

.feed-card-copy h2 {
  display: -webkit-box;
  overflow: hidden;
  margin: 0;
  color: var(--text);
  font-size: 16px;
  line-height: 1.4;
  overflow-wrap: anywhere;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.feed-card-copy p {
  display: -webkit-box;
  overflow: hidden;
  margin: 7px 0 0;
  color: var(--muted);
  line-height: 1.55;
  overflow-wrap: anywhere;
  white-space: pre-wrap;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
}

.feed-card-meta {
  flex-wrap: wrap;
  gap: 7px 12px;
  margin-top: auto;
  padding-top: 11px;
  border-top: 1px solid #f0ecfa;
  color: #716b88;
  font-size: 11px;
}

.feed-card-schedule {
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

.request-mode {
  text-transform: capitalize;
}

.schedule-item {
  display: flex;
  min-height: 20px;
  align-items: center;
  gap: 5px;
  overflow-wrap: anywhere;
}

.feed-card-actions {
  justify-content: flex-end;
}
</style>
