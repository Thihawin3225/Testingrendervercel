<?php

namespace App\Services\Marketplace;

use App\Models\MarketplaceCategory;
use App\Repositories\Contracts\Marketplace\MarketplaceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MarketplaceCategoryService
{
    public function __construct(
        protected MarketplaceCategoryRepositoryInterface $categoryRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    public function getById(int $id): ?MarketplaceCategory
    {
        return $this->categoryRepository->findById($id);
    }

    public function create(array $data): MarketplaceCategory
    {
        return $this->categoryRepository->create($data);
    }

    public function update(
        MarketplaceCategory $category,
        array $data
    ): MarketplaceCategory {
        return $this->categoryRepository->update(
            $category,
            $data
        );
    }

    public function delete(MarketplaceCategory $category): bool
    {
        if ($category->listings()->exists()) {
            throw new \RuntimeException(
                'Cannot delete category because listings are using it.'
            );
        }

        return $this->categoryRepository->delete($category);
    }
}
