<script setup lang="ts">
import { X } from '@lucide/vue'

withDefaults(
  defineProps<{
    modelValue: boolean
    title: string
    subtitle?: string
  }>(),
  {
    subtitle: '',
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  close: []
}>()

function closeModal() {
  emit('update:modelValue', false)
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        class="modal-overlay"
        @click.self="closeModal"
      >
        <section
          class="modal-container"
          role="dialog"
          aria-modal="true"
          aria-labelledby="base-modal-title"
        >
          <header class="modal-header">
            <div class="modal-heading">
              <h2 id="base-modal-title">
                {{ title }}
              </h2>

              <p v-if="subtitle" class="modal-subtitle">
                {{ subtitle }}
              </p>
            </div>

            <button
              type="button"
              class="close-button"
              aria-label="Close modal"
              @click="closeModal"
            >
              <X :size="20" />
            </button>
          </header>

          <div class="modal-body">
            <slot />
          </div>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;

  display: flex;
  align-items: center;
  justify-content: center;

  overflow-y: auto;
  padding: 20px;

  background: rgb(30 20 50 / 50%);
  backdrop-filter: blur(3px);
  -webkit-backdrop-filter: blur(3px);

  scrollbar-width: none;
  -ms-overflow-style: none;
}

.modal-overlay::-webkit-scrollbar {
  display: none;
}

.modal-container {
  width: 100%;
  max-width: 620px;
  max-height: 90vh;
  overflow-y: auto;

  border: 1px solid #e6e4f5;
  border-radius: 16px;

  background: #ffffff;
  box-shadow: 0 20px 60px rgb(30 20 50 / 20%);

  scrollbar-width: none;
  -ms-overflow-style: none;
}

.modal-container::-webkit-scrollbar {
  display: none;
}

.modal-header {
  position: sticky;
  top: 0;
  z-index: 2;

  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;

  padding: 18px 22px;

  border-bottom: 1px solid #eae6f2;
  background: #ffffff;
}

.modal-heading h2 {
  margin: 0;
  color: #302744;
  font-size: 18px;
  font-weight: 750;
}

.modal-subtitle {
  margin: 5px 0 0;
  color: #8a849b;
  font-size: 13px;
}

.close-button {
  display: grid;
  width: 35px;
  height: 35px;
  flex-shrink: 0;
  place-items: center;

  border: 0;
  border-radius: 9px;

  background: #f2eeff;
  color: #652ece;

  cursor: pointer;
  transition: background 0.2s ease;
}

.close-button:hover {
  background: #e9e0ff;
}

.modal-body {
  padding: 22px;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-active .modal-container,
.modal-leave-active .modal-container {
  transition: transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: translateY(12px);
}

@media (max-width: 540px) {
  .modal-overlay {
    padding: 12px;
  }

  .modal-header {
    padding: 16px;
  }

  .modal-body {
    padding: 16px;
  }
}
</style>
