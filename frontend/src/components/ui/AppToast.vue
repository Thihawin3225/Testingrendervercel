<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { X } from '@lucide/vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()
const teleportTarget = ref<Element | string>('body')
let modalObserver: MutationObserver | undefined

function updateTeleportTarget() {
  const activeModal = document.querySelector('dialog:modal')
  teleportTarget.value = activeModal ?? 'body'
}

watch(toast.toasts, updateTeleportTarget, { flush: 'post' })

onMounted(() => {
  modalObserver = new MutationObserver(updateTeleportTarget)
  modalObserver.observe(document.body, {
    attributes: true,
    attributeFilter: ['open'],
    subtree: true,
  })
  updateTeleportTarget()
})

onUnmounted(() => {
  modalObserver?.disconnect()
  toast.clearAll()
})
</script>

<template>
  <Teleport :to="teleportTarget">
    <div v-if="toast.toasts.value.length" class="toast-layer" role="region" aria-label="Notifications">
      <div class="toast-stack" aria-live="polite" aria-relevant="additions">
        <div
          v-for="notification in toast.toasts.value"
          :key="notification.id"
          :class="['toast', `toast--${notification.type}`]"
          :role="notification.type === 'error' ? 'alert' : 'status'"
          :aria-live="notification.type === 'error' ? 'assertive' : 'polite'"
          aria-atomic="true"
        >
          <div class="toast__message">
            <span class="toast__icon" aria-hidden="true">
              {{ notification.type === 'success' ? '✓' : notification.type === 'warning' ? '!' : notification.type === 'info' ? 'i' : '×' }}
            </span>
            <p>{{ notification.message }}</p>
          </div>

          <button
            type="button"
            class="toast__close"
            aria-label="Dismiss notification"
            @click="toast.dismiss(notification.id)"
          >
            <X :size="16" aria-hidden="true" />
          </button>

          <div
            class="toast__progress"
            :style="{ animationDuration: `${notification.duration}ms` }"
            aria-hidden="true"
          />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-layer {
  position: fixed;
  z-index: 10000;
  inset: 0;
  pointer-events: none;
}

.toast-stack {
  position: absolute;
  z-index: 3000;
  top: calc(var(--toast-header-height, var(--navbar-height, 70px)) + max(12px, env(safe-area-inset-top)));
  right: max(16px, env(safe-area-inset-right));
  display: grid;
  gap: 12px;
  width: min(380px, calc(100% - 32px));
  pointer-events: none;
}

.toast {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 42px 16px 14px;
  overflow: hidden;
  border: 1px solid #e6def6;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 12px 30px rgba(44, 31, 75, 0.16);
  color: var(--text, #29243a);
  pointer-events: auto;
}

.toast--success {
  border-color: #b7e7cb;
  --toast-accent: #198754;
}

.toast--error {
  border-color: #f0c2c2;
  --toast-accent: #c62828;
}

.toast--warning {
  border-color: #f1d49a;
  --toast-accent: #a96800;
}

.toast--info {
  border-color: #cfc5ed;
  --toast-accent: #6747ad;
}

.toast__message {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  min-width: 0;
}

.toast__icon {
  display: grid;
  flex: 0 0 23px;
  width: 23px;
  height: 23px;
  place-items: center;
  border-radius: 50%;
  background: color-mix(in srgb, var(--toast-accent) 13%, white);
  color: var(--toast-accent);
  font-size: 13px;
  font-weight: 800;
}

.toast p {
  margin: 2px 0 0;
  overflow-wrap: anywhere;
  font-size: 13px;
  line-height: 1.5;
  font-weight: 600;
}

.toast__close {
  position: absolute;
  top: 10px;
  right: 10px;
  display: grid;
  width: 25px;
  height: 25px;
  padding: 0;
  place-items: center;
  border: 0;
  border-radius: 50%;
  background: transparent;
  color: #777184;
  cursor: pointer;
}

.toast__close:hover,
.toast__close:focus-visible {
  background: #f1eef7;
  outline: none;
}

.toast__progress {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  height: 3px;
  background: var(--toast-accent);
  transform-origin: left center;
  animation: toast-progress linear forwards;
}

@keyframes toast-progress {
  from {
    transform: scaleX(1);
  }

  to {
    transform: scaleX(0);
  }
}

@media (max-width: 480px) {
  .toast-stack {
    right: max(10px, env(safe-area-inset-right));
    width: calc(100% - 20px);
  }
}

@media (max-width: 760px) {
  .toast-layer {
    --toast-header-height: 62px;
  }
}

</style>
