<script setup lang="ts">
import { useRoute, useRouter } from "vue-router";
import { computed, ref } from "vue";
import { useQuery } from "@tanstack/vue-query";

import {
  useMarketplaceListing,
  useMyRequests,
} from "../composables/useMarketplace";

import { authApi } from "../../../modules/auth/api/authApi";
import { getToken } from "../../../modules/auth/authToken";

import ListingRequestForm from "../components/ListingRequestForm.vue";

// ============================================
// Router
// ============================================

const route = useRoute();
const router = useRouter();

const listingId = computed(() => Number(route.params.id));

// ============================================
// Marketplace Listing
// ============================================

const {
  data: listing,
  isLoading,
  isError,
  error,
  refetch,
} = useMarketplaceListing(listingId);

// ============================================
// Current User
// ============================================

const { data: currentUser, isLoading: isUserLoading } = useQuery({
  queryKey: ["auth", "me"],
  queryFn: authApi.me,
  enabled: !!getToken(),
  retry: false,
});

// ============================================
// My Requests
// ============================================

const {
  data: myRequests,
  isLoading: isRequestsLoading,
  isError: isRequestsError,
} = useMyRequests();

// ============================================
// Computed Properties
// ============================================

// Check listing owner
const isOwner = computed(() => {
  return listing.value?.user?.id === currentUser.value?.id;
});

// Check wanted listing
const isWantedListing = computed(() => {
  return listing.value?.listing_type === "wanted";
});

// Check whether current user already requested this listing
const isAlreadyRequested = computed(() => {
  if (!listing.value || !myRequests.value?.data) {
    return false;
  }

  const currentListingId = listing.value.id;

  return myRequests.value.data.some((request) => {
    return (
      request.listing?.id === currentListingId &&
      ["pending", "accepted"].includes(request.status)
    );
  });
});

// ============================================
// Image Gallery
// ============================================

const selectedImage = ref(0);

const images = computed(() => {
  return listing.value?.images ?? [];
});

const currentImage = computed(() => {
  return images.value[selectedImage.value]?.url ?? "";
});

// ============================================
// Price
// ============================================

const formattedPrice = computed(() => {
  if (listing.value?.listing_type === "wanted") {
    return "Wanted";
  }

  if (!listing.value?.price) {
    return "Free";
  }

  const price = Number(listing.value.price);

  if (Number.isNaN(price)) {
    return "Price not available";
  }

  return `${new Intl.NumberFormat("en-US").format(price)} MMK`;
});

// ============================================
// Error Message
// ============================================

const errorMessage = computed(() => {
  if (error.value instanceof Error) {
    return error.value.message;
  }

  return "Unable to load listing details.";
});

// ============================================
// Actions
// ============================================

function goBack() {
  router.push({ name: "marketplace" });
}

function goToEdit() {
  router.push({
    name: "marketplace-edit",
    params: { id: listingId.value },
  });
}

async function deleteListing() {
  const confirmed = window.confirm(
    "Are you sure you want to delete this listing?",
  );

  if (!confirmed) return;

  // Connect Delete API in the next step.
  alert("Delete API has not been connected yet.");
}
</script>

