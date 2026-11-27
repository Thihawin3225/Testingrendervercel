import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";

import { computed, type MaybeRefOrGetter, toValue } from "vue";

import { marketplaceApi } from "../api/marketplaceApi";

import type {
  ListingRequestForm,
  UpdateListingRequestStatusPayload,
  AcceptListingRequestPayload,
} from "../types/marketplace";

// ============================================
// Query Keys
// ============================================

export const marketplaceKeys = {
  all: ["marketplace"] as const,

  // Listings
  listings: () => [...marketplaceKeys.all, "listings"] as const,

  myListings: () => [...marketplaceKeys.all, "my-listings"] as const,

  listing: (id: number) => [...marketplaceKeys.all, "listing", id] as const,

  // Listing Requests
  myRequests: () => [...marketplaceKeys.all, "my-requests"] as const,

  listingRequests: (id: number) =>
    [...marketplaceKeys.all, "listing-requests", id] as const,

  // Other
  categories: () => [...marketplaceKeys.all, "categories"] as const,

  campusLocations: () => [...marketplaceKeys.all, "campus-locations"] as const,

  savedListings: () => [...marketplaceKeys.all, "saved-listings"] as const,
};

// ============================================
// Browse All Listings
// ============================================

export function useMarketplaceListings() {
  return useQuery({
    queryKey: marketplaceKeys.listings(),

    queryFn: () => marketplaceApi.getListings(),

    staleTime: 30_000,
  });
}

// ============================================
// Listing Detail
// ============================================

export function useMarketplaceListing(id: MaybeRefOrGetter<number>) {
  return useQuery({
    queryKey: computed(() => marketplaceKeys.listing(toValue(id))),

    queryFn: () => marketplaceApi.getListing(toValue(id)),

    enabled: computed(() => toValue(id) > 0),
  });
}

// ============================================
// Seller: My Listings
// ============================================

export function useMyListings() {
  return useQuery({
    queryKey: marketplaceKeys.myListings(),

    queryFn: () => marketplaceApi.getMyListings(),

    staleTime: 30_000,
  });
}

// ============================================
// Buyer: My Requests
// ============================================

export function useMyRequests() {
  return useQuery({
    queryKey: marketplaceKeys.myRequests(),

    queryFn: () => marketplaceApi.getMyRequests(),

    staleTime: 30_000,
  });
}

// ============================================
// Seller: Get Requests for a Listing
// ============================================

export function useListingRequests(listingId: MaybeRefOrGetter<number>) {
  return useQuery({
    queryKey: computed(() =>
      marketplaceKeys.listingRequests(toValue(listingId)),
    ),

    queryFn: () => marketplaceApi.getListingRequests(toValue(listingId)),

    enabled: computed(() => toValue(listingId) > 0),
  });
}

// ============================================
// Buyer: Send Listing Request
// ============================================

export function useSendListingRequest(listingId: number) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (payload: ListingRequestForm) =>
      marketplaceApi.sendRequest(listingId, payload),

    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({
          queryKey: marketplaceKeys.listing(listingId),
        }),

        queryClient.invalidateQueries({
          queryKey: marketplaceKeys.listingRequests(listingId),
        }),

        queryClient.invalidateQueries({
          queryKey: marketplaceKeys.myRequests(),
        }),
      ]);
    },
  });
}

// ============================================
// Seller: Accept or Reject Request
// ============================================

export function useUpdateRequestStatus() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      requestId,
      status,
    }: {
      requestId: number;
      status: UpdateListingRequestStatusPayload["status"];
    }) => marketplaceApi.updateRequestStatus(requestId, { status }),

    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.all,
      });
    },
  });
}

// ============================================
// Buyer: Cancel Own Request
// ============================================

export function useCancelListingRequest() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (requestId: number) => marketplaceApi.cancelRequest(requestId),

    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.all,
      });
    },
  });
}

// ============================================
// Seller: Accept Request and Schedule
// ============================================

export function useAcceptListingRequest() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      requestId,
      payload,
    }: {
      requestId: number;
      payload: AcceptListingRequestPayload;
    }) => marketplaceApi.acceptRequest(requestId, payload),

    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.all,
      });
    },
  });
}

// ============================================
// Buyer: Confirm Updated Listing
// ============================================

export function useConfirmListingRequest() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (requestId: number) =>
      marketplaceApi.confirmListingRequest(requestId),

    onSuccess: async () => {
      // Refresh buyer requests, seller requests,
      // listing details and seller listings.
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.all,
      });
    },
  });
}

// ============================================
// Seller: Release Schedule
// ============================================

export function useReleaseSchedule() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (listingId: number) =>
      marketplaceApi.releaseSchedule(listingId),

    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.all,
      });
    },
  });
}

// ============================================
// Seller: Mark Listing as Done
// ============================================

export function useCompleteListing() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (listingId: number) =>
      marketplaceApi.completeListing(listingId),

    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.all,
      });
    },
  });
}

// ============================================
// Create Listing
// ============================================

export function useCreateListing() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (formData: FormData) => marketplaceApi.createListing(formData),

    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.all,
      });
    },
  });
}

// ============================================
// Marketplace Categories
// ============================================

export function useMarketplaceCategories() {
  return useQuery({
    queryKey: marketplaceKeys.categories(),

    queryFn: () => marketplaceApi.getCategories(),
  });
}

// ============================================
// Campus Locations
// ============================================

export function useCampusLocations() {
  return useQuery({
    queryKey: marketplaceKeys.campusLocations(),

    queryFn: () => marketplaceApi.getCampusLocations(),

    staleTime: 5 * 60 * 1000,
  });
}

// ============================================
// Saved Listings
// ============================================

export function useSavedListings() {
  return useQuery({
    queryKey: marketplaceKeys.savedListings(),

    queryFn: () => marketplaceApi.getSavedListings(),

    staleTime: 30_000,
  });
}

// ============================================
// Save Listing
// ============================================

export function useSaveListing() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (listingId: number) => marketplaceApi.saveListing(listingId),

    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.savedListings(),
      });
    },
  });
}

// ============================================
// Unsave Listing
// ============================================

export function useUnsaveListing() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (listingId: number) => marketplaceApi.unsaveListing(listingId),

    onSuccess: async () => {
      await queryClient.invalidateQueries({
        queryKey: marketplaceKeys.savedListings(),
      });
    },
  });
}
export function useUpdateListing() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      listingId,
      formData,
    }: {
      listingId: number;
      formData: FormData;
    }) => marketplaceApi.updateListing(listingId, formData),

    onSuccess: async (_data, variables) => {
      await Promise.all([
        queryClient.invalidateQueries({
          queryKey: marketplaceKeys.all,
        }),
        queryClient.invalidateQueries({
          queryKey: marketplaceKeys.listing(variables.listingId),
        }),
      ]);
    },
  });
}
