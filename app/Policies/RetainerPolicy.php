<?php

namespace App\Policies;

use App\Models\Retainer;
use App\Models\User;

class RetainerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Retainer $retainer): bool
    {
        return $user->retainers()->where('id', $retainer->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Retainer $retainer): bool
    {
        return $user->retainers()->where('id', $retainer->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Retainer $retainer): bool
    {
        return $user->retainers()->where('id', $retainer->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Retainer $retainer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Retainer $retainer): bool
    {
        return false;
    }
}
