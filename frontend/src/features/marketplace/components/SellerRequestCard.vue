<script setup lang="ts">
import { computed, ref } from "vue";

import {
  useUpdateRequestStatus,
  useCampusLocations,
  useAcceptListingRequest,
  useReleaseSchedule,
  useCompleteListing,
} from "../composables/useMarketplace";

import type {
  ListingRequest,
  AcceptListingRequestPayload,
} from "../types/marketplace";

const props = defineProps<{
  request: ListingRequest;
}>();

// Existing reject mutation
const statusMutation = useUpdateRequestStatus();

// Campus locations
const {
  data: campusResponse,
  isLoading: isLocationsLoading,
  isError: isLocationsError,
} = useCampusLocations();

const activeLocations = computed(() =>
  (campusResponse.value?.data ?? []).filter(
    (location) => location.is_active === true || location.is_active === 1,
  ),
);

// Accept + Schedule
const acceptMutation = useAcceptListingRequest();

// Release and Complete
const releaseMutation = useReleaseSchedule();
const completeMutation = useCompleteListing();

// Modal state
const showScheduleModal = ref(false);

// Form fields
const sellerPhone = ref("");
const campusLocationId = ref<number | "">("");
const sellerMessage = ref("");
const meetingDate = ref("");
const meetingTime = ref("");

// Minimum date = today
const today = new Date().toLocaleDateString("en-CA");

const isSubmitting = computed(
  () =>
    acceptMutation.isPending.value ||
    statusMutation.isPending.value ||
    releaseMutation.isPending.value ||
    completeMutation.isPending.value,
);

// Open schedule modal
function openScheduleModal() {
  // Buyer must confirm the updated listing first
  if (!props.request.confirmation) {
    window.alert(
      "The buyer must confirm the updated listing before you can schedule a meeting.",
    );
    return;
  }

  sellerPhone.value = "";
  campusLocationId.value = "";
  sellerMessage.value = "";
  meetingDate.value = "";
  meetingTime.value = "";

  showScheduleModal.value = true;
}

// Close schedule modal
function closeScheduleModal() {
  if (acceptMutation.isPending.value) return;

  showScheduleModal.value = false;
}

// Reject request
function rejectRequest() {
  const confirmed = window.confirm(
    "Are you sure you want to reject this request?",
  );

  if (!confirmed) return;

  statusMutation.mutate({
    requestId: props.request.id,
    status: "rejected",
  });
}

// Submit schedule
function submitSchedule() {
  // 1. Check buyer confirmation
  if (!props.request.confirmation) {
    window.alert(
      "You cannot schedule this request until the buyer confirms the updated listing.",
    );
    return;
  }

  // 2. Validate required fields
  if (
    !sellerPhone.value.trim() ||
    !campusLocationId.value ||
    !meetingDate.value ||
    !meetingTime.value
  ) {
    window.alert("Please fill in all required fields.");
    return;
  }

  // 3. Combine date and time
  const meetingAt = `${meetingDate.value}T${meetingTime.value}:00`;

  // 4. Prepare API payload
  const payload: AcceptListingRequestPayload = {
    seller_phone: sellerPhone.value.trim(),
    campus_location_id: Number(campusLocationId.value),
    seller_message: sellerMessage.value.trim(),
    meeting_at: meetingAt,
  };

  // 5. Call mutation
  acceptMutation.mutate(
    {
      requestId: props.request.id,
      payload,
    },
    {
      onSuccess: () => {
        window.alert("Meeting scheduled successfully.");
        showScheduleModal.value = false;
      },
      onError: () => {
        window.alert(
          "Failed to schedule meeting. Please check the request and try again.",
        );
      },
    },
  );
}

// Release schedule
function releaseCurrentSchedule() {
  const confirmed = window.confirm(
    "Are you sure the meeting was unsuccessful and you want to release this schedule?",
  );

  if (!confirmed) return;

  releaseMutation.mutate(props.request.listing.id, {
    onSuccess: () => {
      window.alert("Schedule released successfully.");
    },
    onError: () => {
      window.alert("Failed to release schedule.");
    },
  });
}

// Complete listing
function markAsDone() {
  const confirmed = window.confirm(
    "Confirm that the transaction is completed and this item has been sold?",
  );

  if (!confirmed) return;

  completeMutation.mutate(props.request.listing.id, {
    onSuccess: () => {
      window.alert("Listing marked as completed.");
    },
    onError: () => {
      window.alert("Failed to complete listing.");
    },
  });
}

const formattedMeetingDate = computed(() => {
  if (!props.request.meeting_at) return "";

  return new Date(props.request.meeting_at).toLocaleString();
});

const isReserved = computed(() => props.request.listing.status === "reserved");

const isSold = computed(() => props.request.listing.status === "sold");
</script>

