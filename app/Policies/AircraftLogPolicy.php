<?php

namespace App\Policies;

use App\Models\AircraftLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AircraftLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AircraftLog $aircraftLog): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AircraftLog $aircraftLog): bool
    {
        return $aircraftLog->user()->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AircraftLog $aircraftLog): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AircraftLog $aircraftLog): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AircraftLog $aircraftLog): bool
    {
        //
    }
}
