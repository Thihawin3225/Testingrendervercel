<?php

namespace App\Http\Controllers\Api\V1\Marketplace;


use App\Http\Controllers\Controller;
use App\Http\Resources\Marketplace\MarketplaceListingResource;
use App\Models\MarketplaceListing;
use App\Services\Marketplace\MarketplaceListingSaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SavedListingController extends Controller
{
    public function __construct(
        private readonly MarketplaceListingSaveService $saveService
    ) {}

    /**
     * Save a listing.
     */
    public function store(
        Request $request,
        MarketplaceListing $listing
    ): JsonResponse {
        $saved = $this->saveService->save(
            $request->user(),
            $listing
        );

        if (! $saved) {
            return response()->json([
                'message' => 'Listing is already saved.',
            ], 200);
        }

        return response()->json([
            'message' => 'Listing saved successfully.',
        ], 201);
    }

    /**
     * Unsave a listing.
     */
    public function destroy(
        Request $request,
        MarketplaceListing $listing
    ): JsonResponse {
        $deleted = $this->saveService->unsave(
            $request->user(),
            $listing
        );

        if (! $deleted) {
            return response()->json([
                'message' => 'Listing was not saved.',
            ], 404);
        }

        return response()->json([
            'message' => 'Listing removed from saved listings.',
        ]);
    }

    /**
     * Get authenticated user's saved listings.
     */
    public function index(
        Request $request
    ): AnonymousResourceCollection {
        $listings = $this->saveService->getSavedListings(
            $request->user()
        );

        return MarketplaceListingResource::collection($listings);
    }
}