<template>
  <article class="seller-request-card">
    <div class="request-header">
      <div class="request-user">
        <div class="user-avatar">
          {{ request.requester.name.charAt(0).toUpperCase() }}
        </div>

        <div>
          <h3>{{ request.requester.name }}</h3>
          <p class="listing-title">{{ request.listing.title }}</p>
        </div>
      </div>

      <span :class="['status-badge', `status-${request.status}`]">
        {{ request.status }}
      </span>
    </div>

    <p class="request-message">
      {{ request.message || "No message provided." }}
    </p>

    <!-- Pending request -->
    <div v-if="request.status === 'pending'">
      <!-- Buyer confirmation warning -->
      <div v-if="!request.confirmation" class="confirmation-required">
        <strong>Buyer Confirmation Required</strong>

        <p>
          You have updated this listing. The buyer must confirm that they are
          still interested before you can schedule a meeting.
        </p>
      </div>

      <div class="request-actions">
        <button
          type="button"
          class="accept-button"
          :disabled="
            isSubmitting ||
            !request.confirmation ||
            request.listing.status === 'reserved' ||
            isSold
          "
          @click="openScheduleModal"
        >
          {{
            !request.confirmation
              ? "Waiting for Buyer Confirmation"
              : "Accept & Schedule"
          }}
        </button>

        <button
          type="button"
          class="reject-button"
          :disabled="isSubmitting"
          @click="rejectRequest"
        >
          Reject
        </button>
      </div>
    </div>

    <!-- Accepted request with reserved listing -->
    <div
      v-else-if="request.status === 'accepted' && isReserved"
      class="schedule-section"
    >
      <h4>Meeting Schedule</h4>

      <p><strong>Phone:</strong> {{ request.seller_phone }}</p>

      <p>
        <strong>Location:</strong>
        {{ request.campus_location?.display_name || "Not provided" }}
      </p>

      <p>
        <strong>Meeting:</strong>
        {{ formattedMeetingDate }}
      </p>

      <p>
        <strong>Message:</strong>
        {{ request.seller_message || "No message provided." }}
      </p>

      <div class="schedule-actions">
        <button
          type="button"
          class="release-button"
          :disabled="isSubmitting"
          @click="releaseCurrentSchedule"
        >
          Release Schedule
        </button>

        <button
          type="button"
          class="complete-button"
          :disabled="isSubmitting"
          @click="markAsDone"
        >
          Mark as Done
        </button>
      </div>
    </div>

    <!-- Completed listing -->
    <p v-else-if="isSold" class="status-description">
      This listing has been sold.
    </p>

    <!-- Other statuses -->
    <p v-else class="status-description">
      This request has been {{ request.status }}.
    </p>

    <!-- Schedule Modal -->
    <div
      v-if="showScheduleModal"
      class="modal-overlay"
      @click.self="closeScheduleModal"
    >
      <div class="schedule-modal">
        <div class="modal-header">
          <div>
            <h2>Schedule Meeting</h2>
            <p>Arrange a meeting with {{ request.requester.name }}.</p>
          </div>

          <button
            type="button"
            class="close-button"
            :disabled="acceptMutation.isPending.value"
            @click="closeScheduleModal"
          >
            &times;
          </button>
        </div>

        <form class="schedule-form" @submit.prevent="submitSchedule">
          <div class="form-group">
            <label for="seller-phone">Phone Number *</label>

            <input
              id="seller-phone"
              v-model="sellerPhone"
              type="tel"
              maxlength="30"
              placeholder="09xxxxxxxxx"
              required
            />
          </div>

          <div class="form-group">
            <label for="campus-location">Campus Location *</label>

            <select
              id="campus-location"
              v-model="campusLocationId"
              required
              :disabled="isLocationsLoading || isLocationsError"
            >
              <option value="" disabled>
                {{
                  isLocationsLoading
                    ? "Loading locations..."
                    : "Select campus location"
                }}
              </option>

              <option
                v-for="location in activeLocations"
                :key="location.id"
                :value="location.id"
              >
                {{ location.display_name }}
              </option>
            </select>

            <p v-if="isLocationsError" class="field-error">
              Failed to load campus locations.
            </p>

            <p
              v-else-if="!isLocationsLoading && activeLocations.length === 0"
              class="field-error"
            >
              No active campus locations available.
            </p>
          </div>

          <div class="form-group">
            <label for="seller-message">Message to Buyer</label>

            <textarea
              id="seller-message"
              v-model="sellerMessage"
              rows="3"
              maxlength="2000"
              placeholder="Please meet me near the main entrance."
            />
          </div>

          <div class="form-group">
            <label for="meeting-date">Meeting Date *</label>

            <input
              id="meeting-date"
              v-model="meetingDate"
              type="date"
              :min="today"
              required
            />
          </div>

          <div class="form-group">
            <label for="meeting-time">Meeting Time *</label>

            <input
              id="meeting-time"
              v-model="meetingTime"
              type="time"
              required
            />
          </div>

          <div class="modal-actions">
            <button
              type="button"
              class="cancel-modal-button"
              :disabled="acceptMutation.isPending.value"
              @click="closeScheduleModal"
            >
              Cancel
            </button>

            <button
              type="submit"
              class="submit-schedule-button"
              :disabled="
                acceptMutation.isPending.value ||
                isLocationsLoading ||
                activeLocations.length === 0
              "
            >
              {{
                acceptMutation.isPending.value
                  ? "Scheduling..."
                  : "Confirm Schedule"
              }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </article>
</template>

<style scoped>
.schedule-section {
  margin-top: 18px;
  padding: 16px;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  background: #eff6ff;
}

.schedule-section h4 {
  margin: 0 0 12px;
  color: #1e40af;
}

.schedule-section p {
  margin: 8px 0;
  color: #334155;
  font-size: 13px;
  line-height: 1.5;
  overflow-wrap: anywhere;
}

.schedule-actions {
  display: flex;
  gap: 10px;
  margin-top: 16px;
}

.schedule-actions button {
  flex: 1;
  padding: 11px;
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 700;
  cursor: pointer;
}

.release-button {
  background: #d97706;
}

.complete-button {
  background: #16a34a;
}

.schedule-actions button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modal */

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(15, 23, 42, 0.6);
  overflow-y: auto;
}

