<script setup lang="ts">
import { computed } from "vue";
import { RouterLink } from "vue-router";

import type { MarketplaceListing } from "../types/marketplace";

import {
  useSavedListings,
  useSaveListing,
  useUnsaveListing,
} from "../composables/useMarketplace";

const props = defineProps<{
  listing: MarketplaceListing;
}>();

const { data: savedListings } = useSavedListings();

const saveMutation = useSaveListing();
const unsaveMutation = useUnsaveListing();

const imageUrl = computed(() => {
  return props.listing.images?.[0]?.url || "";
});

const isSaved = computed(() => {
  return (
    savedListings.value?.data.some(
      (savedListing) => savedListing.id === props.listing.id,
    ) ?? false
  );
});

const isSaving = computed(() => {
  return saveMutation.isPending.value || unsaveMutation.isPending.value;
});

const formattedPrice = computed(() => {
  if (props.listing.listing_type === "donation") {
    return "Free";
  }

  if (props.listing.listing_type === "wanted") {
    return "Price not available";
  }

  if (props.listing.price === null) {
    return "Price not available";
  }

  const price = Number(props.listing.price);

  if (Number.isNaN(price)) {
    return "Price not available";
  }

  return `${new Intl.NumberFormat("en-US", {
    maximumFractionDigits: 0,
  }).format(price)} MMK`;
});

const sellerName = computed(() => {
  return props.listing.user?.name || "Unknown Seller";
});

const categoryName = computed(() => {
  return props.listing.category?.name || "Uncategorized";
});

async function toggleSave() {
  if (isSaving.value) {
    return;
  }

  try {
    if (isSaved.value) {
      await unsaveMutation.mutateAsync(props.listing.id);
    } else {
      await saveMutation.mutateAsync(props.listing.id);
    }
  } catch (error) {
    console.error("Failed to update saved listing:", error);
  }
}
</script>

<template>
  <div class="listing-card">
    <RouterLink :to="`/marketplace/${listing.id}`" class="listing-link">
      <div class="listing-image-wrapper">
        <img
          v-if="imageUrl"
          :src="imageUrl"
          :alt="listing.title"
          class="listing-image"
          loading="lazy"
        />

        <div v-else class="image-placeholder">
          <span>No Image Available</span>
        </div>

        <span :class="['listing-type-badge', listing.listing_type]">
          {{ listing.listing_type }}
        </span>
      </div>

      <div class="listing-content">
        <div class="listing-category">
          {{ categoryName }}
        </div>

        <h3 class="listing-title">
          {{ listing.title }}
        </h3>

        <p class="listing-description">
          {{ listing.description || "No description available." }}
        </p>

        <div class="listing-condition">
          <span>Condition:</span>
          <strong>
            {{ listing.condition || "Not specified" }}
          </strong>
        </div>

        <div class="listing-card-footer">
          <div class="listing-price">
            {{ formattedPrice }}
          </div>

          <div class="seller-name">Seller: {{ sellerName }}</div>
        </div>
      </div>
    </RouterLink>

    <button
      type="button"
      class="save-button"
      :class="{ saved: isSaved }"
      :disabled="isSaving"
      :aria-label="isSaved ? 'Remove from saved listings' : 'Save listing'"
      :title="isSaved ? 'Unsave listing' : 'Save listing'"
      @click="toggleSave"
    >
      {{ isSaved ? "★" : "☆" }}
    </button>
  </div>
</template>

<style scoped>
.listing-card {
  position: relative;
}

.listing-link {
  display: block;
  color: inherit;
  text-decoration: none;
}

.save-button {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 50%;
  background: #ffffff;
  color: #64748b;
  font-size: 25px;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
  transition: 0.2s ease;
}

.save-button:hover {
  color: #4f46e5;
  transform: scale(1.08);
}

.save-button.saved {
  color: #4f46e5;
}

.save-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.listing-card {
  display: block;
  min-width: 0;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #ffffff;
  color: inherit;
  text-decoration: none;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease;
}

.listing-card:hover {
  transform: translateY(-4px);
  border-color: #c7d2fe;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.09);
}

.listing-image-wrapper {
  position: relative;
  height: 210px;
  overflow: hidden;
  background: #f1f5f9;
}

.listing-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.listing-card:hover .listing-image {
  transform: scale(1.05);
}

.image-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  color: #94a3b8;
  font-size: 13px;
}

.listing-type-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  padding: 6px 11px;
  border-radius: 20px;
  background: #ffffff;
  color: #334155;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
}

.listing-type-badge.sale {
  color: #047857;
}

.listing-type-badge.exchange {
  color: #6d28d9;
}

.listing-content {
  padding: 16px;
}

.listing-category {
  margin-bottom: 8px;
  color: #6366f1;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.listing-title {
  margin: 0 0 8px;
  overflow: hidden;
  color: #1e293b;
  font-size: 16px;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.listing-description {
  display: -webkit-box;
  min-height: 36px;
  margin: 0 0 12px;
  overflow: hidden;
  color: #64748b;
  font-size: 12px;
  line-height: 1.5;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.listing-condition {
  display: flex;
  gap: 5px;
  margin-bottom: 14px;
  color: #64748b;
  font-size: 12px;
}

.listing-condition strong {
  color: #334155;
  font-weight: 600;
  text-transform: capitalize;
}

.listing-card-footer {
  padding-top: 13px;
  border-top: 1px solid #f1f5f9;
}

.listing-price {
  margin-bottom: 6px;
  color: #4f46e5;
  font-size: 17px;
  font-weight: 800;
}

.seller-name {
  overflow: hidden;
  color: #94a3b8;
  font-size: 11px;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
