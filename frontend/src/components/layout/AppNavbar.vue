<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Bell, Menu, Search } from '@lucide/vue'
import BaseButton from '../ui/BaseButton.vue'
import ProfileDropdown from './ProfileDropdown.vue'
import { authApi, type AuthUser } from '@/modules/auth/api/authApi'

withDefaults(
  defineProps<{
    sidebarOpen?: boolean
  }>(),
  {
    sidebarOpen: false,
  },
)

defineEmits<{
  'toggle-sidebar': []
}>()

const currentUser = ref<AuthUser | null>(null)

onMounted(async () => {
  try {
    currentUser.value = await authApi.me()
  } catch {
    currentUser.value = null
  }
})
</script>

<template>
  <header class="topbar">
    <BaseButton
      class="menu-button"
      variant="ghost"
      size="sm"
      type="button"
      aria-label="Toggle navigation"
      aria-controls="sidebar"
      :aria-expanded="sidebarOpen"
      @click="$emit('toggle-sidebar')"
    >
      <Menu :size="18" aria-hidden="true" />
    </BaseButton>

    <label class="globalsearch">
      <Search :size="16" aria-hidden="true" />
      <input
        type="search"
        placeholder="Search marketplace, materials, topics or mentors…"
        autocomplete="off"
        aria-label="Search marketplace, materials, topics or mentors"
      />
    </label>

    <div class="userbar">
      <button class="bell" type="button" title="Open notifications" aria-label="Open notifications">
        <Bell :size="21" aria-hidden="true" />
        <span>3</span>
      </button>
      <ProfileDropdown
        :display-name="currentUser?.name"
        :avatar-url="currentUser?.avatar_url ?? ''"
      />
    </div>
  </header>
</template>

<style scoped>
.topbar {
  position: sticky;
  z-index: 9;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  height: var(--navbar-height);
  padding: var(--navbar-padding);
  border-bottom: 1px solid var(--line);
  background: #fff;
}

.topbar .menu-button {
  display: none;
}

.globalsearch {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  max-width: 560px;
  padding: var(--navbar-search-padding);
  border: 1px solid #e0dbf3;
  border-radius: var(--navbar-search-radius);
  background: #fbfaff;
  color: #74718d;
}

.globalsearch input {
  flex: 1;
  min-width: 50px;
  border: 0;
  outline: 0;
  background: transparent;
}

.userbar {
  display: flex;
  align-items: center;
  gap: 13px;
  white-space: nowrap;
}

.bell {
  position: relative;
  display: grid;
  flex: none;
  place-items: center;
  width: 40px;
  height: 40px;
  padding: 7px 10px;
  border: 0;
  border-radius: var(--notification-radius);
  background: #f3efff;
  font-size: 21px;
}

.bell span {
  position: absolute;
  top: -5px;
  right: -4px;
  padding: 0 6px;
  border-radius: 10px;
  background: #ff405c;
  color: #fff;
  font-size: 10px;
}

@media (max-width: 760px) {
  .topbar {
    height: auto;
    min-height: 62px;
    padding: 0 13px;
  }

  .topbar .menu-button {
    display: inline-flex;
  }

  .globalsearch {
    display: none;
  }

  .userbar {
    gap: 8px;
  }
}
</style>