.schedule-modal {
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 24px;
  border-radius: 14px;
  background: white;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 22px;
}

.modal-header h2 {
  margin: 0 0 6px;
  color: #1e293b;
  font-size: 21px;
}

.modal-header p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.close-button {
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 28px;
  cursor: pointer;
}

.schedule-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.form-group label {
  color: #334155;
  font-size: 13px;
  font-weight: 700;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 11px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: white;
  color: #1e293b;
  font: inherit;
  font-size: 13px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px #6366f11a;
}

.form-group textarea {
  resize: vertical;
}

.field-error {
  margin: 0;
  color: #dc2626;
  font-size: 12px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 8px;
}

.modal-actions button {
  padding: 11px 16px;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.cancel-modal-button {
  background: #f1f5f9;
  color: #334155;
}

.submit-schedule-button {
  background: #16a34a;
  color: white;
}

.modal-actions button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 500px) {
  .schedule-actions {
    flex-direction: column;
  }

  .schedule-modal {
    padding: 18px;
  }
}
.seller-request-card {
  padding: 20px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #ffffff;
  transition: box-shadow 0.2s ease;
}

.seller-request-card:hover {
  box-shadow: 0 5px 18px rgba(15, 23, 42, 0.06);
}

.request-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.request-user {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.user-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #eef2ff;
  color: #4f46e5;
  font-size: 16px;
  font-weight: 700;
}

.request-user h3 {
  margin: 0 0 4px;
  color: #1e293b;
  font-size: 14px;
}

.listing-title {
  margin: 0;
  color: #94a3b8;
  font-size: 12px;
}

.status-badge {
  flex-shrink: 0;
  padding: 6px 11px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}

.status-pending {
  background: #fef3c7;
  color: #b45309;
}

.status-accepted {
  background: #dbeafe;
  color: #1d4ed8;
}

.status-rejected {
  background: #fee2e2;
  color: #dc2626;
}

.status-cancelled {
  background: #f1f5f9;
  color: #475569;
}

.status-scheduled {
  background: #ede9fe;
  color: #6d28d9;
}

.status-completed {
  background: #dcfce7;
  color: #15803d;
}

.request-message {
  margin: 18px 0;
  padding: 13px;
  border-radius: 9px;
  background: #f8fafc;
  color: #64748b;
  font-size: 13px;
  line-height: 1.6;
  overflow-wrap: anywhere;
}

.request-actions {
  display: flex;
  gap: 10px;
}

.request-actions button {
  flex: 1;
  padding: 11px;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.request-actions button:hover:not(:disabled) {
  opacity: 0.85;
}

.request-actions button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.accept-button {
  background: #16a34a;
}

.reject-button {
  background: #dc2626;
}

.status-description {
  margin: 0;
  color: #64748b;
  font-size: 12px;
}
.requests-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 22px;
}

.requests-header h2 {
  margin-bottom: 6px;
}

.requests-header p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.request-count {
  padding: 7px 12px;
  border-radius: 20px;
  background: #eef2ff;
  color: #4f46e5;
  font-size: 12px;
  font-weight: 700;
}

.request-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}
.confirmation-required {
  margin: 16px 0;
  padding: 14px;
  border: 1px solid #fcd34d;
  border-radius: 9px;
  background: #fffbeb;
}

.confirmation-required strong {
  display: block;
  margin-bottom: 6px;
  color: #92400e;
  font-size: 14px;
}

.confirmation-required p {
  margin: 0;
  color: #a16207;
  font-size: 13px;
  line-height: 1.6;
}

.accept-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}
</style>
