<?php

namespace App\Policies;

use App\Models\MarketplaceCategory;
use App\Models\User;

class MarketplaceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MarketplaceCategory $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, MarketplaceCategory $category): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, MarketplaceCategory $category): bool
    {
        return $user->role === 'admin'
            && ! $category->listings()->exists();
    }
}