<template>
  <main class="detail-page">
    <!-- Back Button -->
    <button class="back-button" type="button" @click="goBack">
      ← Back to Marketplace
    </button>

    <!-- Loading -->
    <section v-if="isLoading" class="state-container">
      <div class="spinner"></div>
      <h3>Loading listing details...</h3>
    </section>

    <!-- Error -->
    <section v-else-if="isError" class="state-container">
      <h3>Unable to load listing</h3>
      <p>{{ errorMessage }}</p>

      <button type="button" @click="refetch()">Try Again</button>
    </section>

    <!-- Listing Detail -->
    <template v-else-if="listing">
      <div class="detail-layout">
        <!-- Image Gallery -->
        <section class="gallery-section">
          <div class="main-image">
            <img v-if="currentImage" :src="currentImage" :alt="listing.title" />

            <div v-else class="image-placeholder">No Image Available</div>
          </div>

          <!-- Thumbnails -->
          <div v-if="images.length > 1" class="thumbnail-list">
            <button
              v-for="(image, index) in images"
              :key="image.id"
              type="button"
              :class="['thumbnail-button', { active: selectedImage === index }]"
              @click="selectedImage = index"
            >
              <img :src="image.url" :alt="`Image ${index + 1}`" />
            </button>
          </div>
        </section>

        <!-- Listing Information -->
        <section class="information-section">
          <div class="category-label">
            {{ listing.category?.name || "Uncategorized" }}
          </div>

          <h1>{{ listing.title }}</h1>

          <div class="price">
            {{ formattedPrice }}
          </div>

          <!-- Listing Meta -->
          <div class="listing-meta">
            <span>{{ listing.listing_type }}</span>

            <span>
              {{ listing.condition || "Condition not specified" }}
            </span>

            <span>{{ listing.status }}</span>
          </div>

          <div class="divider"></div>

          <h2>Description</h2>

          <p class="description">
            {{ listing.description || "No description available." }}
          </p>

          <!-- Seller Information -->
          <div class="seller-information">
            <div class="seller-avatar">
              {{ (listing.user?.name || "S").charAt(0).toUpperCase() }}
            </div>

            <div>
              <span class="seller-label">Seller</span>

              <strong>
                {{ listing.user?.name || "Unknown Seller" }}
              </strong>
            </div>
          </div>

          <!-- ================================= -->
          <!-- OWNER ACTIONS -->
          <!-- ================================= -->

          <div v-if="isOwner" class="owner-actions">
            <button type="button" class="edit-button" @click="goToEdit">
              Edit Listing
            </button>

            <button
              type="button"
              class="delete-button"
              :disabled="['reserved', 'sold'].includes(listing.status)"
              @click="deleteListing"
            >
              Delete Listing
            </button>
          </div>

          <!-- ================================= -->
          <!-- STUDENT REQUEST SECTION -->
          <!-- ================================= -->

          <template v-else>
            <!-- Checking current user -->
            <div v-if="isUserLoading" class="unavailable-message">
              Checking user information...
            </div>

            <!-- Wanted Listing -->
            <div
              v-else-if="isWantedListing"
              class="unavailable-message wanted-message"
            >
              <strong>Wanted Post</strong>

              <p>
                This is a wanted post. Students cannot send requests to wanted
                listings.
              </p>
            </div>

            <!-- Already Requested -->
            <div
              v-else-if="isAlreadyRequested"
              class="already-requested-message"
            >
              <strong> You have already requested this post. </strong>

              <span> Your request has been sent successfully. </span>
            </div>

            <!-- Checking Request -->
            <div v-else-if="isRequestsLoading" class="unavailable-message">
              Checking your request...
            </div>

            <!-- Failed to Load Requests -->
            <div
              v-else-if="isRequestsError"
              class="unavailable-message error-message"
            >
              Unable to check your previous requests. Please refresh the page
              and try again.
            </div>

            <!-- Listing Accepting Requests -->
            <div
              v-else-if="listing.status === 'pending'"
              class="request-section"
            >
              <h2>Interested in this item?</h2>

              <p>Send a request to contact the seller.</p>

              <ListingRequestForm :listing-id="listing.id" />
            </div>

            <!-- Listing Unavailable -->
            <div v-else class="unavailable-message">
              This listing is not currently accepting requests.
            </div>
          </template>
        </section>
      </div>
    </template>
  </main>
</template>

<style scoped>
.detail-page {
  max-width: 1250px;
  margin: 0 auto;
  padding: 30px;
}

/* Back Button */

.back-button {
  margin-bottom: 25px;
  padding: 10px 15px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #ffffff;
  color: #475569;
  font-size: 13px;
  cursor: pointer;
}

