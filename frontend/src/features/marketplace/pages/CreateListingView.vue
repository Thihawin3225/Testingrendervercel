<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from "vue";
import { useRouter } from "vue-router";
import {
  useMarketplaceCategories,
  useCreateListing,
} from "../composables/useMarketplace";

const router = useRouter();

// Fetch categories
const {
  data: categoriesResponse,
  isLoading: categoriesLoading,
  isError: categoriesError,
} = useMarketplaceCategories();

const categories = computed(() => categoriesResponse.value?.data ?? []);

// Create listing mutation
const createMutation = useCreateListing();

// Form fields
const title = ref("");
const description = ref("");
const categoryId = ref("");
const listingType = ref<"sale" | "donation" | "wanted">("sale");
const price = ref<number | null>(null);
const condition = ref("");

const selectedImages = ref<File[]>([]);
const imagePreviews = ref<string[]>([]);

const errorMessage = ref("");
const successMessage = ref("");

const isSubmitting = computed(() => createMutation.isPending.value);

// Image selection
function handleImageChange(event: Event) {
  const target = event.target as HTMLInputElement;
  const files = Array.from(target.files ?? []);

  errorMessage.value = "";

  if (selectedImages.value.length + files.length > 5) {
    errorMessage.value = "You can upload a maximum of 5 images.";
    target.value = "";
    return;
  }

  const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

  for (const file of files) {
    if (!allowedTypes.includes(file.type)) {
      errorMessage.value = "Only JPG, JPEG, PNG and WEBP images are allowed.";

      target.value = "";
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      errorMessage.value = "Each image must be smaller than 5 MB.";

      target.value = "";
      return;
    }
  }

  // Add new images without removing previous images
  selectedImages.value.push(...files);

  // Generate previews for newly selected images
  const newPreviews = files.map((file) => URL.createObjectURL(file));

  imagePreviews.value.push(...newPreviews);

  // Allow selecting the same file again if needed
  target.value = "";
}

function removeImage(index: number) {
  URL.revokeObjectURL(imagePreviews.value[index] || "");

  selectedImages.value.splice(index, 1);
  imagePreviews.value.splice(index, 1);
}

// Change listing type
function handleListingTypeChange() {
  if (listingType.value !== "sale") {
    price.value = null;
  }
}

// Submit form
function submitListing() {
  errorMessage.value = "";
  successMessage.value = "";

  if (!categoryId.value) {
    errorMessage.value = "Please select a category.";
    return;
  }

  if (!title.value.trim()) {
    errorMessage.value = "Please enter the item title.";
    return;
  }

  if (listingType.value === "sale") {
    if (price.value === null || price.value < 0) {
      errorMessage.value = "Please enter a valid price for sale listings.";
      return;
    }
  }

  if (selectedImages.value.length === 0) {
    errorMessage.value = "Please upload at least one image.";
    return;
  }

  const formData = new FormData();

  formData.append("category_id", categoryId.value);
  formData.append("title", title.value.trim());
  formData.append("listing_type", listingType.value);

  if (description.value.trim()) {
    formData.append("description", description.value.trim());
  }

  if (listingType.value === "sale") {
    formData.append("price", String(price.value));
  }

  if (condition.value) {
    formData.append("condition", condition.value);
  }

  selectedImages.value.forEach((image) => {
    formData.append("images[]", image);
  });

  createMutation.mutate(formData, {
    onSuccess: () => {
      successMessage.value = "Listing created successfully!";

      imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));

      // Return to Marketplace after successful creation.
      router.push("/marketplace");
    },

    onError: (error: any) => {
      const errors = error.response?.data?.errors;

      if (errors) {
        errorMessage.value = Object.values(errors).flat().join(" ");
      } else {
        errorMessage.value =
          error.response?.data?.message ??
          "Failed to create listing. Please try again.";
      }
    },
  });
}

onBeforeUnmount(() => {
  imagePreviews.value.forEach((url) => URL.revokeObjectURL(url));
});
</script>

