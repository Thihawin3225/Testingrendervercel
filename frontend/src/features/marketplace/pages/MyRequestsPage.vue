<script setup lang="ts">
import { computed } from "vue";
import { RouterLink } from "vue-router";

import {
  useMyRequests,
  useCancelListingRequest,
  useConfirmListingRequest,
} from "../composables/useMarketplace";

import type { ListingRequest } from "../types/marketplace";

const { data: requestsResponse, isLoading, isError, error } = useMyRequests();

const cancelMutation = useCancelListingRequest();
const confirmRequestMutation = useConfirmListingRequest();

const requests = computed(() => requestsResponse.value?.data ?? []);

function formatPrice(price: string | number | null): string {
  if (price === null || price === "") {
    return "No price";
  }

  return `${Number(price).toLocaleString("en-US")} MMK`;
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}

function formatDateTime(date: string | null | undefined): string {
  if (!date) return "Not scheduled";

  const parsedDate = new Date(date);

  if (Number.isNaN(parsedDate.getTime())) {
    return "Invalid date";
  }

  return parsedDate.toLocaleString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}

function formatStatus(status: string): string {
  return status.charAt(0).toUpperCase() + status.slice(1);
}

function cancelRequest(request: ListingRequest) {
  if (request.status !== "pending") return;

  const confirmed = window.confirm(
    `Are you sure you want to cancel your request for "${request.listing.title}"?`,
  );

  if (!confirmed) return;

  cancelMutation.mutate(request.id);
}

function handleConfirmRequest(requestId: number) {
  confirmRequestMutation.mutate(requestId);
}
</script>

<template>
  <main class="my-requests-page">
    <!-- Page Header -->
    <header class="page-header">
      <div>
        <h1>My Requests</h1>
        <p>Manage the requests you have sent to other students.</p>
      </div>

      <span class="total-count"> {{ requests.length }} Requests </span>
    </header>

    <!-- Loading -->
    <div v-if="isLoading" class="state-message">Loading your requests...</div>

    <!-- Error -->
    <div v-else-if="isError" class="state-message error">
      {{ error?.message || "Failed to load your requests." }}
    </div>

    <!-- Empty -->
    <div v-else-if="requests.length === 0" class="empty-state">
      <h2>No Requests Yet</h2>

      <p>
        You haven't sent any listing requests yet. Explore the marketplace and
        find something you need.
      </p>
    </div>

    <!-- Request List -->
    <section v-else class="request-list">
      <article
        v-for="request in requests"
        :key="request.id"
        class="request-card"
      >
        <!-- Buyer Confirmation Required -->
        <div
          v-if="request.status === 'pending' && !request.confirmation"
          class="confirmation-panel"
        >
          <h3>Listing Updated</h3>

          <p>
            The seller has updated this listing. Please review the updated post
            and confirm if you are still interested.
          </p>

          <RouterLink
            :to="`/marketplace/${request.listing.id}`"
            class="review-listing-button"
          >
            Review Updated Listing
          </RouterLink>

          <button
            type="button"
            class="confirm-button"
            :disabled="confirmRequestMutation.isPending.value"
            @click="handleConfirmRequest(request.id)"
          >
            {{
              confirmRequestMutation.isPending.value
                ? "Confirming..."
                : "Confirm Updated Listing"
            }}
          </button>
        </div>

        <!-- Already Confirmed -->
        <div
          v-else-if="request.status === 'pending' && request.confirmation"
          class="confirmation-success"
        >
          You confirmed this listing.
        </div>

        <!-- Request Main Information -->
        <div class="request-main">
          <div class="request-info">
            <span class="request-id"> Request #{{ request.id }} </span>

            <h2>
              <RouterLink
                :to="`/marketplace/${request.listing.id}`"
                class="listing-title-link"
              >
                {{ request.listing.title }}
              </RouterLink>
            </h2>

            <p class="listing-type">
              {{ request.listing.listing_type }}
            </p>

            <p class="price">
              {{ formatPrice(request.listing.price) }}
            </p>

            <!-- Buyer's Original Request Message -->
            <div class="message-box">
              <span class="message-label"> Your Request Message </span>

              <p>
                {{ request.message || "No message provided." }}
              </p>
            </div>

            <!-- View Listing Post -->
            <RouterLink
              :to="`/marketplace/${request.listing.id}`"
              class="view-post-button"
            >
              View Listing Post →
            </RouterLink>

            <p class="request-date">
              Requested on {{ formatDate(request.created_at) }}
            </p>
          </div>

          <div class="request-actions">
            <span :class="['status-badge', `status-${request.status}`]">
              {{ formatStatus(request.status) }}
            </span>

            <button
              v-if="request.status === 'pending'"
              type="button"
              class="cancel-button"
              :disabled="cancelMutation.isPending.value"
              @click="cancelRequest(request)"
            >
              {{
                cancelMutation.isPending.value
                  ? "Cancelling..."
                  : "Cancel Request"
              }}
            </button>
          </div>
        </div>

        <!-- Seller Message: Click to View -->
        <details v-if="request.seller_message" class="seller-message-accordion">
          <summary>
            <span>Message from Seller</span>

            <span class="message-action"> Click to view </span>
          </summary>

          <div class="seller-message-content">
            {{ request.seller_message }}
          </div>
        </details>

        <!-- Schedule Information -->
        <section v-if="request.status === 'accepted'" class="schedule-panel">
          <div class="schedule-heading">
            <div>
              <span class="schedule-label"> SELLER SCHEDULE </span>

              <h3>Your Meeting Details</h3>
            </div>

            <span class="schedule-confirmed"> Scheduled </span>
          </div>

          <div class="schedule-details">
            <!-- Seller Phone -->
            <div class="schedule-item">
              <span class="detail-label"> Seller Phone </span>

              <a
                v-if="request.seller_phone"
                :href="`tel:${request.seller_phone}`"
                class="detail-value phone-link"
              >
                {{ request.seller_phone }}
              </a>

              <span v-else class="detail-value"> Not provided </span>
            </div>

            <!-- Campus Location -->
            <div class="schedule-item">
              <span class="detail-label"> Meeting Location </span>

              <span class="detail-value">
                {{
                  request.campus_location?.display_name ||
                  request.campus_location?.name ||
                  "Not provided"
                }}
              </span>
            </div>

            <!-- Meeting Date -->
            <div class="schedule-item">
              <span class="detail-label"> Meeting Date & Time </span>

              <span class="detail-value">
                {{ formatDateTime(request.meeting_at) }}
              </span>
            </div>
          </div>

          <p class="schedule-notice">
            Your request has been accepted. Please contact the seller if you
            need to discuss the meeting details.
          </p>
        </section>

        <!-- Completed / Sold -->
        <div
          v-if="
            request.listing.status === 'sold' && request.status === 'accepted'
          "
          class="completed-notice"
        >
          This listing has been marked as completed.
        </div>
      </article>
    </section>
  </main>
