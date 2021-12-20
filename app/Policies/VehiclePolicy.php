<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehiclePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(Customer $customer)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Vehicle  $vehicle
     * @return mixed
     */
    public function view(Customer $customer, Vehicle $vehicle)
    {
        return $customer->vehicles->contains($vehicle->id);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(Customer $customer)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Vehicle  $vehicle
     * @return mixed
     */
    public function update(Customer $customer, Vehicle $vehicle)
    {
        return $customer->vehicles->contains($vehicle->id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Vehicle  $vehicle
     * @return mixed
     */
    public function delete(Customer $customer, Vehicle $vehicle)
    {
        return $customer->vehicles->contains($vehicle->id);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Vehicle  $vehicle
     * @return mixed
     */
    public function restore(Customer $customer, Vehicle $vehicle)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Vehicle  $vehicle
     * @return mixed
     */
    public function forceDelete(Customer $customer, Vehicle $vehicle)
    {
        //
    }
}