<template>
  <main class="create-listing-page">
    <header class="page-header">
      <div>
        <span class="eyebrow">CAMPUS BRIDGE</span>
        <h1>Create New Listing</h1>
        <p>Share your items with other students on campus.</p>
      </div>
    </header>

    <form class="listing-form" @submit.prevent="submitListing">
      <!-- Error Message -->
      <div v-if="errorMessage" class="alert error-alert">
        {{ errorMessage }}
      </div>

      <div v-if="successMessage" class="alert success-alert">
        {{ successMessage }}
      </div>

      <!-- Title -->
      <div class="form-group">
        <label for="title">Item Title *</label>

        <input
          id="title"
          v-model="title"
          type="text"
          maxlength="180"
          placeholder="e.g. Scientific Calculator"
          required
        />
      </div>

      <!-- Description -->
      <div class="form-group">
        <label for="description">Description</label>

        <textarea
          id="description"
          v-model="description"
          rows="4"
          placeholder="Describe your item..."
        />
      </div>

      <!-- Category -->
      <div class="form-group">
        <label for="category">Category *</label>

        <select
          id="category"
          v-model="categoryId"
          :disabled="categoriesLoading"
          required
        >
          <option value="" disabled>
            {{
              categoriesLoading ? "Loading categories..." : "Select category"
            }}
          </option>

          <option
            v-for="category in categories"
            :key="category.id"
            :value="String(category.id)"
          >
            {{ category.name }}
          </option>
        </select>

        <small v-if="categoriesError" class="field-error">
          Unable to load categories. Please refresh the page.
        </small>
      </div>

      <!-- Listing Type -->
      <div class="form-group">
        <label for="listingType">Listing Type *</label>

        <select
          id="listingType"
          v-model="listingType"
          required
          @change="handleListingTypeChange"
        >
          <option value="sale">For Sale</option>
          <option value="donation">Donation</option>
          <option value="wanted">Wanted</option>
        </select>
      </div>

      <!-- Price -->
      <div v-if="listingType === 'sale'" class="form-group">
        <label for="price">Price (MMK) *</label>

        <input
          id="price"
          v-model.number="price"
          type="number"
          min="0"
          step="0.01"
          placeholder="Enter price"
          required
        />
      </div>

      <!-- Condition -->
      <div class="form-group">
        <label for="condition">Condition</label>

        <select id="condition" v-model="condition">
          <option value="">Select condition</option>
          <option value="new">New</option>
          <option value="like_new">Like New</option>
          <option value="good">Good</option>
          <option value="fair">Fair</option>
          <option value="poor">Poor</option>
        </select>
      </div>

      <!-- Images -->
      <div class="form-group">
        <label for="images">Item Images *</label>

        <label class="upload-area" for="images">
          <span class="upload-icon">＋</span>
          <strong>Click to upload images</strong>
          <span>JPG, PNG, WEBP · Maximum 5 images · 5 MB each</span>
        </label>

        <input
          id="images"
          class="file-input"
          type="file"
          accept=".jpg,.jpeg,.png,.webp"
          multiple
          @change="handleImageChange"
        />

        <div v-if="imagePreviews.length" class="image-grid">
          <div
            v-for="(preview, index) in imagePreviews"
            :key="preview"
            class="image-preview"
          >
            <img :src="preview" alt="Selected item image" />

            <button
              type="button"
              class="remove-image"
              aria-label="Remove image"
              @click="removeImage(index)"
            >
              ×
            </button>
          </div>
        </div>

        <small>{{ selectedImages.length }} / 5 images selected</small>
      </div>

      <!-- Submit -->
      <div class="form-actions">
        <button type="button" class="cancel-button" @click="router.back()">
          Cancel
        </button>

        <button
          type="submit"
          class="submit-button"
          :disabled="isSubmitting || categoriesLoading"
        >
          {{ isSubmitting ? "Creating..." : "Create Listing" }}
        </button>
      </div>
    </form>
  </main>
</template>

<style scoped>
.create-listing-page {
  max-width: 850px;
  margin: 0 auto;
  padding: 30px;
}

.page-header {
  margin-bottom: 25px;
}

.eyebrow {
  color: #6366f1;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 2px;
}

.page-header h1 {
  margin: 9px 0;
  color: #1e293b;
  font-size: 28px;
  font-weight: 800;
}

.page-header p {
  color: #64748b;
  font-size: 14px;
}

.listing-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 28px;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  background: #fff;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 9px;
}

.form-group > label:not(.upload-area) {
  color: #334155;
  font-size: 13px;
  font-weight: 700;
}

.form-group input:not([type="file"]),
.form-group select,
.form-group textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 12px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 9px;
  outline: none;
  background: #f8fafc;
  color: #1e293b;
  font-size: 13px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #818cf8;
  background: #fff;
}

.form-group textarea {
  resize: vertical;
}

.upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 28px;
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  background: #f8fafc;
  cursor: pointer;
  text-align: center;
}

.upload-area:hover {
  border-color: #818cf8;
  background: #eef2ff;
}

.upload-icon {
  color: #6366f1;
  font-size: 30px;
}

.upload-area strong {
  color: #334155;
  font-size: 13px;
}

.upload-area span:last-child {
  color: #94a3b8;
  font-size: 11px;
}

.file-input {
  display: none;
}

.image-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 12px;
}

.image-preview {
  position: relative;
  aspect-ratio: 1;
}

.image-preview img {
  width: 100%;
  height: 100%;
  border-radius: 9px;
  object-fit: cover;
}

.remove-image {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 25px;
  height: 25px;
  border: none;
  border-radius: 50%;
  background: #dc2626;
  color: #fff;
  font-size: 18px;
  cursor: pointer;
}

.form-group small {
  color: #94a3b8;
  font-size: 11px;
}

.field-error {
  color: #dc2626 !important;
}

.alert {
  padding: 12px 14px;
  border-radius: 9px;
  font-size: 13px;
}

.error-alert {
  background: #fef2f2;
  color: #dc2626;
}

.success-alert {
  background: #f0fdf4;
  color: #15803d;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 12px;
  border-top: 1px solid #e2e8f0;
}

.cancel-button,
.submit-button {
  padding: 12px 20px;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.cancel-button {
  border: 1px solid #e2e8f0;
  background: #fff;
  color: #64748b;
}

.submit-button {
  border: none;
  background: #4f46e5;
  color: #fff;
}

.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 600px) {
  .create-listing-page {
    padding: 18px;
  }

  .listing-form {
    padding: 18px;
  }

  .image-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
