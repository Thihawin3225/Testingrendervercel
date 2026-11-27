export interface MarketplaceUser {
  id: number;
  name: string;
}

export interface MarketplaceCategory {
  id: number;
  name: string;
}

export interface MarketplaceImage {
  id: number;
  url: string;
  sort_order: number;
}

export type ListingRequestStatus =
  | "pending"
  | "accepted"
  | "rejected"
  | "cancelled";

export interface CampusLocation {
  id: number;
  name: string;
  building: string | null;
  display_name: string | null;
  is_active?: boolean | number;
}

export interface ListingRequestListing {
  id: number;
  title: string;
  price: string | null;
  listing_type: "sale" | "donation" | "wanted";
  status: "pending" | "active" | "reserved" | "sold" | "closed" | "rejected";
}

export interface ListingRequestRequester {
  id: number;
  name: string;
}

export interface ListingRequest {
  id: number;
  listing: ListingRequestListing;
  requester: ListingRequestRequester;
  message: string | null;
  status: ListingRequestStatus;

  // Buyer confirmation
  confirmation: boolean;

  // Seller schedule information
  seller_phone: string | null;
  seller_message: string | null;
  meeting_at: string | null;
  campus_location: CampusLocation | null;

  created_at: string;
  updated_at: string;
}

export interface ListingRequestResponse {
  data: ListingRequest;
}

export interface ListingRequestsResponse {
  data: ListingRequest[];
  links?: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta?: PaginationMeta;
}

export interface ListingRequestForm {
  message?: string;
}

export interface CreateListingRequestPayload {
  message?: string;
}

export interface UpdateListingRequestStatusPayload {
  status: "accepted" | "rejected";
}

export interface AcceptListingRequestPayload {
  seller_phone: string;
  campus_location_id: number;
  seller_message: string;
  meeting_at: string;
}

export interface ScheduleDetails {
  seller_phone: string | null;
  seller_message: string | null;
  meeting_at: string | null;
  campus_location: CampusLocation | null;
}

export interface MarketplaceListing {
  id: number;
  title: string;
  description: string;
  listing_type: "sale" | "donation" | "wanted";
  price: string | null;
  condition: string;
  status: string;
  user: MarketplaceUser;
  category: MarketplaceCategory;
  images: MarketplaceImage[];
  created_at: string;
  updated_at: string;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface MarketplaceListingsResponse {
  data: MarketplaceListing[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: PaginationMeta;
}

export interface CampusLocationsResponse {
  data: CampusLocation[];
}

export type CreateListingPayload = {
  category_id: number;
  title: string;
  description?: string;
  listing_type: "sale" | "donation" | "wanted";
  price?: number | null;
  condition?: "new" | "like_new" | "good" | "fair" | "poor" | null;
  images: File[];
};
