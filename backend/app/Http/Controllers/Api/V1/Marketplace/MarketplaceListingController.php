<?php

namespace App\Http\Controllers\Api\V1\Marketplace;


use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\StoreMarketplaceListingRequest;
use App\Http\Requests\Marketplace\UpdateMarketplaceListingRequest;
use App\Http\Resources\Marketplace\MarketplaceListingResource;
use App\Models\MarketplaceListing;
use App\Services\Marketplace\MarketplaceListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MarketplaceListingController extends Controller
{
    public function __construct(
        protected MarketplaceListingService $listingService
    ) {}

    /**
     * Display listings.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', MarketplaceListing::class);

        $filters = $request->only([
            'category_id',
            'listing_type',
            'status',
            'search',
            'user_id',
        ]);

        // Students can only browse pending listings.
        if ($request->user()->role !== 'admin') {
            $filters['status'] = 'pending';
        }

        $listings = $this->listingService->getAll($filters);

        return MarketplaceListingResource::collection($listings);
    }

    /**
     * Create listing with multiple images.
     */
    public function store(StoreMarketplaceListingRequest $request)
    {
        $data = $request->validated();

        $data['images'] = $request->file('images', []);

        $listing = $this->listingService->create(
            $data,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Listing created successfully.',
            'data' => new MarketplaceListingResource($listing),
        ], 201);
    }

    /**
     * Display one listing.
     */
    public function show(int $id)
    {
        $listing = $this->listingService->getById($id);

        abort_if(! $listing, 404, 'Listing not found.');

        Gate::authorize('view', $listing);

        return new MarketplaceListingResource($listing);
    }

    /**
     * Display authenticated user's listings.
     */
    public function myListings(Request $request)
    {
        $filters = [
            'user_id' => $request->user()->id,
        ];

        $listings = $this->listingService->getAll($filters);

        return MarketplaceListingResource::collection($listings);
    }

    /**
     * Update listing and optionally replace images.
     */
    public function update(
        UpdateMarketplaceListingRequest $request,
        int $id
    ) {
        $listing = $this->listingService->getById($id);

        abort_if(! $listing, 404, 'Listing not found.');

        Gate::authorize('update', $listing);

        abort_if(! $listing, 404, 'Listing not found.');

        // Check listing ownership through policy.
        Gate::authorize('update', $listing);

        $data = $request->validated();

        // Only replace images when new images are uploaded.
        if ($request->hasFile('images')) {
            $data['images'] = $request->file('images');
        }

        $updatedListing = $this->listingService->update(
            $listing,
            $data
        );

        return response()->json([
            'message' => 'Listing updated successfully.',
            'data' => new MarketplaceListingResource($updatedListing),
        ]);
    }

    /**
     * Delete listing and its images.
     */
    public function destroy(int $id): JsonResponse
    {
        $listing = $this->listingService->getById($id);

        abort_if(! $listing, 404, 'Listing not found.');

        Gate::authorize('delete', $listing);

        $this->listingService->delete($listing);

        return response()->json([
            'message' => 'Listing deleted successfully.',
        ]);
    }
}
