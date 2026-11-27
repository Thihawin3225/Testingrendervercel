<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import AppNavbar from '../components/layout/AppNavbar.vue'
import AppSidebar from '../components/layout/AppSidebar.vue'

const route = useRoute()
const mobileBreakpoint = '(max-width: 760px)'
const breadcrumbTitle = computed(() =>
  typeof route.meta.title === 'string' ? route.meta.title : '',
)

withDefaults(
  defineProps<{
    variant?: 'student' | 'admin'
  }>(),
  {
    variant: 'student',
  },
)

const sidebarOpen = ref(false)
const isMobile = ref(false)
let mobileMediaQuery: MediaQueryList | undefined

const sidebarInert = computed(() => isMobile.value && !sidebarOpen.value)

function updateMobileState(event?: MediaQueryListEvent) {
  isMobile.value = event?.matches ?? mobileMediaQuery?.matches ?? false
  if (!isMobile.value) sidebarOpen.value = false
}

onMounted(() => {
  mobileMediaQuery = window.matchMedia(mobileBreakpoint)
  updateMobileState()
  mobileMediaQuery.addEventListener('change', updateMobileState)
})

onBeforeUnmount(() => {
  mobileMediaQuery?.removeEventListener('change', updateMobileState)
})
</script>

<template>
  <AppSidebar
    :variant="variant"
    :open="sidebarOpen"
    :inert="sidebarInert"
    :aria-hidden="sidebarInert"
    @navigate="sidebarOpen = false"
  />
  <div class="shell">
    <AppNavbar
      :sidebar-open="sidebarOpen"
      @toggle-sidebar="sidebarOpen = !sidebarOpen"
    />
    <main class="content">
      <div class="breadcrumb">CampusBridge / <span>{{ breadcrumbTitle }}</span></div>
      <RouterView />
    </main>
  </div>
</template>
