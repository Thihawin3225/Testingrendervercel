<?php

namespace App\Repositories\Eloquent\Marketplace;


use App\Models\MarketplaceCategory;
use App\Repositories\Contracts\Marketplace\MarketplaceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentMarketplaceCategoryRepository implements MarketplaceCategoryRepositoryInterface
{
    public function getAll(): Collection
    {
        return MarketplaceCategory::withCount('listings')
            ->latest()
            ->get();
    }

    public function findById(int $id): ?MarketplaceCategory
    {
        return MarketplaceCategory::withCount('listings')
            ->find($id);
    }

    public function create(array $data): MarketplaceCategory
    {
        return MarketplaceCategory::create($data);
    }

    public function update(
        MarketplaceCategory $category,
        array $data
    ): MarketplaceCategory {
        $category->update($data);

        return $category->refresh();
    }

    public function delete(MarketplaceCategory $category): bool
    {
        return (bool) $category->delete();
    }
}
