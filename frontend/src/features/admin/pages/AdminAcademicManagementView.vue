
<script setup lang="ts">
import {
  BookOpen,
  CalendarRange,
  GraduationCap,
  NotebookText,
  School,
  Sparkles,
} from '@lucide/vue'

import BaseCard from '@/components/ui/BaseCard.vue'

const academicSections = [
  {
    title: 'Academic Years',
    description: 'Manage intake periods and academic calendars.',
    icon: CalendarRange,
    route: 'admin-academic-years',
  },
  {
    title: 'Year Levels',
    description: 'Track progression across all study years.',
    icon: School,
    route: 'admin-year-levels',
  },
  {
    title: 'Majors',
    description: 'Configure university programs and specialization paths.',
    icon: GraduationCap,
    route: 'admin-majors',
  },
  {
    title: 'Semesters',
    description: 'Coordinate enrollment timing and academic terms.',
    icon: NotebookText,
    route: 'admin-semesters',
  },
  {
    title: 'Subjects',
    description: 'Maintain the catalog of available courses.',
    icon: BookOpen,
    route: 'admin-subjects',
  },
  {
    title: 'Subject Offerings',
    description: 'Map subjects to terms, majors and year groups.',
    icon: Sparkles,
    route: 'admin-subject-offerings',
  },
]
</script>

<template>
  <section class="academics" aria-labelledby="admin-academics-title">
    <header class="page-header">
      <div>
        <p class="eyebrow">ACADEMIC OPERATIONS</p>
        <h1 id="admin-academics-title">Academic management</h1>
      </div>
    </header>

    <div class="grid">
      <RouterLink
        v-for="section in academicSections"
        :key="section.title"
        :to="{ name: section.route }"
        class="card-link"
      >
        <BaseCard
          as="article"
          class="card"
          padded
        >
          <div class="icon-wrap">
            <component :is="section.icon" :size="24" aria-hidden="true" />
          </div>

          <h2>{{ section.title }}</h2>
          <p>{{ section.description }}</p>
        </BaseCard>
      </RouterLink>
    </div>
  </section>
</template>

<style scoped>
.academics {
  color: var(--text);
}

.page-header {
  margin-bottom: 18px;
}

.eyebrow {
  margin: 0 0 8px;
  color: #6a43cc;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.15em;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  font-size: clamp(28px, 2.7vw, 36px);
}

.grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

/* Remove RouterLink underline */
.card-link {
  display: block;
  color: inherit;
  text-decoration: none;
}

/* Keep card design and add hover animation */
.card {
  min-height: 180px;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.card-link:hover .card {
  transform: scale(1.05);
  box-shadow: 0 10px 25px rgba(106, 67, 204, 0.12);
}

.card-link:focus-visible {
  outline: 2px solid var(--purple);
  outline-offset: 4px;
  border-radius: 12px;
}

.icon-wrap {
  display: grid;
  place-items: center;
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: #f3edff;
  color: var(--purple);
}

h2 {
  margin: 18px 0 8px;
  font-size: 18px;
}

p {
  margin: 0;
  color: var(--muted);
  line-height: 1.6;
}

@media (max-width: 760px) {
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
