
<script setup lang="ts">
import { computed } from 'vue'
import type { Component } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

import {
  Bell,
  BookOpen,
  Bookmark,
  Building2,
  Calendar,
  FileText,
  Flag,
  GraduationCap,
  Handshake,
  Heart,
  House,
  Inbox,
  LayoutDashboard,
  LogOut,
  MapPin,
  Package,
  Search,
  ShieldAlert,
  ShoppingCart,
  Sparkles,
  Upload,
  UserRound,
  Users,
  UserRoundPlus,
  Store,
} from '@lucide/vue'

import { useLogout } from '@/modules/auth/composables/useLogout'

// ==========================================
// PROPS
// ==========================================

const props = withDefaults(
  defineProps<{
    open?: boolean
    variant?: 'student' | 'admin'
  }>(),
  {
    open: false,
    variant: 'student',
  },
)

// ==========================================
// EMITS
// ==========================================

const emit = defineEmits<{
  navigate: []
}>()

// ==========================================
// ROUTER
// ==========================================

const route = useRoute()

// ==========================================
// AUTHENTICATION — LOGOUT
// ==========================================

const {
  mutate: logout,
  isPending: isLoggingOut,
  isError: logoutFailed,
} = useLogout()

function handleLogout() {
  if (isLoggingOut.value) {
    return
  }

  logout()
}

function handleNavigation() {
  emit('navigate')
}

// ==========================================
// NAVIGATION TYPES
// ==========================================

type NavigationItem = {
  icon: Component
  label: string
  routeName?: string
  badge?: string
}

type NavigationGroup = {
  label: string
  items: NavigationItem[]
}

// ==========================================
// STUDENT NAVIGATION
// ==========================================

const studentGroups: NavigationGroup[] = [
  {
    label: 'OVERVIEW',
    items: [
      {
        icon: House,
        label: 'Dashboard',
        routeName: 'home',
      },
      {
        icon: Bell,
        label: 'Notifications',
        badge: '3',
      },
    ],
  },

  {
    label: 'MARKETPLACE',
    items: [
      {
        icon: ShoppingCart,
        label: 'Browse Marketplace',
        routeName: 'marketplace',
      },
      {
        icon: Package,
        label: 'My Listings',
        routeName: 'seller-requests',
      },
      {
        icon: Inbox,
        label: 'Item Requests',
        routeName: 'my-requests',
      },
      {
        icon: Heart,
        label: 'Wishlist (Demo)',
        routeName: 'saved-listings',
      },
    ],
  },

  {
    label: 'STUDY MATERIALS',
    items: [
      {
        icon: BookOpen,
        label: 'Browse Materials',
        routeName: 'study-materials',
      },
      {
        icon: Upload,
        label: 'My Uploads',
        routeName: 'my-uploads',
      },
      {
        icon: Bookmark,
        label: 'Bookmarked Materials',
        routeName: 'bookmarked-materials',
      },
    ],
  },

  {
    label: 'PEER LEARNING',
    items: [
      {
        icon: Handshake,
        label: 'Find Mentors',
      },
      {
        icon: FileText,
        label: 'My Learning Requests',
        routeName: 'peer-learning-requests',
      },
      {
        icon: Search,
        label: 'Learning Needs Feed',
        routeName: 'learning-needs-feed',
      },
      {
        icon: Sparkles,
        label: 'Matched Mentors',
      },
      {
        icon: GraduationCap,
        label: 'Become / Manage Mentor',
      },
      {
        icon: Calendar,
        label: 'My Bookings',
      },
    ],
  },

  {
    label: 'MY ACCOUNT',
    items: [
      {
        icon: Flag,
        label: 'My Reports',
      },
      {
        icon: UserRound,

        label: "My Profile",
        routeName: "profile",

      },
    ],
  },
]

// ==========================================
// ADMIN NAVIGATION
// ==========================================

const adminGroup: NavigationGroup = {
  label: 'ADMINISTRATION',
  items: [
    {
      icon: LayoutDashboard,
      label: 'Admin Dashboard',
      routeName: 'admin-dashboard',
    },
    {
      icon: Users,
      label: 'Student Management',
      routeName: 'admin-students',
    },
    {
      icon: UserRoundPlus,
      label: 'Student Enrollment',
      routeName: 'admin-student-enrollments',
    },
    {
      icon: Store,
      label: 'Marketplace Categories',
      routeName: 'admin-marketplace-categories',
    },
    {
      icon: Building2,
      label: 'Academic Management',
      routeName: 'admin-academics',
    },
    {
      icon: MapPin,
      label: 'Approved Locations',
      routeName: 'admin-campus-locations',
    },
    {
      icon: ShieldAlert,
      label: 'Reports & Warnings',
    },
  ],
}

// ==========================================
// COMPUTED NAVIGATION
// ==========================================

const navigationGroups = computed(() =>
  props.variant === 'admin' ? [adminGroup] : studentGroups,
)
</script>

