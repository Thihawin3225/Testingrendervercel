<script setup lang="ts">
import { ref } from "vue";
import { useSendListingRequest } from "../composables/useMarketplace";

const props = defineProps<{
  listingId: number;
}>();

const message = ref("");

const { mutate, isPending, isSuccess, isError, error, reset } =
  useSendListingRequest(props.listingId);

function submitRequest() {
  reset();

  mutate({
    message: message.value.trim(),
  });
}
</script>

<template>
  <div class="request-card">
    <div class="request-header">
      <div class="request-icon">✉</div>
      <div>
        <h3>Interested in this item?</h3>
        <p>Send a request to the seller.</p>
      </div>
    </div>

    <form @submit.prevent="submitRequest">
      <label for="request-message"> Message <span>(Optional)</span> </label>

      <textarea
        id="request-message"
        v-model="message"
        maxlength="1000"
        placeholder="Hello, I am interested in this item..."
        rows="4"
        :disabled="isPending || isSuccess"
      />

      <div class="character-count">{{ message.length }}/1000</div>

      <button type="submit" :disabled="isPending || isSuccess">
        {{ isPending ? "Sending..." : "Send Request →" }}
      </button>

      <p v-if="isSuccess" class="success-message">
        ✓ Your request has been sent successfully!
      </p>

      <p v-if="isError" class="error-message">
        {{ error?.message || "Unable to send request." }}
      </p>
    </form>
  </div>
</template>

<style scoped>
.request-card {
  padding: 24px;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  background: white;
}

.request-header {
  display: flex;
  align-items: center;
  gap: 13px;
  margin-bottom: 22px;
}

.request-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 45px;
  height: 45px;
  border-radius: 12px;
  background: #eef2ff;
  color: #4f46e5;
  font-size: 22px;
}

.request-header h3 {
  margin: 0 0 5px;
  color: #1e293b;
  font-size: 17px;
}

.request-header p {
  margin: 0;
  color: #94a3b8;
  font-size: 12px;
}

.request-card label {
  display: block;
  margin-bottom: 9px;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
}

.request-card label span {
  color: #94a3b8;
  font-weight: 400;
}

.request-card textarea {
  box-sizing: border-box;
  width: 100%;
  padding: 13px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  outline: none;
  resize: vertical;
  font: inherit;
  font-size: 13px;
}

.request-card textarea:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px #eef2ff;
}

.character-count {
  margin-top: 6px;
  text-align: right;
  color: #94a3b8;
  font-size: 11px;
}

.request-card button {
  width: 100%;
  margin-top: 17px;
  padding: 13px;
  border: none;
  border-radius: 10px;
  background: #4f46e5;
  color: white;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.request-card button:hover {
  background: #4338ca;
}

.request-card button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-message {
  color: #16a34a;
  font-size: 13px;
}

.error-message {
  color: #dc2626;
  font-size: 13px;
}
</style>
