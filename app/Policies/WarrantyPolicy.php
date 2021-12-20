<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Warranty;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarrantyPolicy
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
     * @param  \App\Models\Warranty  $warranty
     * @return mixed
     */
    public function view(Customer $customer, Warranty $warranty)
    {
        return $warranty->customer->id == $customer->id;
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
     * @param  \App\Models\Warranty  $warranty
     * @return mixed
     */
    public function update(Customer $customer, Warranty $warranty)
    {
        return $warranty->customer->id == $customer->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Warranty  $warranty
     * @return mixed
     */
    public function delete(Customer $customer, Warranty $warranty)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Warranty  $warranty
     * @return mixed
     */
    public function restore(Customer $customer, Warranty $warranty)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Warranty  $warranty
     * @return mixed
     */
    public function forceDelete(Customer $customer, Warranty $warranty)
    {
        //
    }
}
