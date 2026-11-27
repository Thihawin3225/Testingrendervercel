<script setup lang="ts">
import { computed, ref } from "vue";

import {
  useMyListings,
  useListingRequests,
} from "../composables/useMarketplace";

import SellerRequestCard from "../components/SellerRequestCard.vue";

import type { MarketplaceListing } from "../types/marketplace";

// Get seller's own listings
const { data, isLoading, isError, error } = useMyListings();

// Selected listing
const selectedListing = ref<number | null>(null);

// Convert null to 0 when no listing is selected
const selectedListingId = computed(() => selectedListing.value ?? 0);

// Get incoming requests for selected listing
const {
  data: requests,
  isLoading: isRequestsLoading,
  isError: isRequestsError,
  error: requestsError,
} = useListingRequests(selectedListingId);

function selectListing(listing: MarketplaceListing) {
  selectedListing.value = listing.id;
}

function formatPrice(price: string | null) {
  if (price === null) return "Free";

  return Number(price).toLocaleString("en-US") + " MMK";
}
</script>

<template>
  <main class="seller-page">
    <section class="listings-section">
      <div class="section-header">
        <h2>My Listings</h2>

        <span class="listing-count">
          {{ data?.data.length ?? 0 }} Listings
        </span>
      </div>

      <div v-if="isLoading" class="state-message">Loading your listings...</div>

      <div v-else-if="isError" class="state-message error">
        {{ error?.message || "Failed to load listings." }}
      </div>

      <div v-else-if="!data?.data.length" class="state-message">
        You haven't created any listings yet.
      </div>

      <div v-else class="listing-grid">
        <article
          v-for="listing in data.data"
          :key="listing.id"
          class="listing-card"
          :class="{
            selected: selectedListing === listing.id,
          }"
          @click="selectListing(listing)"
        >
          <img
            v-if="listing.images[0]?.url"
            :src="listing.images[0].url"
            :alt="listing.title"
            class="listing-image"
          />

          <div v-else class="image-placeholder">No Image</div>

          <div class="listing-content">
            <span class="listing-status">
              {{ listing.status }}
            </span>

            <h3>{{ listing.title }}</h3>

            <p class="category">
              {{ listing.category.name }}
            </p>

            <p class="price">
              {{ formatPrice(listing.price) }}
            </p>

            <button
              type="button"
              class="view-button"
              @click.stop="selectListing(listing)"
            >
              View Requests
            </button>
          </div>
        </article>
      </div>
    </section>

    <section v-if="selectedListing !== null" class="requests-section">
      <div class="requests-header">
        <div>
          <h2>Incoming Requests</h2>

          <p>Requests for listing #{{ selectedListing }}</p>
        </div>

        <span class="request-count">
          {{ requests?.data.length ?? 0 }} Requests
        </span>
      </div>

      <!-- Loading -->
      <div v-if="isRequestsLoading" class="state-message">
        Loading incoming requests...
      </div>

      <!-- Error -->
      <div v-else-if="isRequestsError" class="state-message error">
        {{ requestsError?.message || "Failed to load requests." }}
      </div>

      <!-- Empty -->
      <div v-else-if="!requests?.data.length" class="state-message">
        No requests for this listing yet.
      </div>

      <!-- Request List -->
      <div v-else class="request-list">
        <SellerRequestCard
          v-for="request in requests.data"
          :key="request.id"
          :request="request"
        />
      </div>
    </section>
  </main>
</template>

<style scoped>
.seller-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px;
}

.page-header {
  margin-bottom: 32px;
}

.page-header h1 {
  margin: 0 0 8px;
  color: #1e293b;
  font-size: 28px;
}

.page-header p {
  margin: 0;
  color: #64748b;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.section-header h2,
.requests-section h2 {
  color: #1e293b;
  font-size: 20px;
}

.listing-count {
  color: #64748b;
  font-size: 13px;
}

.listing-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
}

.listing-card {
  overflow: hidden;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: white;
  cursor: pointer;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.listing-card:hover,
.listing-card.selected {
  border-color: #6366f1;
  box-shadow: 0 5px 18px #6366f11a;
}

.listing-image,
.image-placeholder {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.image-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  color: #94a3b8;
}

.listing-content {
  padding: 17px;
}

.listing-status {
  color: #d97706;
  font-size: 11px;
  text-transform: capitalize;
}

.listing-content h3 {
  margin: 8px 0;
  color: #1e293b;
  font-size: 17px;
}

.category {
  color: #64748b;
  font-size: 12px;
}

.price {
  color: #4f46e5;
  font-weight: 700;
}

.view-button {
  width: 100%;
  margin-top: 10px;
  padding: 11px;
  border: none;
  border-radius: 8px;
  background: #4f46e5;
  color: white;
  cursor: pointer;
}

.view-button:hover {
  background: #4338ca;
}

.state-message {
  padding: 30px;
  text-align: center;
  color: #64748b;
}

.error {
  color: #dc2626;
}

.requests-section {
  margin-top: 40px;
  padding: 24px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
}

.request-placeholder {
  color: #64748b;
}

@media (max-width: 600px) {
  .seller-page {
    padding: 20px 14px;
  }

  .listing-grid {
    grid-template-columns: 1fr;
  }
}
</style>