</template>

<style scoped>
/* ============================================
   Main Page
============================================ */

.my-requests-page {
  width: 100%;
  max-width: 1100px;
  margin: 0 auto;
  padding: 32px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
}

.page-header h1 {
  margin: 0 0 8px;
  font-size: 28px;
  color: #1f2937;
}

.page-header p {
  margin: 0;
  color: #6b7280;
}

.total-count {
  padding: 9px 15px;
  border-radius: 20px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

/* ============================================
   Request List and Card
============================================ */

.request-list {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.request-card {
  padding: 22px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
}

.request-main {
  display: flex;
  justify-content: space-between;
  gap: 24px;
}

.request-info {
  min-width: 0;
}

.request-info h2 {
  margin: 8px 0;
  color: #111827;
  font-size: 20px;
}

.request-id,
.request-date {
  color: #9ca3af;
  font-size: 13px;
}

/* ============================================
   Listing Post Link
============================================ */

.listing-title-link {
  color: #111827;
  text-decoration: none;
  transition: color 0.2s;
}

.listing-title-link:hover {
  color: #2563eb;
}

.view-post-button {
  display: inline-block;
  margin-top: 8px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.view-post-button:hover {
  text-decoration: underline;
}

.listing-type {
  color: #6b7280;
  text-transform: capitalize;
  margin: 6px 0;
}

.price {
  color: #2563eb;
  font-weight: 700;
  margin: 10px 0;
}

/* ============================================
   Buyer's Request Message
============================================ */

.message-box {
  margin-top: 16px;
  padding: 12px;
  border-radius: 8px;
  background: #f9fafb;
}

.message-label {
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
}

.message-box p {
  margin: 6px 0 0;
  color: #374151;
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}

.request-date {
  margin-top: 16px;
}

/* ============================================
   Request Actions
============================================ */

.request-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 14px;
}

.status-badge {
  padding: 7px 13px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  text-transform: capitalize;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-accepted {
  background: #dcfce7;
  color: #166534;
}

.status-rejected {
  background: #fee2e2;
  color: #991b1b;
}

.status-cancelled {
  background: #f3f4f6;
  color: #4b5563;
}

.cancel-button {
  padding: 9px 14px;
  border: 1px solid #dc2626;
  border-radius: 7px;
  background: white;
  color: #dc2626;
  cursor: pointer;
}

.cancel-button:hover:not(:disabled) {
  background: #dc2626;
  color: white;
}

.cancel-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ============================================
   Buyer Confirmation
============================================ */

.confirmation-panel {
  margin-bottom: 20px;
  padding: 18px;
  border: 1px solid #fcd34d;
  border-radius: 10px;
  background: #fffbeb;
}

.confirmation-panel h3 {
  margin: 0 0 8px;
  color: #92400e;
  font-size: 16px;
}

.confirmation-panel p {
  margin: 0;
  color: #92400e;
  font-size: 14px;
  line-height: 1.6;
}

.review-listing-button {
  display: inline-block;
  margin-top: 14px;
  margin-right: 10px;
  padding: 10px 14px;
  border: 1px solid #d97706;
  border-radius: 7px;
  color: #92400e;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.review-listing-button:hover {
  background: #fef3c7;
}

.confirm-button {
  margin-top: 14px;
  padding: 10px 15px;
  border: none;
  border-radius: 7px;
  background: #2563eb;
  color: white;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.confirm-button:hover:not(:disabled) {
  background: #1d4ed8;
}

.confirm-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.confirmation-success {
  margin-bottom: 16px;
  padding: 12px;
  border-radius: 8px;
  background: #dcfce7;
  color: #166534;
  font-size: 13px;
  font-weight: 600;
}

/* ============================================
   Seller Message Accordion
============================================ */

.seller-message-accordion {
  margin-top: 18px;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  background: #f8fbff;
  overflow: hidden;
}

.seller-message-accordion summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding: 15px;
  color: #1e40af;
  font-weight: 600;
  cursor: pointer;
  list-style: none;
}

.seller-message-accordion summary::-webkit-details-marker {
  display: none;
}

.seller-message-accordion summary::after {
  content: "+";
  font-size: 20px;
}

.seller-message-accordion[open] summary::after {
  content: "−";
}

.message-action {
  margin-left: auto;
  color: #64748b;
  font-size: 12px;
  font-weight: 400;
}

.seller-message-content {
  padding: 15px;
  border-top: 1px solid #bfdbfe;
  color: #334155;
  font-size: 14px;
  line-height: 1.7;
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}

/* ============================================
   Seller Schedule Information
============================================ */

.schedule-panel {
  margin-top: 22px;
  padding: 20px;
  border: 1px solid #bfdbfe;
  border-radius: 12px;
  background: #f8fbff;
}

.schedule-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.schedule-label {
  color: #2563eb;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
}

.schedule-heading h3 {
  margin: 6px 0 0;
  color: #1e293b;
  font-size: 18px;
}

.schedule-confirmed {
  padding: 7px 12px;
  border-radius: 20px;
  background: #dcfce7;
  color: #166534;
  font-size: 12px;
  font-weight: 700;
}

.schedule-details {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.schedule-item {
  display: flex;
  flex-direction: column;
  gap: 7px;
  padding: 13px;
  border: 1px solid #e2e8f0;
  border-radius: 9px;
  background: white;
  overflow-wrap: anywhere;
}

.detail-label {
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.detail-value {
  color: #1e293b;
  font-size: 14px;
  font-weight: 600;
}

.phone-link {
  color: #2563eb;
  text-decoration: none;
}

.phone-link:hover {
  text-decoration: underline;
}

.schedule-notice {
  margin: 16px 0 0;
  color: #64748b;
  font-size: 12px;
  line-height: 1.6;
}

/* ============================================
   Completed Notice
============================================ */

.completed-notice {
  margin-top: 16px;
  padding: 12px;
  border-radius: 8px;
  background: #dcfce7;
  color: #166534;
  font-size: 13px;
  font-weight: 600;
}

/* ============================================
   Loading / Error / Empty States
============================================ */

.state-message,
.empty-state {
  padding: 50px 20px;
  text-align: center;
  color: #6b7280;
}

.error {
  color: #dc2626;
}

.empty-state {
  border: 1px dashed #d1d5db;
  border-radius: 12px;
}

.empty-state h2 {
  color: #374151;
}

/* ============================================
   Responsive
============================================ */

@media (max-width: 650px) {
  .my-requests-page {
    padding: 18px;
  }

  .page-header,
  .request-main {
    align-items: flex-start;
    flex-direction: column;
  }

  .request-actions {
    align-items: flex-start;
  }

  .schedule-details {
    grid-template-columns: 1fr;
  }

  .schedule-heading {
    align-items: flex-start;
    flex-direction: column;
  }

  .seller-message-accordion summary {
    flex-wrap: wrap;
  }

  .review-listing-button {
    margin-right: 0;
  }
}
</style>
