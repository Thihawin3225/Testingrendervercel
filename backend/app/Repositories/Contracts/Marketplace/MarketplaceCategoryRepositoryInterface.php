<?php

namespace App\Repositories\Contracts\Marketplace;

use App\Models\MarketplaceCategory;
use Illuminate\Database\Eloquent\Collection;

interface MarketplaceCategoryRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?MarketplaceCategory;

    public function create(array $data): MarketplaceCategory;

    public function update(
        MarketplaceCategory $category,
        array $data
    ): MarketplaceCategory;

    public function delete(MarketplaceCategory $category): bool;
}
