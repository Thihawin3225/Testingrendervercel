<script setup lang="ts">
import { RouterLink } from "vue-router";

import ListingCard from "../components/ListingCard.vue";
import { useSavedListings } from "../composables/useMarketplace";

const { data, isLoading, isError, refetch } = useSavedListings();
</script>

<template>
  <section class="saved-listings-page">
    <div class="page-header">
      <div>
        <h1>Saved Listings</h1>
        <p>Your bookmarked marketplace listings.</p>
      </div>

      <RouterLink :to="{ name: 'marketplace' }" class="back-link">
        Browse Marketplace
      </RouterLink>
    </div>

    <div v-if="isLoading" class="state-message">Loading saved listings...</div>

    <div v-else-if="isError" class="state-message error">
      Failed to load saved listings.

      <button @click="refetch()">Try Again</button>
    </div>

    <div v-else-if="!data?.data.length" class="empty-state">
      <div class="empty-icon">☆</div>
      <h2>No Saved Listings Yet</h2>
      <p>Save listings that interest you and find them here later.</p>

      <RouterLink :to="{ name: 'marketplace' }" class="browse-button">
        Explore Marketplace
      </RouterLink>
    </div>

    <div v-else>
      <p class="result-count">{{ data.meta.total }} saved listing(s)</p>

      <div class="listings-grid">
        <ListingCard
          v-for="listing in data.data"
          :key="listing.id"
          :listing="listing"
        />
      </div>
    </div>
  </section>
</template>

<style scoped>
.saved-listings-page {
  padding: 28px;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 28px;
}

.page-header h1 {
  margin: 0 0 8px;
  color: #1e293b;
  font-size: 26px;
}

.page-header p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.back-link,
.browse-button {
  display: inline-block;
  padding: 10px 16px;
  border-radius: 8px;
  background: #4f46e5;
  color: white;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.result-count {
  margin-bottom: 18px;
  color: #64748b;
  font-size: 13px;
}

.listings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 22px;
}

.state-message {
  padding: 40px 20px;
  color: #64748b;
  text-align: center;
}

.error {
  color: #dc2626;
}

.error button {
  display: block;
  margin: 12px auto;
  padding: 8px 14px;
  cursor: pointer;
}

.empty-state {
  padding: 70px 20px;
  text-align: center;
}

.empty-icon {
  color: #6366f1;
  font-size: 54px;
}

.empty-state h2 {
  color: #1e293b;
}

.empty-state p {
  margin-bottom: 22px;
  color: #64748b;
  font-size: 14px;
}

@media (max-width: 600px) {
  .saved-listings-page {
    padding: 18px;
  }

  .page-header {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
