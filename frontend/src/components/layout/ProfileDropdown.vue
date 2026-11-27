<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { ChevronDown, CircleUserRound, LogOut, UserRound } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import { useLogout } from '@/modules/auth/composables/useLogout'

withDefaults(
  defineProps<{
    displayName?: string
    avatarUrl?: string
  }>(),
  {
    displayName: '',
    avatarUrl: '',
  },
)

const root = ref<HTMLElement | null>(null)
const trigger = ref<HTMLButtonElement | null>(null)
const menu = ref<HTMLElement | null>(null)
const isOpen = ref(false)
const {
  mutate: logout,
  isPending: isLoggingOut,
  isError: logoutFailed,
} = useLogout()

function getMenuItems() {
  return menu.value?.querySelectorAll<HTMLElement>('[role="menuitem"]:not([aria-disabled="true"])') ?? []
}

async function setOpen(open: boolean, focusFirstItem = false) {
  isOpen.value = open

  if (open && focusFirstItem) {
    await nextTick()
    getMenuItems()[0]?.focus()
  }
}

function toggleMenu() {
  void setOpen(!isOpen.value, !isOpen.value)
}

function handleSignOut(): void {
  if (!isLoggingOut.value) {
    logout()
  }
}

function focusMenuItem(index: number) {
  const items = getMenuItems()
  if (items.length === 0) return

  const wrappedIndex = (index + items.length) % items.length
  items[wrappedIndex]?.focus()
}

function onMenuKeydown(event: KeyboardEvent) {
  const items = Array.from(getMenuItems())
  const currentIndex = items.indexOf(document.activeElement as HTMLElement)

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      focusMenuItem(currentIndex + 1)
      break
    case 'ArrowUp':
      event.preventDefault()
      focusMenuItem(currentIndex <= 0 ? items.length - 1 : currentIndex - 1)
      break
    case 'Home':
      event.preventDefault()
      focusMenuItem(0)
      break
    case 'End':
      event.preventDefault()
      focusMenuItem(items.length - 1)
      break
    case 'Escape':
      event.preventDefault()
      void setOpen(false)
      trigger.value?.focus()
      break
    case 'Tab':
      void setOpen(false)
      break
  }
}

function onDocumentPointerDown(event: PointerEvent) {
  if (event.target instanceof Node && !root.value?.contains(event.target)) {
    void setOpen(false)
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', onDocumentPointerDown)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onDocumentPointerDown)
})
</script>

<template>
  <div ref="root" class="profile-dropdown">
    <button
      ref="trigger"
      class="profile-trigger"
      type="button"
      aria-label="Account menu"
      aria-haspopup="menu"
      :aria-expanded="isOpen"
      aria-controls="profile-menu"
      @click="toggleMenu"
      @keydown.down.prevent="setOpen(true, true)"
    >
      <img v-if="avatarUrl" class="avatar" :src="avatarUrl" alt="" />
      <span v-else class="avatar avatar-fallback" aria-hidden="true">
        <CircleUserRound :size="22" />
      </span>
      <span class="profile-name">{{ displayName || 'Account' }}</span>
      <ChevronDown class="chevron" :class="{ 'chevron-open': isOpen }" :size="16" aria-hidden="true" />
    </button>

    <div
      v-if="isOpen"
      id="profile-menu"
      ref="menu"
      class="profile-menu"
      role="menu"
      aria-label="Account options"
      @keydown="onMenuKeydown"
    >
      <RouterLink
        class="menu-item"
        role="menuitem"
        :to="{ name: 'profile' }"
        @click="setOpen(false)"
      >
        <UserRound :size="16" aria-hidden="true" />
        <span>My Profile</span>
      </RouterLink>
      <button
        class="menu-item"
        :class="{ 'menu-item--disabled': isLoggingOut }"
        type="button"
        role="menuitem"
        :aria-disabled="isLoggingOut ? 'true' : undefined"
        :disabled="isLoggingOut"
        @click="handleSignOut"
      >
        <LogOut :size="16" aria-hidden="true" />
        <span>{{ isLoggingOut ? 'Signing out…' : 'Sign Out' }}</span>
      </button>
      <p v-if="logoutFailed" class="menu-error" role="alert">
        Could not sign out. Please try again.
      </p>
    </div>
  </div>
</template>

<style scoped>
.profile-dropdown {
  position: relative;
  flex: none;
}

.profile-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  max-width: min(240px, calc(100vw - 26px));
  padding: 4px 8px 4px 4px;
  border: 1px solid transparent;
  border-radius: 10px;
  background: transparent;
  color: var(--text);
  white-space: nowrap;
}

.profile-trigger:hover,
.profile-trigger[aria-expanded='true'] {
  border-color: #e5e0f5;
  background: #fbfaff;
}

.profile-trigger:focus-visible,
.menu-item:focus-visible {
  outline: 2px solid var(--purple);
  outline-offset: 2px;
}

.avatar {
  display: grid;
  flex: none;
  place-items: center;
  width: var(--avatar-size);
  height: var(--avatar-size);
  overflow: hidden;
  border-radius: var(--avatar-radius);
  background: linear-gradient(140deg, #d6ccff, #ede7ff);
  color: #5f35bc;
  object-fit: cover;
}

.avatar-fallback {
  font-weight: 850;
}

.profile-name {
  overflow: hidden;
  max-width: 150px;
  text-overflow: ellipsis;
}

.chevron {
  flex: none;
  color: var(--muted);
  transition: transform 0.15s;
}

.chevron-open {
  transform: rotate(180deg);
}

.profile-menu {
  position: absolute;
  z-index: 30;
  top: calc(100% + 8px);
  right: 0;
  width: 190px;
  max-width: calc(100vw - 26px);
  padding: 6px;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: #fff;
  box-shadow: var(--shadow);
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 10px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: var(--text);
  text-align: left;
  text-decoration: none;
  cursor: pointer;
}

.menu-item:hover,
.menu-item:focus-visible {
  background: #f5f1ff;
}

.menu-item--disabled {
  color: var(--muted);
  cursor: wait;
  opacity: 0.55;
}

.menu-error {
  margin: 5px 9px 7px;
  color: #ae2b40;
  font-size: 11px;
  line-height: 1.45;
}

.menu-item svg {
  flex: none;
}
</style>
