<?php

namespace App\Policies;


use App\Models\MarketplaceListing;
use App\Models\User;

class MarketplaceListingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status === 'active';
    }

    public function view(User $user, MarketplaceListing $listing): bool
    {
        return $user->status === 'active'
            && in_array($user->role, ['admin', 'student'], true);
    }

    public function create(User $user): bool
    {
        return $user->status === 'active'
            && in_array($user->role, ['admin', 'student'], true);
    }

    public function update(User $user, MarketplaceListing $listing): bool
    {
        if ($user->status !== 'active') {
            return false;
        }

        return $user->role === 'admin'
            || (
                $user->role === 'student'
                && (int) $listing->user_id === (int) $user->id
            );
    }

    public function delete(User $user, MarketplaceListing $listing): bool
    {
        if ($user->status !== 'active') {
            return false;
        }

        return $user->role === 'admin'
            || (
                $user->role === 'student'
                && (int) $listing->user_id === (int) $user->id
            );
    }
}
