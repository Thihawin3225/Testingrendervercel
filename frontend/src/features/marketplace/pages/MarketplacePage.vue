<script setup lang="ts">
import { computed, ref } from "vue";

import ListingCard from "../components/ListingCard.vue";
import { useMarketplaceListings } from "../composables/useMarketplace";

// Search and filters
const searchQuery = ref("");
const selectedCategory = ref("");
const selectedType = ref("");

// Fetch marketplace listings
const { data, isLoading, isError, error, refetch } = useMarketplaceListings();

// Get listings from API response
const listings = computed(() => {
  return data.value?.data ?? [];
});

// Get unique categories from listings
const categories = computed(() => {
  const categoryMap = new Map<number, string>();

  listings.value.forEach((listing) => {
    const category = listing.category;

    if (category?.id && category.name) {
      categoryMap.set(category.id, category.name);
    }
  });

  return Array.from(categoryMap, ([id, name]) => ({
    id,
    name,
  }));
});

// Filter listings
const filteredListings = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase();

  return listings.value.filter((listing) => {
    const matchesSearch =
      !keyword ||
      listing.title.toLowerCase().includes(keyword) ||
      (listing.description ?? "").toLowerCase().includes(keyword) ||
      (listing.user?.name ?? "").toLowerCase().includes(keyword);

    const matchesCategory =
      !selectedCategory.value ||
      String(listing.category?.id ?? "") === selectedCategory.value;

    const matchesType =
      !selectedType.value || listing.listing_type === selectedType.value;

    return matchesSearch && matchesCategory && matchesType;
  });
});

const errorMessage = computed(() => {
  if (error.value instanceof Error) {
    return error.value.message;
  }

  return "Something went wrong while loading marketplace listings.";
});

function clearFilters() {
  searchQuery.value = "";
  selectedCategory.value = "";
  selectedType.value = "";
}
</script>

<template>
  <main class="marketplace-page">
    <!-- Page Header -->
    <section class="marketplace-header">
      <div>
        <span class="eyebrow">CAMPUS BRIDGE</span>

        <h1>Campus Marketplace</h1>

        <p>
          Discover items, buy what you need, and exchange with other students.
        </p>
      </div>
      <RouterLink to="/marketplace/create" class="create-listing-button">
        <span>＋</span>
        Create Listing
      </RouterLink>

      <div class="listing-count">
        <strong>{{ filteredListings.length }}</strong>
        <span>Items Found</span>
      </div>
    </section>

    <!-- Search and Filters -->
    <section class="filter-section">
      <div class="search-wrapper">
        <span class="search-icon">⌕</span>

        <input
          v-model="searchQuery"
          type="search"
          placeholder="Search items, descriptions or sellers..."
          aria-label="Search marketplace listings"
        />
      </div>

      <div class="filter-controls">
        <select v-model="selectedCategory" aria-label="Filter by category">
          <option value="">All Categories</option>

          <option
            v-for="category in categories"
            :key="category.id"
            :value="String(category.id)"
          >
            {{ category.name }}
          </option>
        </select>

        <select v-model="selectedType" aria-label="Filter by listing type">
          <option value="">All Types</option>
          <option value="sale">For Sale</option>
          <option value="exchange">For Exchange</option>
        </select>

        <button type="button" class="clear-button" @click="clearFilters">
          Clear Filters
        </button>
      </div>
    </section>

    <!-- Loading State -->
    <section v-if="isLoading" class="state-container">
      <div class="spinner"></div>
      <h3>Loading marketplace...</h3>
      <p>Please wait while we fetch the listings.</p>
    </section>

    <!-- Error State -->
    <section v-else-if="isError" class="state-container error-state">
      <div class="state-icon">!</div>

      <h3>Unable to load listings</h3>

      <p>{{ errorMessage }}</p>

      <button type="button" @click="refetch()">Try Again</button>
    </section>

    <!-- Empty State -->
    <section v-else-if="filteredListings.length === 0" class="state-container">
      <div class="state-icon">⌕</div>

      <h3>No listings found</h3>

      <p>
        {{
          listings.length === 0
            ? "There are no marketplace listings available yet."
            : "Try changing your search or filters."
        }}
      </p>

      <button v-if="listings.length > 0" type="button" @click="clearFilters">
        Clear Filters
      </button>
    </section>

    <!-- Listing Grid -->
    <section v-else class="listing-section">
      <div class="section-heading">
        <div>
          <h2>Explore Listings</h2>
          <p>Find something useful for your campus life.</p>
        </div>

        <span>{{ filteredListings.length }} results</span>
      </div>

      <div class="listing-grid">
        <ListingCard
          v-for="listing in filteredListings"
          :key="listing.id"
          :listing="listing"
        />
      </div>
    </section>
  </main>
