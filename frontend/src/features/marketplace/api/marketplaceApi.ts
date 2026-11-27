import { http } from "../../../services/http";

import type {
  MarketplaceListing,
  MarketplaceListingsResponse,
  ListingRequest,
  ListingRequestForm,
  ListingRequestResponse,
  ListingRequestsResponse,
  UpdateListingRequestStatusPayload,
} from "../types/marketplace";
import type {
  CampusLocationsResponse,
  AcceptListingRequestPayload,
} from "../types/marketplace";

export const marketplaceApi = {
  // ============================================
  // Browse all listings
  // GET /marketplace/listings
  // ============================================

  async getListings(page = 1): Promise<MarketplaceListingsResponse> {
    const response = await http.get<MarketplaceListingsResponse>(
      "/marketplace/listings",
      {
        params: { page },
      },
    );

    return response.data;
  },

  // ============================================
  // View listing detail
  // GET /marketplace/listings/{id}
  // ============================================

  async getListing(id: number): Promise<MarketplaceListing> {
    const response = await http.get<{ data: MarketplaceListing }>(
      `/marketplace/listings/${id}`,
    );

    return response.data.data;
  },

  // ============================================
  // Send listing request
  // POST /marketplace/listings/{id}/requests
  // ============================================

  async sendRequest(
    listingId: number,
    payload: ListingRequestForm,
  ): Promise<ListingRequest> {
    const response = await http.post<ListingRequestResponse>(
      `/marketplace/listings/${listingId}/requests`,
      payload,
    );

    return response.data.data;
  },

  // ============================================
  // View requests for a listing (Seller)
  // GET /marketplace/listings/{id}/requests
  // ============================================

  async getListingRequests(
    listingId: number,
  ): Promise<ListingRequestsResponse> {
    const response = await http.get<ListingRequestsResponse>(
      `/marketplace/listings/${listingId}/requests`,
    );

    return response.data;
  },

  // ============================================
  // Accept or Reject request (Seller)
  // PATCH /marketplace/requests/{id}/status
  // ============================================

  async updateRequestStatus(
    requestId: number,
    payload: UpdateListingRequestStatusPayload,
  ): Promise<ListingRequest> {
    const response = await http.patch<ListingRequestResponse>(
      `/marketplace/requests/${requestId}/status`,
      payload,
    );

    return response.data.data;
  },

  // ============================================
  // Cancel own request (Student)
  // PATCH /marketplace/requests/{id}/cancel
  // ============================================

  async cancelRequest(requestId: number) {
    const response = await http.patch<{
      message: string;
      data: ListingRequest;
    }>(`/marketplace/requests/${requestId}/cancel`);

    return response.data;
  },

  // ============================================
  // View my requests (Student)
  // GET /marketplace/my-requests
  // ============================================

  async getMyRequests(): Promise<ListingRequestsResponse> {
    const response = await http.get<ListingRequestsResponse>(
      "/marketplace/my-requests",
    );

    return response.data;
  },
  async getMyListings(): Promise<MarketplaceListingsResponse> {
    const response = await http.get<MarketplaceListingsResponse>(
      "/marketplace/my-listings",
    );

    return response.data;
  },
  // Get active campus locations
  async getCampusLocations(): Promise<CampusLocationsResponse> {
    const response = await http.get<CampusLocationsResponse>(
      "/campus/locations/active",
    );

    return response.data;
  },

  // Accept request and schedule meeting
  async acceptRequest(
    requestId: number,
    payload: AcceptListingRequestPayload,
  ): Promise<ListingRequest> {
    const response = await http.post<ListingRequestResponse>(
      `/marketplace/requests/${requestId}/accept`,
      payload,
    );

    return response.data.data;
  },

  // Release scheduled meeting
  async releaseSchedule(listingId: number) {
    const response = await http.patch<ListingRequestResponse>(
      `/marketplace/listings/${listingId}/release-schedule`,
    );

    return response.data;
  },

  // Mark listing as done
  async completeListing(listingId: number) {
    const response = await http.patch<{
      message: string;
      data: {
        id: number;
        title: string;
        status: string;
      };
    }>(`/marketplace/listings/${listingId}/complete`);

    return response.data;
  },
  // Get marketplace categories
  async getCategories() {
    const response = await http.get("/marketplace/categories");
    return response.data;
  },

  // Create marketplace listing
  async createListing(formData: FormData) {
    const response = await http.post("/marketplace/listings", formData);

    return response.data;
  },
  // ============================================
  // Save Marketplace Listing
  // POST /marketplace/listings/{id}/save
  // ============================================

  async saveListing(listingId: number): Promise<{ message: string }> {
    const response = await http.post<{ message: string }>(
      `/marketplace/listings/${listingId}/save`,
    );

    return response.data;
  },

  // ============================================
  // Unsave Marketplace Listing
  // DELETE /marketplace/listings/{id}/save
  // ============================================

  async unsaveListing(listingId: number): Promise<{ message: string }> {
    const response = await http.delete<{ message: string }>(
      `/marketplace/listings/${listingId}/save`,
    );

    return response.data;
  },

  // ============================================
  // Get Saved Listings
  // GET /marketplace/saved-listings
  // ============================================

  async getSavedListings(page = 1): Promise<MarketplaceListingsResponse> {
    const response = await http.get<MarketplaceListingsResponse>(
      "/marketplace/saved-listings",
      {
        params: { page },
      },
    );

    return response.data;
  },
  // ============================================
  // Buyer: Confirm Updated Listing
  // PATCH /marketplace/requests/{id}/confirm
  // ============================================

  async confirmListingRequest(requestId: number): Promise<ListingRequest> {
    const response = await http.patch<ListingRequestResponse>(
      `/marketplace/requests/${requestId}/confirm`,
    );

    return response.data.data;
  },
  // ============================================
  // Update Marketplace Listing (Seller)
  // PUT /marketplace/listings/{id}
  // ============================================

  async updateListing(listingId: number, formData: FormData) {
    const response = await http.post(
      `/marketplace/listings/${listingId}`,
      formData,
      {
        params: {
          _method: "PUT",
        },
      },
    );

    return response.data;
  },
};
