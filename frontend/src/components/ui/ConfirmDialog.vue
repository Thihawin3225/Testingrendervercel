<script setup lang="ts">
import BaseButton from "@/components/ui/BaseButton.vue";

const props = withDefaults(
  defineProps<{
    open: boolean;
    title: string;
    message: string;
    confirmLabel?: string;
    cancelLabel?: string;
    confirmVariant?: "primary" | "outline" | "danger" | "ghost";
    busy?: boolean;
  }>(),
  {
    confirmLabel: "Confirm",
    cancelLabel: "Cancel",
    confirmVariant: "danger",
    busy: false,
  },
);

const emit = defineEmits<{
  confirm: [];
  cancel: [];
}>();

function handleDialogClick(event: MouseEvent): void {
  if (event.target === event.currentTarget) {
    emit("cancel");
  }
}
</script>

<template>
  <dialog
    v-if="props.open"
    class="confirm-dialog"
    open
    aria-labelledby="confirm-dialog-title"
    @click="handleDialogClick"
  >
    <div class="confirm-dialog__card" role="alertdialog" aria-live="assertive">
      <h2 id="confirm-dialog-title">{{ props.title }}</h2>
      <p>{{ props.message }}</p>

      <div class="confirm-dialog__actions">
        <BaseButton
          type="button"
          variant="ghost"
          :disabled="props.busy"
          @click="emit('cancel')"
        >
          {{ props.cancelLabel }}
        </BaseButton>
        <BaseButton
          type="button"
          :variant="props.confirmVariant"
          :disabled="props.busy"
          @click="emit('confirm')"
        >
          {{ props.confirmLabel }}
        </BaseButton>
      </div>
    </div>
  </dialog>
</template>

<style scoped>
.confirm-dialog {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  margin: 0;
  padding: 16px;
  border: 0;
  background: rgba(31, 20, 52, 0.48);
  overflow: auto;
}

.confirm-dialog__card {
  display: grid;
  gap: 12px;
  width: min(420px, 100%);
  padding: 22px;
  border: 1px solid #e7ddff;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 24px 50px rgba(54, 32, 96, 0.2);
}

.confirm-dialog__card h2 {
  margin: 0;
  color: var(--text);
  font-size: 17px;
}

.confirm-dialog__card p {
  margin: 0;
  color: var(--muted);
  font-size: 14px;
  line-height: 1.55;
}

.confirm-dialog__actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 6px;
}
</style>
