<script setup lang="ts">
import { CheckCircle, CircleAlert } from '@lucide/vue'
import { usePageMessage } from '@/composables/usePageMessage'

const { successMessage, pageError } = usePageMessage()
</script>

<template>
  <div class="page-message-wrapper">
    <!-- SUCCESS MESSAGE -->
    <Transition name="message">
      <div
        v-if="successMessage"
        class="message success-message"
        role="status"
      >
        <CheckCircle :size="18" />
        <span>{{ successMessage }}</span>
      </div>
    </Transition>

    <!-- ERROR MESSAGE -->
    <Transition name="message">
      <div
        v-if="pageError"
        class="message error-message"
        role="alert"
      >
        <CircleAlert :size="18" />
        <span>{{ pageError }}</span>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.page-message-wrapper {
  width: 100%;
}

.message {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 12px 15px;
  border: 1px solid;
  border-radius: 11px;
  font-size: 13px;
  font-weight: 500;
}

.success-message {
  border-color: #bcebd3;
  background: #effbf4;
  color: #16845b;
}

.error-message {
  border-color: #ffc8ce;
  background: #fff4f4;
  color: #c92f49;
}

/* MESSAGE TRANSITION */
.message-enter-active,
.message-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.message-enter-from,
.message-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}
</style>
