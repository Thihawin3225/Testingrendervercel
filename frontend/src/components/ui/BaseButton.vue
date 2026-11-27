<script setup lang="ts">
type ButtonVariant = "primary" | "outline" | "ghost" | "danger" | "green";
type ButtonSize = "default" | "sm";
type ButtonType = "button" | "submit" | "reset";

withDefaults(
  defineProps<{
    variant?: ButtonVariant;
    size?: ButtonSize;
    type?: ButtonType;
    disabled?: boolean;
  }>(),
  {
    variant: "primary",
    size: "default",
    type: "button",
    disabled: false,
  },
);
</script>

<template>
  <button
    class="base-button"
    :class="[`base-button--${variant}`, `base-button--${size}`]"
    :type="type"
    :disabled="disabled"
    v-bind="$attrs"
  >
    <slot />
  </button>
</template>

<style scoped>
.base-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 15px;
  border: 0;
  border-radius: 10px;
  background: linear-gradient(115deg, #8233f4, #5727ce);
  color: #fff;
  font: inherit;
  font-weight: 750;
  text-decoration: none;
  white-space: nowrap;
  cursor: pointer;
}

.base-button:hover:not(:disabled) {
  filter: brightness(1.07);
}

.base-button:focus-visible {
  outline: 2px solid #6c2dea;
  outline-offset: 2px;
}

.base-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.base-button--outline {
  border: 1px solid #a68af9;
  background: #fff;
  color: #6427d4;
}

.base-button--ghost {
  background: #f2eeff;
  color: #652ece;
}

.base-button--danger {
  border: 1px solid #ffc0c9;
  background: #fff6f6;
  color: #c92f49;
}

.base-button--green {
  background: #15966e;
  color: #fff;
}

.base-button--sm {
  padding: 7px 11px;
  font-size: 12px;
}
</style>