</template>

<style scoped>
.marketplace-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 30px;
}

.marketplace-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 30px;
}

.eyebrow {
  color: #6366f1;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 2px;
}

.marketplace-header h1 {
  margin: 9px 0;
  color: #1e293b;
  font-size: 30px;
  font-weight: 800;
}

.marketplace-header p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.6;
}

.listing-count {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 105px;
  padding: 16px;
  border: 1px solid #e0e7ff;
  border-radius: 12px;
  background: #eef2ff;
}

.listing-count strong {
  color: #4f46e5;
  font-size: 25px;
}

.listing-count span {
  color: #64748b;
  font-size: 11px;
}

.filter-section {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 30px;
  padding: 18px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #ffffff;
}

.search-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  top: 50%;
  left: 14px;
  color: #94a3b8;
  font-size: 23px;
  transform: translateY(-55%);
}

.search-wrapper input {
  width: 100%;
  box-sizing: border-box;
  padding: 13px 14px 13px 43px;
  border: 1px solid #e2e8f0;
  border-radius: 9px;
  outline: none;
  background: #f8fafc;
  color: #1e293b;
  font-size: 13px;
}

.search-wrapper input:focus {
  border-color: #818cf8;
  background: #ffffff;
}

.filter-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.filter-controls select {
  min-width: 160px;
  padding: 11px 13px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  background: #ffffff;
  color: #475569;
  font-size: 12px;
  cursor: pointer;
}

.clear-button {
  padding: 10px 15px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #ffffff;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.clear-button:hover {
  border-color: #a5b4fc;
  color: #4f46e5;
}

.section-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  margin-bottom: 18px;
}

.section-heading h2 {
  margin: 0 0 5px;
  color: #1e293b;
  font-size: 19px;
}

.section-heading p {
  margin: 0;
  color: #94a3b8;
  font-size: 12px;
}

.section-heading > span {
  color: #64748b;
  font-size: 12px;
}

.listing-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 22px;
}

.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  padding: 30px;
  text-align: center;
}

.state-container h3 {
  margin: 14px 0 7px;
  color: #1e293b;
  font-size: 17px;
}

.state-container p {
  max-width: 420px;
  margin: 0;
  color: #94a3b8;
  font-size: 13px;
  line-height: 1.6;
}
.create-listing-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 18px;
  border-radius: 9px;
  background: #4f46e5;
  color: white;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
}

.create-listing-button:hover {
  background: #4338ca;
}
.state-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #eef2ff;
  color: #6366f1;
  font-size: 25px;
  font-weight: 700;
}

.spinner {
  width: 35px;
  height: 35px;
  border: 3px solid #e0e7ff;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error-state .state-icon {
  background: #fee2e2;
  color: #dc2626;
}

.state-container button {
  margin-top: 17px;
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  background: #4f46e5;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

@media (max-width: 1050px) {
  .listing-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 650px) {
  .marketplace-page {
    padding: 18px;
  }

  .marketplace-header {
    align-items: flex-start;
  }

  .marketplace-header h1 {
    font-size: 24px;
  }

  .listing-count {
    min-width: 75px;
    padding: 12px 8px;
  }

  .listing-grid {
    grid-template-columns: 1fr;
  }

  .filter-controls {
    flex-direction: column;
  }

  .filter-controls select,
  .clear-button {
    width: 100%;
  }
}
</style>
