<?php

namespace App\Http\Controllers\Api\V1\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\StoreMarketplaceCategoryRequest;
use App\Http\Requests\Marketplace\UpdateMarketplaceCategoryRequest;
use App\Http\Resources\Marketplace\MarketplaceCategoryResource;
use App\Models\MarketplaceCategory;
use App\Services\Marketplace\MarketplaceCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\Support\Facades\Gate;

class MarketplaceCategoryController extends RoutingController
{
    public function __construct(
        protected MarketplaceCategoryService $categoryService
    ) {}

    public function index()
    {
        Gate::authorize('viewAny', MarketplaceCategory::class);

        return MarketplaceCategoryResource::collection(
            $this->categoryService->getAll()
        );
    }

    public function store(
        StoreMarketplaceCategoryRequest $request
    ): JsonResponse {
        Gate::authorize('create', MarketplaceCategory::class);

        $category = $this->categoryService->create(
            $request->validated()
        );

        return (new MarketplaceCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $category = $this->categoryService->getById($id);

        abort_if(! $category, 404, 'Category not found.');

        Gate::authorize('view', $category);

        return new MarketplaceCategoryResource($category);
    }

    public function update(
        UpdateMarketplaceCategoryRequest $request,
        int $id
    ) {
        $category = $this->categoryService->getById($id);

        abort_if(! $category, 404, 'Category not found.');

        Gate::authorize('update', $category);

        $updatedCategory = $this->categoryService->update(
            $category,
            $request->validated()
        );

        return new MarketplaceCategoryResource($updatedCategory);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = $this->categoryService->getById($id);

        abort_if(! $category, 404, 'Category not found.');

        Gate::authorize('delete', $category);

        $this->categoryService->delete($category);

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