<template>
  <aside
    id="sidebar"
    class="sidebar"
    :class="{ open: props.open }"
    aria-label="Main navigation"
  >
    <!-- BRAND -->
    <div class="brand">
      <span class="brand-icon" aria-hidden="true">◆</span>

      CampusBridge
    </div>

    <!-- NAVIGATION -->
    <nav class="sidebar-nav">

      <!-- SCROLLABLE NAVIGATION CONTENT -->
      <div class="navigation-content">
        <section
          v-for="group in navigationGroups"
          :key="group.label"
        >
          <div class="side-label">
            {{ group.label }}
          </div>

          <template
            v-for="item in group.items"
            :key="item.label"
          >
            <!-- ACTIVE NAVIGATION LINK -->
            <RouterLink
              v-if="item.routeName"
              class="side-nav"
              :class="{
                active: route.name === item.routeName,
              }"
              :to="{ name: item.routeName }"
              :aria-current="
                route.name === item.routeName
                  ? 'page'
                  : undefined
              "
              @click="handleNavigation"
            >
              <span class="icon">
                <component
                  :is="item.icon"
                  :size="18"
                  aria-hidden="true"
                />
              </span>

              {{ item.label }}

              <span
                v-if="item.badge"
                class="sidenum"
              >
                {{ item.badge }}
              </span>
            </RouterLink>

            <!-- STATIC NAVIGATION ITEM -->
            <span
              v-else
              class="side-nav side-nav--static"
              aria-disabled="true"
            >
              <span class="icon">
                <component
                  :is="item.icon"
                  :size="18"
                  aria-hidden="true"
                />
              </span>

              {{ item.label }}

              <span
                v-if="item.badge"
                class="sidenum"
              >
                {{ item.badge }}
              </span>
            </span>
          </template>
        </section>
      </div>

      <!-- LOGOUT SECTION -->
      <div class="sidebar-footer">

        <button
          type="button"
          class="side-nav logout-button"
          :disabled="isLoggingOut"
          :aria-busy="isLoggingOut"
          @click="handleLogout"
        >
          <span class="icon">
            <LogOut
              :size="18"
              aria-hidden="true"
            />
          </span>

          {{ isLoggingOut ? 'Signing out...' : 'Sign out' }}
        </button>

        <!-- LOGOUT ERROR -->
        <p
          v-if="logoutFailed"
          class="logout-error"
          role="alert"
        >
          Could not sign out. Please try again.
        </p>

      </div>
    </nav>
  </aside>
</template>

<style scoped>
/* ==========================================
   SIDEBAR
========================================== */

.sidebar {
  position: fixed;
  z-index: 20;
  top: 0;
  bottom: 0;
  left: 0;

  display: flex;
  flex-direction: column;

  width: var(--sidebar-width);
  overflow: hidden;

  padding: var(--sidebar-padding);

  background: var(--sidebar-gradient);
  color: #fff;
}

/* ==========================================
   BRAND
========================================== */

.brand {
  display: flex;
  align-items: center;
  gap: var(--brand-gap);

  flex-shrink: 0;

  padding: var(--brand-padding);

  font-size: 22px;
  font-weight: 850;
  letter-spacing: -0.7px;
}

.brand-icon {
  color: #a96cff;
  font-size: 29px;
}

/* ==========================================
   NAVIGATION CONTAINER
========================================== */

.sidebar-nav {
  display: flex;
  flex: 1;
  flex-direction: column;

  min-height: 0;
  overflow: hidden;
}

/* ==========================================
   SCROLLABLE NAVIGATION CONTENT
========================================== */

.navigation-content {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  overflow-x: hidden;
}

/* ==========================================
   NAVIGATION LABELS
========================================== */

.side-label {
  padding: var(--sidebar-label-padding);

  color: #a99ccc;

  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.14em;
}

/* ==========================================
   NAVIGATION ITEMS
========================================== */

.side-nav {
  display: flex;
  align-items: center;
  gap: var(--sidebar-nav-gap);

  width: 100%;
  margin: 2px 0;

  padding: var(--sidebar-nav-padding);

  border: 0;
  border-radius: var(--sidebar-nav-radius);

  background: transparent;
  color: #e3dff2;

  text-align: left;
  text-decoration: none;

  transition: 0.15s;
}

.side-nav span.icon {
  display: flex;
  flex: 0 0 24px;
  align-items: center;
  justify-content: center;

  width: 24px;
}

.side-nav.active,
.side-nav:not(.side-nav--static):hover {
  background: var(--sidebar-active-gradient);
  color: #fff;
}

/* ==========================================
   BADGE
========================================== */

.sidenum {
  margin-left: auto;
  padding: 1px 8px;

  border-radius: 40px;
  background: #a85aff;

  font-size: 11px;
}

/* ==========================================
   SIDEBAR FOOTER
========================================== */

.sidebar-footer {
  flex-shrink: 0;

  margin-top: auto;
  padding-top: 12px;

  border-top: 1px solid rgb(255 255 255 / 12%);
}

/* ==========================================
   LOGOUT BUTTON
========================================== */

.logout-button {
  font: inherit;
  cursor: pointer;
}

.logout-button:hover {
  background: rgb(255 255 255 / 12%);
  color: #fff;
}

.logout-button:disabled {
  opacity: 0.6;
  cursor: wait;
}

/* ==========================================
   LOGOUT ERROR
========================================== */

.logout-error {
  margin: 8px 12px;

  color: #ffd8de;
  font-size: 12px;
}

/* ==========================================
   SCROLLBAR
========================================== */

.navigation-content::-webkit-scrollbar {
  width: 4px;
}

.navigation-content::-webkit-scrollbar-track {
  background: transparent;
}

.navigation-content::-webkit-scrollbar-thumb {
  border-radius: 10px;
  background: rgb(255 255 255 / 25%);
}

/* ==========================================
   RESPONSIVE SIDEBAR
========================================== */

@media (max-width: 760px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.2s;
  }

  .sidebar.open {
    transform: translateX(0);
  }
}
</style>