.back-button:hover {
  border-color: #818cf8;
  color: #4f46e5;
}

/* Layout */

.detail-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
  gap: 40px;
}

.gallery-section {
  min-width: 0;
}

.information-section {
  min-width: 0;
}

/* Main Image */

.main-image {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 430px;
  overflow: hidden;
  border-radius: 16px;
  background: #f1f5f9;
}

.main-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.image-placeholder {
  color: #94a3b8;
  font-size: 14px;
}

/* Thumbnails */

.thumbnail-list {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 15px;
}

.thumbnail-button {
  width: 75px;
  height: 75px;
  overflow: hidden;
  padding: 3px;
  border: 2px solid transparent;
  border-radius: 9px;
  background: #f1f5f9;
  cursor: pointer;
}

.thumbnail-button.active {
  border-color: #6366f1;
}

.thumbnail-button img {
  width: 100%;
  height: 100%;
  border-radius: 5px;
  object-fit: cover;
}

/* Listing Information */

.category-label {
  margin-bottom: 12px;
  color: #6366f1;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
}

.information-section h1 {
  margin: 0 0 15px;
  color: #1e293b;
  font-size: 29px;
  line-height: 1.3;
}

.price {
  margin-bottom: 18px;
  color: #4f46e5;
  font-size: 25px;
  font-weight: 800;
}

/* Meta */

.listing-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 9px;
}

.listing-meta span {
  padding: 6px 10px;
  border-radius: 20px;
  background: #f1f5f9;
  color: #475569;
  font-size: 11px;
  text-transform: capitalize;
}

.divider {
  height: 1px;
  margin: 25px 0;
  background: #e2e8f0;
}

.information-section h2 {
  margin: 0 0 12px;
  color: #1e293b;
  font-size: 17px;
}

/* Description */

.description {
  color: #64748b;
  font-size: 14px;
  line-height: 1.8;
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}

/* Seller */

.seller-information {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 25px 0;
  padding: 15px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.seller-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: #eef2ff;
  color: #4f46e5;
  font-weight: 800;
}

.seller-information > div:last-child {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.seller-label {
  color: #94a3b8;
  font-size: 11px;
}

.seller-information strong {
  color: #334155;
  font-size: 13px;
}

/* Owner Actions */

.owner-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.owner-actions button {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 8px;
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.edit-button {
  background: #4f46e5;
}

.delete-button {
  background: #dc2626;
}

.delete-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

/* Request Section */

.request-section {
  margin-top: 25px;
}

.request-section > p {
  margin: 0 0 15px;
  color: #64748b;
  font-size: 13px;
}

/* Already Requested */

.already-requested-message {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 20px;
  padding: 16px;
  border: 1px solid #bbf7d0;
  border-radius: 10px;
  background: #f0fdf4;
  color: #15803d;
  font-size: 13px;
}

.already-requested-message span {
  color: #64748b;
}

/* Unavailable */

.unavailable-message {
  margin-top: 20px;
  padding: 15px;
  border-radius: 9px;
  background: #f8fafc;
  color: #64748b;
  font-size: 13px;
  line-height: 1.7;
}

.wanted-message {
  border: 1px solid #fde68a;
  background: #fffbeb;
  color: #92400e;
}

.wanted-message p {
  margin: 6px 0 0;
}

.error-message {
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
}

/* Loading / Error */

.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  text-align: center;
}

.state-container p {
  color: #64748b;
  font-size: 13px;
}

.state-container button {
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  background: #4f46e5;
  color: white;
  cursor: pointer;
}

.spinner {
  width: 35px;
  height: 35px;
  border: 3px solid #e0e7ff;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Responsive */

@media (max-width: 800px) {
  .detail-page {
    padding: 18px;
  }

  .detail-layout {
    grid-template-columns: 1fr;
    gap: 25px;
  }

  .main-image {
    height: 330px;
  }

  .information-section h1 {
    font-size: 24px;
  }
}
</style>
