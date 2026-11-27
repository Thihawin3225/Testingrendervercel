<?php

namespace App\Http\Controllers\Api\V1\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\StoreListingRequestRequest;
use App\Http\Requests\Marketplace\UpdateListingRequestStatusRequest;
use App\Http\Resources\Marketplace\ListingRequestResource;
use App\Models\ListingRequest;
use App\Models\MarketplaceListing;
use App\Services\Marketplace\ListingRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\Marketplace\AcceptListingRequestRequest;

class ListingRequestController extends Controller
{
    public function __construct(
        protected ListingRequestService $listingRequestService
    ) {}

    /**
     * Buyer sends a request.
     */
    public function store(
        StoreListingRequestRequest $request,
        MarketplaceListing $listing
    ): ListingRequestResource {
        $listingRequest = $this->listingRequestService->createRequest(
            $request->user(),
            $listing,
            $request->validated()
        );

        return new ListingRequestResource($listingRequest);
    }

    public function accept(
        AcceptListingRequestRequest $request,
        ListingRequest $listingRequest
    ): ListingRequestResource {
        $acceptedRequest = $this->listingRequestService->acceptRequest(
            $request->user(),
            $listingRequest,
            $request->validated()
        );

        return new ListingRequestResource($acceptedRequest);
    }
    public function releaseSchedule(
        MarketplaceListing $listing
    ): JsonResponse {
        $releasedRequest = $this->listingRequestService->releaseSchedule(
            request()->user(),
            $listing
        );

        return response()->json([
            'message' => 'Schedule released successfully. The listing is available for new requests.',
            'data' => new ListingRequestResource($releasedRequest),
        ]);
    }
    public function complete(
        MarketplaceListing $listing
    ): JsonResponse {
        $completedListing = $this->listingRequestService->completeListing(
            request()->user(),
            $listing
        );

        return response()->json([
            'message' => 'Listing marked as completed successfully.',
            'data' => [
                'id' => $completedListing->id,
                'title' => $completedListing->title,
                'status' => $completedListing->status,
            ],
        ]);
    }

    /**
     * Buyer views their own requests.
     */
    public function myRequests(): AnonymousResourceCollection
    {
        $requests = $this->listingRequestService->getMyRequests(
            request()->user()
        );

        return ListingRequestResource::collection($requests);
    }

    /**
     * Seller views requests for their listing.
     */
    public function index(
        MarketplaceListing $listing
    ): AnonymousResourceCollection {
        $requests = $this->listingRequestService->getListingRequests(
            request()->user(),
            $listing
        );

        return ListingRequestResource::collection($requests);
    }

    /**
     * Seller accepts or rejects a request.
     */
    public function updateStatus(
        UpdateListingRequestStatusRequest $request,
        ListingRequest $listingRequest
    ): ListingRequestResource {
        $updatedRequest = $this->listingRequestService->updateRequestStatus(
            $request->user(),
            $listingRequest,
            $request->validated('status')
        );

        return new ListingRequestResource($updatedRequest);
    }

    /**
     * Buyer cancels their pending request.
     */
    public function cancel(
        ListingRequest $listingRequest
    ): JsonResponse {
        $cancelledRequest = $this->listingRequestService->cancelRequest(
            request()->user(),
            $listingRequest
        );

        return response()->json([
            'message' => 'Listing request cancelled successfully.',
            'data' => new ListingRequestResource($cancelledRequest),
        ]);
    }


    /**
     * Buyer confirms an updated listing.
     */
    public function confirm(
        ListingRequest $listingRequest
    ): ListingRequestResource {
        $confirmedRequest = $this->listingRequestService->confirmRequest(
            request()->user(),
            $listingRequest
        );

        return new ListingRequestResource($confirmedRequest);
    }
}
