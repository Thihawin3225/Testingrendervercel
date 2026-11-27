<script setup lang="ts">
import type { Component } from "vue";
import {
  Bell,
  BookOpen,
  Building2,

  GraduationCap,  
  Handshake,

  Inbox,
  MapPin,
  PackagePlus,
  School,
  Sparkles,
  Upload,
} from "@lucide/vue";
import BaseButton from "../../components/ui/BaseButton.vue";
import BaseCard from "../../components/ui/BaseCard.vue";
import BaseEmptyState from "../../components/ui/BaseEmptyState.vue";
import PageHero from "../../components/layout/PageHero.vue";

const statistics: { icon: Component; label: string }[] = [
  { icon: PackagePlus, label: "My marketplace listings" },
  { icon: BookOpen, label: "My study uploads" },
  { icon: Handshake, label: "My learning requests" },
  { icon: Sparkles, label: "Mentor matches" },
  { icon: Bell, label: "Unread alerts" },
];

const quickActions: {
  icon: Component;
  title: string;
  description: string;
}[] = [
  {
    icon: PackagePlus,
    title: "Create Listing",
    description: "Sell, donate or post a wanted item",
  },
  {
    icon: Upload,
    title: "Upload Material",
    description: "Share class notes and lecture files",
  },
  {
    icon: Handshake,
    title: "Request Learning Help",
    description: "Choose a topic; time is optional",
  },
  {
    icon: GraduationCap,
    title: "Become a Mentor",
    description: "Create one profile with several skills",
  },
];
</script>

<template>
  <section class="dashboard" aria-labelledby="dashboard-title">
    <PageHero
      class="dashboard-hero"
      eyebrow="UCS PYAY · CAMPUSBRIDGE"
      title="Welcome to CampusBridge!"
      title-id="dashboard-title"
      description="Exchange resources, learn from peers, and collaborate safely at UCS Pyay."
      :icon="School"
    />

    <section class="grid4" aria-label="Dashboard statistics">
      <BaseCard
        v-for="stat in statistics"
        :key="stat.label"
        as="article"
        class="stat"
      >
        <span class="stat-icon">
          <component :is="stat.icon" :size="26" aria-hidden="true" />
        </span>
        <div>
          <strong aria-hidden="true">—</strong>
          <span class="sr-only">Not available yet</span>
          <div class="small">{{ stat.label }}</div>
        </div>
      </BaseCard>
    </section>

    <div class="bar">
      <h2>Quick actions</h2>
      <span class="fine" role="status">
        Everything you need, one dashboard · Live data unavailable
      </span>
    </div>

    <section class="grid4" aria-label="Quick actions">
      <button
        v-for="action in quickActions"
        :key="action.title"
        class="quick"
        type="button"
        disabled
        :aria-label="`${action.title}. Unavailable until this feature is implemented.`"
      >
        <component
          :is="action.icon"
          class="quick-icon"
          :size="23"
          aria-hidden="true"
        />
        <strong>{{ action.title }}</strong>
        <small>{{ action.description }}</small>
      </button>
    </section>

    <div class="layout2">
      <div class="card-column">
        <BaseCard as="section" padded>
          <div class="card-head">
            <h2><Sparkles :size="18" aria-hidden="true" /> Matched mentors</h2>
            <BaseButton variant="ghost" size="sm" disabled>
              All learning requests →
            </BaseButton>
          </div>
          <BaseEmptyState
            title="No live data available"
            description="Mentor suggestions will appear when dashboard data is connected."
          >
            <template #icon><Inbox :size="42" /></template>
          </BaseEmptyState>
        </BaseCard>

        <BaseCard as="section" padded>
          <div class="card-head">
            <h2>
              <BookOpen :size="18" aria-hidden="true" /> Recently shared study
              materials
            </h2>
          </div>
          <BaseEmptyState
            title="No live data available"
            description="Recently shared study resources will appear here when data is connected."
          >
            <template #icon><Inbox :size="42" /></template>
          </BaseEmptyState>
        </BaseCard>
      </div>

      <div class="card-column">
        <BaseCard as="section" padded>
          <div class="card-head">
            <h2><Bell :size="18" aria-hidden="true" /> Recent notifications</h2>
          </div>
          <BaseEmptyState
            title="No live data available"
            description="Recent notifications will appear here when data is connected."
          >
            <template #icon><Inbox :size="42" /></template>
          </BaseEmptyState>
        </BaseCard>

        <BaseCard as="section" padded>
          <div class="card-head">
            <h2>
              <Building2 :size="18" aria-hidden="true" /> Approved campus
              exchange locations
            </h2>
          </div>
          <p class="muted">
            Marketplace pickups and in-person mentoring use approved locations
            only.
          </p>
          <div class="location-empty">
            <MapPin :size="18" aria-hidden="true" />
            <span
              >Approved locations will appear here when live data is
              available.</span
            >
          </div>
        </BaseCard>
      </div>
    </div>
  </section>
</template>

<style scoped>
.dashboard {
  color: var(--text);
}

.dashboard-hero {
  margin-bottom: 22px;
}

.grid4 {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 15px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 19px;
}

.stat-icon {
  display: grid;
  flex: none;
  place-items: center;
  padding: 12px;
  border-radius: 14px;
  background: #f3edff;
  color: var(--purple);
}

.stat strong {
  font-size: 27px;
  line-height: 1.1;
}

.small {
  color: var(--muted);
  font-size: 12px;
}

.bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 13px;
  margin: 15px 0;
  flex-wrap: wrap;
}

.bar h2 {
  font-size: 20px;
}

.fine {
  color: var(--muted);
  font-size: 12px;
}

.quick {
  width: 100%;
  padding: 17px;
  border: 1px solid #e6e0fa;
  border-radius: 14px;
  background: linear-gradient(155deg, #fff, #f6f1ff);
  color: var(--text);
  text-align: left;
}

.quick-icon {
  display: block;
  margin-bottom: 6px;
  color: var(--purple);
}

.quick strong,
.quick small {
  display: block;
}

.quick strong {
  font-size: 13px;
}

.quick small {
  margin-top: 5px;
  color: var(--muted);
}

.layout2 {
  display: grid;
  grid-template-columns: minmax(0, 1.6fr) minmax(270px, 0.9fr);
  gap: 17px;
  margin-top: 18px;
}

.card-column {
  display: flex;
  flex-direction: column;
  gap: 17px;
}

.card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.card-head h2 {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 19px;
}

.card-head h2 svg {
  flex: none;
  color: var(--purple);
}

.muted {
  color: var(--muted);
}

.location-empty {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 10px 0;
  border-bottom: 1px solid #f0ecfa;
  color: var(--muted);
  font-size: 12px;
}

.location-empty svg {
  flex: none;
  color: var(--purple);
}

@media (max-width: 1150px) {
  .grid4 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .layout2 {
    grid-template-columns: 1fr;
  }
}
</style>
