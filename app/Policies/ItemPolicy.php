<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Filament\Facades\Filament;

class ItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Item $item): bool
    {
        // Non-super-admins cannot view soft-deleted items
        if ($item->trashed() && ! $user->can('super-admin')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can register their own item via the
        // member-facing dashboard panel; the admin panel's item resource
        // still requires manage-items (staff creating/booking in on behalf
        // of anyone).
        if (Filament::getCurrentPanel()?->getId() === 'dashboard') {
            return true;
        }

        return $user->can('manage-items');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Item $item): bool
    {
        return $item->user_id === $user->id || $user->can('manage-items');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Item $item): bool
    {
        return $item->user_id === $user->id || $user->can('manage-items');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Item $item): bool
    {
        // Only super-admins can restore soft-deleted items
        return $user->can('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Item $item): bool
    {
        // Only super-admins can force delete items
        return $user->can('super-admin');
    }
}
