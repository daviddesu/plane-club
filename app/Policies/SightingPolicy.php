<?php

namespace App\Policies;

use App\Models\Sighting;
use App\Models\User;

class SightingPolicy
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
    public function view(User $user, Sighting $sighting): bool
    {
        return $sighting->user()->is($user);
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
    public function update(User $user, Sighting $sighting): bool
    {
        return $sighting->user()->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sighting $sighting): bool
    {
        return $sighting->user()->is($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sighting $sighting): bool
    {
        return $sighting->user()->is($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sighting $sighting): bool
    {
        return $sighting->user()->is($user);
    }
}
